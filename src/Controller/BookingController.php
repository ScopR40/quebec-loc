<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Form\BookingType;
use App\Form\CommentType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookingController extends AbstractController
{
    /**
     * @Route("/ads/{slug}/book", name="booking_create")
     * @IsGranted("ROLE_USER")
     */
    public function book(Ad $ad, Request $request, ObjectManager $manager)
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
           $user = $this->getUser();

           $booking->setBooker($user)
                   ->setAd($ad);

           // si les dates ne sont pas disponibles, message d'erreur
           if(!$booking->isBookableDates()) {
              $this->addFlash(
                 'warning',
                 "Les dates que vous avez choisi ne peuvent pas être réservées : elles sont déjà prises."
              );
           }else {
              // sinon enregistrement et redirection
              $manager->persist($booking);
              $manager->flush();

              return $this->redirectToRoute('booking_show', ['id' => $booking->getId(), 'withAlert' => true]);
           }
        }

        return $this->render('booking/book.html.twig', [
            'ad' => $ad,
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher la page d'une réservation
     *
     * @Route("/booking/{id}", name="booking_show")
     *
     * @param Booking $booking
     * @param Request $request
     * @param ObjectManager $manager
     * @return Response
     */
    public function show(Booking $booking, Request $request, ObjectManager $manager) {
       $comment = new Comment();

       $form = $this->createForm(CommentType::class, $comment);

       $form->handleRequest($request);

       if($form->isSubmitted() && $form->isValid()) {
          $comment->setAd($booking->getAd()) // le commentaire appartient a cette annonce
                  ->setAuthor($this->getUser()); // l'auteur c'est l'user connecté

         $manager->persist($comment); //manager fait persisté le commntaire
         $manager->flush(); //envoi vers la base de donnée

         $this->addFlash(
            'success',
            "Votre commentaire a bien été pris en compte !"
         );
       }

      return $this->render('booking/show.html.twig', [
         'booking' => $booking,
         'form' => $form->createView()
      ]);
    }
}