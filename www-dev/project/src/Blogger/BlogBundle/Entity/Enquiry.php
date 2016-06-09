<?php

namespace Blogger\BlogBundle\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints as Assert;

class Enquiry
{
    protected $name;
    protected $mail;
    protected $subject;
    protected $body;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new NotBlank());

        $metadata->addPropertyConstraint('mail', new Email());

        $metadata->addPropertyConstraint('subject', new NotBlank());
        $metadata->addPropertyConstraint('subject', new Assert\Length(array(
            'min'        => 2,
            'max'        => 50,
            'minMessage' => 'Your first name must be at least {{ limit }} characters length',
            'maxMessage' => 'Your first name cannot be longer than {{ limit }} characters length',
        )));

        $metadata->addPropertyConstraint('body', new Assert\Length(array(
            'min'        => 1,
            'max'        => 500,
            'minMessage' => 'Your first name must be at least {{ limit }} characters length',
            'maxMessage' => 'Your first name cannot be longer than {{ limit }} characters length',
        )));
    }

}