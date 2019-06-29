<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
    /**
     * @Route("/ads", name="ads_index")
     */
    public function index(AdRepository $repo) //injection de dépendances
    {
       $ads = $repo->findAll(); //va chercher toutes les annonces dans la base de données

        return $this->render('ad/index.html.twig', [
            'ads' => $ads
        ]);
    }

    /**
     * Permet de créer une annonce
     *
     * @Route("/ads/new", name="ads_create")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
    */
    public function create(Request $request, ObjectManager $manager){ //$request représente le POST
      $ad = new Ad(); //nouvelle annonce

      $ad->setAuthor($this->getUser()); // l'user connecté est l'auteur de cet annonce

      $form = $this->createForm(AnnonceType::class, $ad); //demande la creation du formulaire

      $form->handleRequest($request); //fait le lien entre les champs du form pour les mettres dans le $ad

      if($form->isSubmitted() && $form->isValid()){ //si le form est soumit et qu'il est valide
         foreach($ad->getImages() as $image){ //pour chaque image
            $image->setAd($ad); //dit a quel annonce elle appartient
            $manager->persist($image); //$manager fait persisté cette image
         }

         $manager->persist($ad); //$manager fait persisté la nouvelle annonce
         $manager->flush(); //$manager enregistre la nouvelle annonce  dans la base de données

         $this->addFlash( //affiche que l'annonce a bien était enregistrer
            'success',
            "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée !"
         );

         return$this->redirectToRoute('ads_show', [//redirige vers l'annonce
            'slug'=> $ad->getSlug()
         ]);
      }

      return $this->render('ad/new.html.twig', [ //montre ad/new.html.twig
         'form' => $form->createView() //créer la vue du $form
      ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition
     *
     * @Route("/ads/{slug}/edit", name="ads_edit")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor()", message="Cette annonce ne vous appartient pas, vous ne pouvez pas la modifier")
     *
     * @return Response
     */
    public function edit(Ad $ad, Request $request, ObjectManager $manager){

      $form = $this->createForm(AnnonceType::class, $ad); //demande la creation du formulaire

      $form->handleRequest($request); //fait le lien entre les champs du form pour les mettres dans le $ad

      if($form->isSubmitted() && $form->isValid()){ //si le form est soumit et qu'il est valide
         foreach($ad->getImages() as $image){ //pour chaque image
            $image->setAd($ad); //dit a quel annonce elle appartient
            $manager->persist($image); //$manager fait persisté cette image
         }

         $manager->persist($ad); //$manager fait persisté la nouvelle annonce
         $manager->flush(); //$manager enregistre la nouvelle annonce  dans la base de données

         $this->addFlash( //affiche que l'annonce a bien était enregistrer
            'success',
            "Les modifications de l'annonce <strong>{$ad->getTitle()}</strong> ont  bien été enregistrées !"
         );

         return$this->redirectToRoute('ads_show', [//redirige vers l'annonce
            'slug'=> $ad->getSlug()
         ]);
      }

      return$this->render('ad/edit.html.twig', [
         'form' => $form->createView(),
         'ad' => $ad
      ]);
    }

    /**
     * Permet d'afficher une seule annonce
     *
     * @Route("/ads/{slug}", name="ads_show")
     *
     * @return Response
    */
    public function show(Ad $ad ){
      return $this->render('ad/show.html.twig', [ //montre moi l'annonce sur la page ad/show.html.twig
         'ad' => $ad
      ]);
    }

    /**
     * Permet de supprimer une annonce
     *
     * @Route("/ads/{slug}/delete", name="ads_delete")
     * @Security("is_granted('ROLE_USER') and user == ad.getAuthor()", message="Vous n'avez pas le droit d'accéder à cette ressource")
     *
     * @param Ad $ad
     * @param ObjectManager $manager
     * @return Response
     */
    public function delete(Ad $ad, ObjectManager $manager) {
      $manager->remove($ad); //manager supprime cette annonce
      $manager->flush(); //manager valide l'action demandé dans la base

      $this->addFlash(
         'success',
         "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée !"
      );

      return $this->redirectToRoute("ads_index"); //redirige vers ads_index
    }

}
