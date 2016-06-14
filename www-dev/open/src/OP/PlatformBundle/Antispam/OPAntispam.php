<?php
// src/OC/PlatformBundle/Antispam/OCAntispam.php

namespace OP\PlatformBundle\Antispam;

class OPAntispam
{
  private $mailer;
  private $locale;
  private $minLenght;

  public function __construct(\Swift_Mailer $mailer, $locale, $minLength)
  {
    $this->mailer = $mailer;
    $this->locale = $locale;
    $this->minLenght = $minLength;
  }

  /**
   * Vérifie si le texte est un spam ou non
   *
   * @param string $text
   * @return bool
   */
  public function isSpam($text)
  {
    return strlen($text) < $this->minLenght;
  }
}