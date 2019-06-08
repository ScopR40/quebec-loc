<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController {


   /**
    * @Route("/", name="homepage")
   */
   public function home(){

      return $this->render( //retourne la page home.html.twig
         'home.html.twig'
      );
   }
}



?>