<?php

namespace App\DataFixtures;

use App\Entity\Instrumento;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class InstrumentoFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $instrumentos = [
            'Guitarra eléctrica',
            'Guitarra acústica',
            'Bajo',
            'Batería',
            'Piano',
            'Teclado',
            'Violín',
            'Violonchelo',
            'Trompeta',
            'Saxofón',
            'Flauta',
            'Clarinete',
            'Voz',
            'Percusión',
            'DJ / Producción',
        ];

        foreach ($instrumentos as $nombre) {
            $instrumento = new Instrumento();
            $instrumento->setNombre($nombre);
            $manager->persist($instrumento);
        }

        $manager->flush();
    }
}