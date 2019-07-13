<?php

namespace App\Controller;

use App\Repository\AdRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserRepository;

class HomeController extends AbstractController {


   /**
    * @Route("/", name="homepage")
   */
   public function home(AdRepository $adRepo, UserRepository $userRepo){

      return $this->render( //retourne la page home.html.twig
         'home.html.twig',
         [
            'ads' => $adRepo->findBestAds(3),
            'users' => $userRepo->findBestUsers(2)
         ]
      );
   }
}



?>