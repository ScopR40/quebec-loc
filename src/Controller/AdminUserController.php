<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    /**
     * @Route("/admin/user/{page<\d+>?1}", name="admin_user_index")
     */
    public function index(UserRepository $userRepository, $page, PaginationService $pagination)
    {
      $pagination->setEntityClass(User::class)
      ->setPage($page);
        return $this->render('admin/user/index.html.twig', [
         'pagination' => $pagination
        ]);
    }

    /**
     * Permet de modifier les utilisateurs
     *
     * @Route("/admin/user/{id}/edit", name="admin_user_edit")
     *
     * @param User $user
     * @param Request $request
     * @param ObjectManager $manager
     * @return Response
     */
    public function edit(User $user, Request $request, ObjectManager $manager) {
      $form = $this->createForm(AdminUserType::class, $user);

      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()) { //si le form est soumis et qu'il est valide

         $manager->persist($user); //manager fait persisté $user
         $manager->flush(); //manager valide dans la base de données

         $this->addFlash(
            'success',
            "L'utilisateur a bien été modifiée"
         );
         return $this->redirectToRoute("admin_user_index");
      }
      return $this->render('admin/user/edit.html.twig', [
         'form' => $form->createView(),
         'user' => $user
      ]);
   }
}
