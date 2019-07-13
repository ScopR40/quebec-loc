<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\PaginationService;

class AdminAdController extends AbstractController
{
    /**
     * @Route("/admin/ads/{page<\d+>?1}", name="admin_ads_index")
     */
    public function index(AdRepository $repo, $page, PaginationService $pagination)
    {
       $pagination->setEntityClass(Ad::class)
                  ->setPage($page);

        return $this->render('admin/ad/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition
     *
     * @Route("/admin/ads/{id}/edit", name="admin_ads_edit")
     *
     * @param Ad $ad
     * @return Response
     */
    public function edit(Ad $ad, Request $request, ObjectManager $manager) {
       $form = $this->createForm(AnnonceType::class, $ad);

       $form->handleRequest($request); //passe la request

       if($form->isSubmitted() && $form->isValid()) { //si le formulaire est sousmis et valide
         $manager->persist($ad); //manager fait persisté l'annonce
         $manager->flush(); //manager valide dans la base

         $this->addFlash(
            'success',
            "L'annonce <strong>{$ad->getTitle() }</strong> a bien été enregistrée !"
         );
       }

       return $this->render('admin/ad/edit.html.twig', [
         'ad' => $ad,
         'form' => $form->createView()
       ]);
    }

    /**
     * Permet de supprimer une annonce
     *
     * @Route("/admin/ads/{id}/delete", name="admin_ads_delete")
     *
     * @param Ad $ad
     * @param ObjectManager $manager
     * @return Response
     */
    public function delete(Ad $ad, ObjectManager $manager) {
       if(count($ad->getBookings()) > 0) {
          $this->addFlash(
            'warning',
            "Vous ne pouvez pas supprimer l'annonce <strong>{$ad->getTitle()}</strong> car elle possède déjà des réservations !"
          );
       }else {
          $manager->remove($ad); //manager supprime cette annonce
          $manager->flush(); //confirme la suppression de l'annonce dans la base de données

          $this->addFlash(
             'success',
             "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée !"
          );

       }
         return $this->redirectToRoute('admin_ads_index');
    }
}
