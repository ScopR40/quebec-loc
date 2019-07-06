<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminAccountController extends AbstractController
{
    /**
     * @Route("/admin/login", name="admin_account_login")
     */
    public function login(AuthenticationUtils $utils)
    {
      $error = $utils->getLastAuthenticationError(); //donne la dernière erreur de connexion
      $username = $utils->getLastUsername(); //donne le dernier username

         return $this->render('admin/account/login.html.twig', [
            'hasError' => $error !== null, //affiche l'erreur si elle est diffèrente de null
            'username' => $username
         ]);
    }

   /**
    * Permet de se déconnecter
    *
    * @Route("/admin/logout", name="admin_account_logout")
    *
    * @return void
    */
    public function logout() {

    }
}
