<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Persistence\ObjectManager;

class AdminBookingController extends AbstractController
{
    /**
     * @Route("/admin/bookings", name="admin_booking_index")
     */
    public function index(BookingRepository $repo)
    {
        return $this->render('admin/booking/index.html.twig', [
            'bookings' => $repo->findAll()
        ]);
    }

    /**
     * Permet d'éditer un eréservation
     *
     * @Route("/admin/bookings/{id}/edit", name="admin_booking_edit")
     *
     * @return Response
     */
    public function edit(Booking $booking, Request $request, ObjectManager $manager) {
      $form = $this->createForm(AdminBookingType::class, $booking);

      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()) { //si le form est soumis et qu'il est valide
         $booking->setAmount(0); //met Amount a zéro pour pouvoir recalculé le prix grace a @ORM\PreUpdate de la function PrePersit() de booking.php

         $manager->persist($booking); //manager fait persisté $booking
         $manager->flush(); //manager valide dans la base de données

         $this->addFlash(
            'success',
            "La réservation n° {$booking->getId()} a bien été modifiée"
         );

         return $this->redirectToRoute("admin_booking_index");
      }
       return $this->render('admin/booking/edit.html.twig', [
          'form' => $form->createView(),
          'booking' => $booking
       ]);
    }

    /**
     * Permet de supprimer une réservation 
     * 
     * @Route("/admin/bookings/{id}/delete", name="admin_booking_delete")
     *
     * @return Response
     */
    public function delete(Booking $booking, ObjectManager $manager) {
      $manager->remove($booking); //supprime la réservation
      $manager->flush(); //manager valide dans la base de données

      $this->addFlash(
         'success',
         "La réservation a bien été supprimée"
      );

      return $this->redirectToRoute("admin_booking_index");
    }
}
