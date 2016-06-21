<?php
// src/OP/PlatformBundle/Controller/AdvertController.php
namespace OP\PlatformBundle\Controller;

use OP\PlatformBundle\Entity\Advert;
use OP\PlatformBundle\Entity\Application;
use OP\PlatformBundle\Form\AdvertType;
use OP\PlatformBundle\Form\ApplicationType;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;



class AdvertController extends Controller
{
    public function indexAction($page)
    {
        // On ne sait pas combien de pages il y a
        // Mais on sait qu'une page doit être supérieure ou égale à 1
        if ($page < 1) {
            // On déclenche une exception NotFoundHttpException, cela va afficher
            // une page d'erreur 404 (qu'on pourra personnaliser plus tard d'ailleurs)
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }
        $listAdverts = $this->getDoctrine()
            ->getManager()
            ->getRepository('OPPlatformBundle:Advert')
            ->findBy(array('published' => 1))
        ;
        
            // Et modifiez le 2nd argument pour injecter notre liste
        return $this->render('OPPlatformBundle:Advert:index.html.twig', array(
            'listAdverts' => $listAdverts,
        ));
    }
    
    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // Pour récupérer une seule annonce, on utilise la méthode find($id)
        $advert = $em->getRepository('OPPlatformBundle:Advert')->find($id);

        // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
        // ou null si l'id $id n'existe pas, d'où ce if :
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        // Récupération de la liste des candidatures de l'annonce
        $listApplications = $em
            ->getRepository('OPPlatformBundle:Application')
            ->findBy(array('advert' => $advert))
        ;


        return $this->render('OPPlatformBundle:Advert:view.html.twig', array(
            'advert'           => $advert,
            'listApplications' => $listApplications,
        ));
    }

    public function addAction(Request $request)
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_AUTHOR')){
            throw new AccessDeniedException('Accès limité aux auteurs');
        }
        $em = $this->getDoctrine()->getManager();
        $advert = new Advert();
        $advert->setDate(new \Datetime());
        $advert->setAuthor($this->getUser());
        
        $form = $this->createForm(new AdvertType(), $advert);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($advert);
                $em->flush();
                $this->addFlash('notice', 'Annonce bien enregistrée.');

                return $this->redirectToRoute('View_page', array('id' => $advert->getId()));
            }
        }

        return $this->render('OPPlatformBundle:Advert:add.html.twig', array('form' => $form->createView()));
    }

    public function editAction($id, Request $request)
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_AUTHOR')){
            throw new AccessDeniedException('Accès limité aux auteurs');
        }
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('OPPlatformBundle:Advert')->find($id);
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }
        $form = $this->createForm(new AdvertType(), $advert);
        ;
        // Ici encore, il faudra mettre la gestion du formulaire
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if($form->isValid()) {
                $em->flush();
                $this->addFlash('notice', 'Annonce bien modifiée.');
                return $this->redirectToRoute('View_page', array('id' => $id));
            }
        }
        return $this->render('OPPlatformBundle:Advert:edit.html.twig', array(
            'advert' => $advert,
            'form' => $form->createView()
        ));
    }

    public function deleteAction($id)
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux admins');
        }
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('OPPlatformBundle:Advert')->find($id);
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }
        $em->remove($advert);
        $em->flush();
        $ladvert = $em->getRepository('OPPlatformBundle:Advert')->findBy(array('published' => 1));
        return $this->render('OPPlatformBundle:Advert:index.html.twig', array('listAdverts' => $ladvert));
    }

    public function menuAction($limit)
    {
        $em = $this->getDoctrine()->getManager();
        $listAdverts = $em->getRepository('OPPlatformBundle:Advert')->findBy(
            array('published' => 1),                 // Pas de critère
            array('date' => 'desc'), // On trie par date décroissante
            $limit,                  // On sélectionne $limit annonces
            0                        // À partir du premier
        );
        return $this->render('OPPlatformBundle:Advert:menu.html.twig', array(
            'listAdverts' => $listAdverts
        ));
    }

    public function adminAction()
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès limité aux ADMINS');
        }
        $listUsers = $this->get('fos_user.user_manager')->findUsers();
        $lAd = $this->getDoctrine()->getRepository('OPPlatformBundle:Advert')->findAll();
        return $this->render('OPPlatformBundle:Advert:Admin.html.twig', array(
            'listUsers' => $listUsers, 'adList' => $lAd
        ));
    }

    public function applicationAction($id, Request $request)
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_AUTHOR')){
            throw new AccessDeniedException('Accès limité aux Auteurs');
        }
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('OPPlatformBundle:Advert')->find($id);
        $application = new Application();
        $application->setAdvert($advert);
        $application->setAuthor($this->getUser());
        $application->setDate(new \DateTime());
        
        $form = $this->createForm(new ApplicationType(), $application);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if($form->isValid()) {
                $advert->addApplication($application);
                $em->persist($application);
                $em->flush();
                $this->addFlash('notice', 'Candidature ajoutée.');
                return $this->redirectToRoute('View_page', array('id' => $id));
            }
        }
        return $this->render('OPPlatformBundle:Advert:Application.html.twig', array(
            'form' => $form->createView(),
            'advert' => $advert
        ));
    }
}