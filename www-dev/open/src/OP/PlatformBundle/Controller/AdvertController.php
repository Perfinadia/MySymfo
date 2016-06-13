<?php


// src/OP/PlatformBundle/Controller/AdvertController.php


namespace OP\PlatformBundle\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdvertController extends Controller

{
    public function indexAction()
    {
        $content = $this->get('templating')->render('OPPlatformBundle:Advert:index.html.twig');
        return new Response($content);
    }

    public function viewAction($id)
    {
        return $this->render('OPPlatformBundle:Advert:view.html.twig', array('id' => $id));
    }
}