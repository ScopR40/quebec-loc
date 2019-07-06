<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Booking;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

   private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder){
       $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
       $faker = Factory::create('FR-fr');

       $adminRole = new Role(); //creation du role admin
       $adminRole->setTitle('ROLE_ADMIN');
       $manager->persist($adminRole); //manager fait persisté $adminRole

       $adminUser = new User(); //creation adminUser
       $adminUser->setFirstName('Sébastien')
                 ->setLastName('Duforet')
                 ->setEmail('seb@symfony.com')
                 ->setHash($this->encoder->encodePassword($adminUser, 'azertyuiop')) //encode le password
                 ->setPicture('https://avatars.io/twitter/ScopR')
                 ->setIntroduction($faker->sentence()) //faker créer une introduction
                 ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>') //faker créer une description de 3 paragraphes au format html
                 ->addUserRole($adminRole); //ajoute le Role d'admin a l'utilisateur

       $manager->persist($adminUser); //manager fait persisté l'adminUser

       //Nous gérons les utilisateurs
       $users = [];
       $genres = ['male', 'female']; //tableau des genres

       for($i = 1; $i <= 10; $i++) {

         $user = new User(); //creation d'un user

         $genre = $faker->randomElement($genres); //faker donne un genre au hasard

         $picture = 'https://randomuser.me/api/portraits/';
         $pictureId = $faker->numberBetween(1, 99) . '.jpg'; //faker donne un numero entre 1 et 99

         $picture .= ($genre == 'male' ? 'men/' : 'women/') . $pictureId; //si le genre est bien male alors donner pictureId de male sinon donner pictureId female

         $hash = $this->encoder->encodePassword($user, 'password');

         $user->setFirstName($faker->firstName($genre)) //faker créer un firstname suivant le genre
              ->setLastName($faker->lastname) //faker créer un lastname
              ->setEmail($faker->email) //faker créer une adresse email
              ->setIntroduction($faker->sentence()) //faker créer une introduction
              ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>') //faker créer une description de 3 paragraphes au format html
              ->setHash($hash) //faker créer un password
              ->setPicture($picture); //donne cette picture

          $manager->persist($user); //$manager fait persisté cet utilisateur
          $users[] = $user; //rentre l'utlisateur créer dans le tableau des utilisateurs
       }
       //Nous gérons les annonces
       for($i = 1; $i <= 30; $i++) {
           $ad = new Ad();

           $title = $faker->sentence(); //creation aléatoire d'un titre lorem de 6 mots
           $coverImage = $faker->imageUrl(1000,350); //création aléatoire d'une url de 1000px / 350px
           $introduction = $faker->paragraph(2); //creation d'un paragraphe pour l'introduction de deux phrases en lorem
           $content = '<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>'; //creation de 5 paragraphes au format html

           $user = $users[mt_rand(0, count($users) - 1)]; //mets au hasard un utilisateur

           $ad->setTitle($title)
              ->setCoverImage($coverImage)
              ->setIntroduction($introduction)
              ->setContent($content)
              ->setPrice(mt_rand(40, 200)) //chiffre aux hasard entre 40 et 200 euro
              ->setRooms(mt_rand(1, 5)) //chiffre aux hasard entre 1 et 5 chambres
              ->setAuthor($user); //préviens l'annonce que c'est cet utilisateur qui est son auteur

           for($j = 1; $j <= mt_rand(2, 5); $j++) {
              $image = new Image();

              $image->setUrl($faker->imageUrl()) //faker choisit une url d'image
                    ->setCaption($faker->sentence()) //creation aléatoire de la légende de l'image
                    ->setAd($ad); //lie l'image a l'annonce

              $manager->persist($image); //$manager fait persisté cette image
           }

           //Gestions des réservations
           for($j = 1; $j <= mt_rand(0, 10); $j++) {
              $booking = new Booking();

              $createdAt = $faker->dateTimeBetween('-6 months');
              $startDate = $faker->dateTimeBetween('-3 months');
            //Gestion de la date de fin
              $duration = mt_rand(3, 10); //entre 3 et 10 nuits au hasard
              $endDate = (clone $startDate)->modify("+$duration days");

              $amount = $ad->getPrice() * $duration; //calcul de du prix du séjour
              $booker = $users[mt_rand(0, count($users) -1)]; //donne un utilisateur au hasard
              $comment = $faker->paragraph(); //creation d'un paragraphe

              $booking->setBooker($booker) //l'utilisateur
                      ->setAd($ad)  //l'annonce
                      ->setStartDate($startDate) //la date d'arrivée
                      ->setEndDate($endDate) //la date de départ
                      ->setCreatedAt($createdAt) //la date de création
                      ->setAmount($amount) //le prix total des nuitées
                      ->setComment($comment); //le commentaire

               $manager->persist($booking); //manager fait psersité cette réservation

               //Gestion des commentaires
               if(mt_rand(0, 1)) {
                  $comment = new Comment(); //creation nouveau commentaire
                  $comment->setContent($faker->paragraph()) // $faker créer un paragraphe
                          ->setRating(mt_rand(1, 5)) // note aléatoire entre 1 et 5
                          ->setAuthor($booker) // l'auteur c'est celui qui a réservé
                          ->setAd($ad); //l'annonce

                  $manager->persist($comment); //manager fait persisté le commentaire
               }
           }

           $manager->persist($ad); //demande a $manager de faire persisté l'annonce($ad)
       }

        $manager->flush(); //envoi la requete finale
    }
}
