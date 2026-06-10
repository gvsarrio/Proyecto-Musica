<?php

namespace App\DataFixtures;

use App\Entity\InstrumentoSistema;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $nombres = [
            'Voz', 'Guitarra Eléctrica', 'Bajo Eléctrico', 'Batería',
            'Teclado / Piano', 'Guitarra Acústica', 'Saxofón',
            'Violín', 'Sintetizador', 'Percusión'
        ];

        foreach ($nombres as $nombre) {
            if ($manager->getRepository(InstrumentoSistema::class)->findOneBy(['nombre' => $nombre])) {
                continue;
            }
            $instrumento = new InstrumentoSistema();
            $instrumento->setNombre($nombre);
            $manager->persist($instrumento);
        }

        $manager->flush();
    }
}
