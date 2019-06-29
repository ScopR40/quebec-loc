<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AccountController extends AbstractController
{
    /**
     * Permet d'afficher et de gérer le formulaire de connexion
     *
     * @Route("/login", name="account_login")
     *
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError(); //donne la dernière erreur de connexion
        $username = $utils->getLastUsername(); //donne le dernier username

        return $this->render('account/login.html.twig', [
           'hasError' => $error !== null, //affiche l'erreur si elle est diffèrente de null
           'username' => $username
        ]);
    }

    /**
     * Permet de se déconnecter
     *
     * @Route("/logout", name="account_logout")
     *
     * @return void
     */
    public function logout(){
      //rien ...
    }

    /**
     * Permet d'  afficher le formulaire d'inscription
     *
     * @Route("/register", name="account_register")
     * 
     * @return Response
     */
    public function register(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder) {
      $user = new User(); //nouveau utilisateur

      $form = $this->createForm(RegistrationType::class, $user); //creation du formulaire d'inscription de l'user

      $form->handleRequest($request); //gère la requette

      if($form->isSubmitted() && $form->isValid()) { //si mon formulaire a était soumit et qu'il est valide
         $hash = $encoder->encodePassword($user, $user->getHash()); //encode le password de l'user
         $user->setHash($hash); //user je modifie ton password avec l'encoder

         $manager->persist($user); //manager fait persisté l'utilisateur
         $manager->flush(); //envoi la requette sur la base

         $this->addFlash(
            'success',
            "Votre compte a bien été créé ! Vous pouvez maintenant vous connecter !"
         );

         return $this->redirectToRoute('account_login'); //redirige vers account_login
      }

      return $this->render('account/registration.html.twig', [ //affiche account/registration.html.twig
         'form' => $form->createView() //créer la vue
      ]);
    }

    /**
     * Permet d'afficher et de traiter le formulaire de modification de profil
     *
     * @Route("/account/profile", name="account_profile")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function profile(Request $request, ObjectManager $manager){
      $user = $this->getUser(); //l'utilisateur connecté

       $form = $this->createForm(AccountType::class, $user); //creation du formulaire de l'user

       $form->handleRequest($request); //gère la requette

       if($form->isSubmitted() && $form->isValid()) { //si le form est  soumit et qu'il est valide
         $manager->persist($user); //manager fait persisté l'utilisateur
         $manager->flush(); //envoi la requette sur la base

         $this->addFlash(
            'success',
            "Les données du profil ont été enregistrée avec succès !"
         );
       }

      return $this->render('account/profile.html.twig', [
         'form' => $form->createView() //créer la vue
      ]);
    }

    /**
     * Permet de modifier le mot de passe
     *
     * @Route("/account/password-update", name="account_password")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function updatePassword(Request $request, UserPasswordEncoderInterface $encoder, ObjectManager $manager) {

      $passwordUpdate = new PasswordUpdate();

      $user = $this->getUser(); //l'utilisateur connecté

      $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate); //création du formulaire de nouveau password

      $form->handleRequest($request); //gère la requette

      if($form->isSubmitted() && $form->isValid()) { //si le form est  soumit et qu'il est valide
         // 1.Vérifier que le oldPassword du formulaire soit le meme que le password de l'user
         if(!password_verify($passwordUpdate->getOldPassword(), $user->getHash())){
            // Gérer l'erreur
            $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez tapé n'est pas pas votre mot de passe actuel !"));
         } else {
            $newPassword = $passwordUpdate->getNewPassword(); //nouveau password
            $hash = $encoder->encodePassword($user, $newPassword);//encode le nouveau Password

            $user->setHash($hash); //user je change ton hash par le nouveau $hash

            $manager->persist($user); //manager fait persisté l'utilisateur
            $manager->flush(); //envoi la requette sur la base

            $this->addFlash(
               'success',
               "Votre mot de passe a bien été modifié !"
            );

            return $this->redirectToRoute('homepage');
         }
      }

      return $this->render('account/password.html.twig', [
         'form' => $form->createView()
      ]);
    }
    /**
     * Permet d'afficher le profil de l'utilisateur connecté
     *
     * @Route("/account", name="account_index")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function myAccount() {
      return $this->render('user/index.html.twig', [ //affiche user/index.html.twig
         'user' => $this->getUser()
      ]);
    }

    /**
     * Permet d'afficher la liste des réservations faites par l'utilisateur
     * 
     * @Route("/account/bookings", name="account_bookings")
     *
     * @return Response
     */
    public function bookings() {
      return $this->render('account/bookings.html.twig');  //affiche account/bookings.html.twig
    }
}
