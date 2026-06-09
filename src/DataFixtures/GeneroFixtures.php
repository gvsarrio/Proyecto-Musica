<?php

namespace App\DataFixtures;

use App\Entity\Genero;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GeneroFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $nombres = [
            'Rock', 'Pop', 'Jazz', 'Blues', 'Flamenco',
            'Clásica', 'Metal', 'Punk', 'Reggae', 'Hip-Hop',
            'Electrónica', 'Folk', 'R&B', 'Soul', 'Funk',
            'Country', 'Latina', 'Indie', 'Alternativo',
        ];

        foreach ($nombres as $nombre) {
            if ($manager->getRepository(Genero::class)->findOneBy(['nombre' => $nombre])) {
                continue;
            }
            $genero = new Genero();
            $genero->setNombre($nombre);
            $manager->persist($genero);
        }

        $manager->flush();
    }
}
