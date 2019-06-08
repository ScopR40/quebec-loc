<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
       $faker = Factory::create('FR-fr');

       for($i = 1; $i <= 30; $i++) {
           $ad = new Ad();

           $title = $faker->sentence(); //creation aléatoire d'un titre lorem de 6 mots
           $coverImage = $faker->imageUrl(1000,350); //création aléatoire d'une url de 1000px / 350px
           $introduction = $faker->paragraph(2); //creation d'un paragraphe pour l'introduction de deux phrases en lorem
           $content = '<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>'; //creation de 5 paragraphes au format html


           $ad->setTitle($title)
              ->setCoverImage($coverImage)
              ->setIntroduction($introduction)
              ->setContent($content)
              ->setPrice(mt_rand(40, 200)) //chiffre aux hasard entre 40 et 200 euro
              ->setRooms(mt_rand(1, 5)); //chiffre aux hasard entre 1 et 5 chambres

           for($j = 1; $j <= mt_rand(2, 5); $j++) {
              $image = new Image();

              $image->setUrl($faker->imageUrl()) //faker choisit une url d'image
                    ->setCaption($faker->sentence()) //creation aléatoire de la légende de l'image
                    ->setAd($ad); //lie l'image a l'annonce

              $manager->persist($image); //$manager fait persisté cette image
           }

           $manager->persist($ad); //demande a $manager de faire persisté l'annonce($ad)
       }

        $manager->flush(); //envoi la requete finale
    }
}
