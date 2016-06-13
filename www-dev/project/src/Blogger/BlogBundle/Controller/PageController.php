<?php
// src/Blogger/BlogBundle/Controller/PageController.php

namespace Blogger\BlogBundle\Controller;

//imported class///////////
use Blogger\BlogBundle\Entity\Enquiry;
use Blogger\BlogBundle\Form\EnquiryType;
///////////////////////////
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

class PageController extends Controller
{
    public function indexAction()
    {
        return $this->render('BloggerBlogBundle:Page:index.html.twig');
    }

    public function aboutAction()
    {
	    return $this->render('BloggerBlogBundle:Page:about.html.twig');
    }

    public function contactAction()
    {
        $enquiry = new Enquiry();
        $form = $this->createForm(new EnquiryType(), $enquiry);

        $request = $this->get('request_stack')->getCurrentRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {

            $message = \Swift_Message::newInstance();

            //$image = $message->embed(\Swift_Image::fromPath('../Image/image.jpg'));
            $message->setSubject($enquiry->getSubject());
            $message->setFrom($enquiry->getMail());
            $message->setTo($this->container->getParameter('blogger_blog.emails.contact_email'));
            $message->setBody($this->renderView('BloggerBlogBundle:Page:contactemail.html.twig', array('enquiry' => $enquiry)), 'text/html');
            //$message->embed(\Swift_Image::fromPath('../Image/image.jpg'));
            //->setBody($enquiry->getBody());

            $this->get('mailer')->send($message);

            //$this->get('session')->getFlashBag()->add('blogger-notice', 'Your contact enquiry was successfully sent. Thank you!');
            // Redirect - This is important to prevent users re-posting
            // the form if they refresh the page
            return $this->redirect($this->generateUrl('BloggerBlogBundle_contact'));
        }

        return $this->render('BloggerBlogBundle:Page:contact.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
