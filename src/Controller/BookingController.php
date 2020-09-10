<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Form\BookingType;
use App\Form\BookinkType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    /**
     * @Route("/ads/{slug}/book", name="booking_create")
     * @IsGranted("ROLE_USER")
     */
    public function book(Ad $ad,Request $request, EntityManagerInterface $manager){

        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user = $this->getUser();

            $booking->setBooker($user)
                    ->setAd($ad);

            // Si les dates ne sont pas dispo, message d'erreur
            if(!$booking->isBookableDates()){
                $this->addFlash(
                    'warning',
                    "Les dates choisis ne sont plus disponible."
                );
            }else {
                // Sinon enregistrement et redirection
                $manager->persist($booking);
                $manager->flush();

                return $this->redirectToRoute('booking_show', ['id' => $booking->getId(),
                    'withAlert' => true]);
            }
        }

        return $this->render('booking/book.html.twig', [
            'ad'=> $ad,
            'form'=> $form->createView()
        ]);
    }

    /**
     * Permet d'afficher la page de réservation
     *
     * @Route("/booking/{id}", name="booking_show")
     *
     */
    public function show(Booking $booking){

        return $this->render('booking/show.html.twig', [
            'booking'=> $booking
        ]);
    }
}