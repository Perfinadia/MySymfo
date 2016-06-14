<?php
// src/OP/PlatformBundle/Controller/AdvertController.php
namespace OP\PlatformBundle\Controller;

use OP\PlatformBundle\Entity\Advert;
use OP\PlatformBundle\Entity\Application;
use OP\PlatformBundle\Entity\Image;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;



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
        $listAdverts = array(
              array(
                  'title'   => 'Recherche développpeur Symfony',
                  'id'      => 1,
                  'author'  => 'Alexandre',
                  'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
                  'date'    => new \Datetime()),
              array(
                  'title'   => 'Mission de webmaster',
                  'id'      => 2,
                  'author'  => 'Hugo',
                  'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
                  'date'    => new \Datetime()),
              array(
                  'title'   => 'Offre de stage webdesigner',
                  'id'      => 3,
                  'author'  => 'Mathieu',
                  'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
                  'date'    => new \Datetime())
                );

            // Et modifiez le 2nd argument pour injecter notre liste
        return $this->render('OPPlatformBundle:Advert:index.html.twig', array(
            'listAdverts' => $listAdverts
        ));
    }
    
    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository('OPPlatformBundle:Advert')->find($id);

        if (null === $advert) {
          throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        // On récupère la liste des candidatures de cette annonce
        $listApplications = $em
          ->getRepository('OPPlatformBundle:Application')
          ->findBy(array('advert' => $advert))
        ;

        return $this->render('OPPlatformBundle:Advert:view.html.twig', array(
          'advert'           => $advert,
          'listApplications' => $listApplications
        ));
    }

    public function addAction(Request $request)
    {
        $advert = new Advert();
        $advert->setTitle('Recherche developpeur Symfony.');
        $advert->setAuthor('Alexandre');
        $advert->setContent("Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…");
        
        // Création d'une première candidature
        $application1 = new Application();
        $application1->setAuthor('Marine');
        $application1->setContent("J'ai toutes les qualités requises.");
        $application1->setDate(new \DateTime());
    
        // Création d'une deuxième candidature par exemple
        $application2 = new Application();
        $application2->setAuthor('Pierre');
        $application2->setContent("Je suis très motivé.");
        $application2->setDate(new \DateTime());
    
        // On lie les candidatures à l'annonce
        $application1->setAdvert($advert);
        $application2->setAdvert($advert);
        
        $image = new Image();
        $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
        $image->setAlt('Job de rêve');

        $advert->setImage($image);

        $em = $this->getDoctrine()->getManager();
        $em->persist($advert);
        $em->persist($application1);
        $em->persist($application2);
        $em->flush();
        if ($request->isMethod('POST')) {
            $this->addFlash('notice', 'Annonce bien enregistrée.');
            // Puis on redirige vers la page de visualisation de cettte annonce
            return $this->redirectToRoute('View_page', array('id' => $advert->getId()));
        }

        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('OPPlatformBundle:Advert:add.html.twig', array('advert' => $advert));
    }

    public function editAction($id, Request $request)
    {
        // Ici, on récupérera l'annonce correspondante à $id
        // Même mécanisme que pour l'ajout
        if ($request->isMethod('POST')) {
            //$request->get('session')->setFlash('notice', 'Annonce bien modifiée.');
            $this->addFlash('notice', 'Annonce bien modifiée.');
            return $this->redirectToRoute('View_page', array('id' => $id));
        }
        $advert = array(
            'title'   => 'Recherche développpeur Symfony',
            'id'      => $id,
            'author'  => 'Alexandre',
            'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
            'date'    => new \Datetime()
        );

        return $this->render('OPPlatformBundle:Advert:edit.html.twig', array(
            'advert' => $advert
        ));
    }

    public function deleteAction($id)
    {
        // Ici, on récupérera l'annonce correspondant à $id
        // Ici, on gérera la suppression de l'annonce en question
        return $this->render('OPPlatformBundle:Advert:delete.html.twig');
    }

    public function menuAction($limit)
    {
        // On fixe en dur une liste ici, bien entendu par la suite
        // on la récupérera depuis la BDD !
        $listAdverts = array(
          array('id' => 2, 'title' => 'Recherche développeur Symfony'),
          array('id' => 5, 'title' => 'Mission de webmaster'),
          array('id' => 9, 'title' => 'Offre de stage webdesigner')
        );

        return $this->render('OPPlatformBundle:Advert:menu.html.twig', array(
          // Tout l'intérêt est ici : le contrôleur passe
          // les variables nécessaires au template !
          'listAdverts' => $listAdverts
        ));
    }
}