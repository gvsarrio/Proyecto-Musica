<?php

namespace App\DataFixtures;

use App\Entity\Banda;
use App\Entity\Genero;
use App\Entity\MiembroBanda;
use App\Entity\Musico;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class BandaMasivaFixtures extends Fixture implements DependentFixtureInterface
{
    private array $nombres = [
        'Los Náufragos', 'Marea Negra', 'El Último Rayo', 'Tormenta del Sur', 'Zona Oscura',
        'La Fuga Eterna', 'Los Centinelas', 'Tierra Salvaje', 'Viento del Norte', 'La Última Ola',
        'Eco del Silencio', 'Roca Viva', 'Los Errantes', 'Nueva Luz', 'El Horizonte Roto',
        'Banda del Río', 'Los Viajeros', 'Sombra y Luz', 'Cruz del Sur', 'Mar de Fuego',
        'Los Fugitivos', 'Alba Negra', 'El Gran Rugido', 'Voz del Pueblo', 'Los Insurgentes',
        'Marea Brava', 'La Tormenta Azul', 'Rayo de Luna', 'Los Noctámbulos', 'Tierra de Nadie',
        'El Vuelo Libre', 'Los Desterrados', 'Noche Salvaje', 'La Corriente', 'Cielo Roto',
        'Los Eternos', 'Sol Negro', 'La Ola Roja', 'Eco Lejano', 'Los Rebeldes',
        'Viento Eterno', 'La Marea Alta', 'Los Solitarios', 'Roca Negra', 'El Laberinto',
        'Los Buscadores', 'Nueva Era', 'Sombra Roja', 'El Trueno', 'Los Inquietos',
        'Fuego Sagrado', 'La Tempestad', 'Los Nómadas', 'Ola de Calor', 'El Último Paso',
        'Los Aventureros', 'Mar Abierto', 'Voz Libre', 'Los Intrépidos', 'Noche de Luna',
        'La Gran Tormenta', 'Los Pioneros', 'Tierra Firme', 'El Horizonte Lejano', 'Los Viajantes',
        'Marea Roja', 'La Tempestad Azul', 'Los Bravos del Norte', 'Sol Naciente', 'El Grito Libre',
        'Los Desertores', 'Viento Frío', 'La Última Batalla', 'Roca y Roll', 'Los Guerreros',
        'Nueva Ola', 'Sombra Negra', 'El Gran Viaje', 'Los Cazadores', 'Fuego y Agua',
        'La Corriente Roja', 'Los Rebeldes del Sur', 'Mar de Sombras', 'Voz del Trueno', 'Los Errantes del Norte',
        'Noche Fría', 'La Gran Fuga', 'Los Aventureros del Rock', 'Tierra de Fuego', 'El Último Horizonte',
        'Los Nocturnos', 'Marea del Norte', 'La Tormenta Final', 'Los Libres', 'Sol de Medianoche',
        'El Gran Escape', 'Los Veteranos', 'Viento y Fuego', 'La Gran Marea', 'Los Invencibles',
    ];

    private array $ciudades = [
        ['nombre' => 'Madrid',                     'lat' => 40.4168,  'lon' => -3.7038],
        ['nombre' => 'Barcelona',                  'lat' => 41.3851,  'lon' =>  2.1734],
        ['nombre' => 'Valencia',                   'lat' => 39.4699,  'lon' => -0.3763],
        ['nombre' => 'Sevilla',                    'lat' => 37.3891,  'lon' => -5.9845],
        ['nombre' => 'Zaragoza',                   'lat' => 41.6488,  'lon' => -0.8891],
        ['nombre' => 'Málaga',                     'lat' => 36.7213,  'lon' => -4.4214],
        ['nombre' => 'Bilbao',                     'lat' => 43.2630,  'lon' => -2.9350],
        ['nombre' => 'Alicante',                   'lat' => 38.3452,  'lon' => -0.4810],
        ['nombre' => 'Córdoba',                    'lat' => 37.8882,  'lon' => -4.7794],
        ['nombre' => 'Valladolid',                 'lat' => 41.6523,  'lon' => -4.7245],
        ['nombre' => 'Murcia',                     'lat' => 37.9922,  'lon' => -1.1307],
        ['nombre' => 'Palma',                      'lat' => 39.5696,  'lon' =>  2.6502],
        ['nombre' => 'Las Palmas de Gran Canaria', 'lat' => 28.1235,  'lon' => -15.4366],
        ['nombre' => 'Santa Cruz de Tenerife',     'lat' => 28.4636,  'lon' => -16.2518],
        ['nombre' => 'Granada',                    'lat' => 37.1773,  'lon' => -3.5986],
        ['nombre' => 'Salamanca',                  'lat' => 40.9701,  'lon' => -5.6635],
        ['nombre' => 'Toledo',                     'lat' => 39.8628,  'lon' => -4.0273],
        ['nombre' => 'Burgos',                     'lat' => 42.3440,  'lon' => -3.6969],
        ['nombre' => 'Pamplona',                   'lat' => 42.8169,  'lon' => -1.6432],
        ['nombre' => 'San Sebastián',              'lat' => 43.3183,  'lon' => -1.9812],
    ];

    private array $plantillasBio = [
        'Banda formada en {ciudad} en {anyo}. Desde sus inicios han fusionado distintos estilos logrando un sonido propio que les ha llevado a actuar en festivales de toda España.',
        'Nacidos en {ciudad} en {anyo}, llevan años construyendo un sonido ecléctico con influencias de la música clásica española y los ritmos modernos.',
        'Grupo musical fundado en {ciudad} en {anyo}. Su energía en directo y su propuesta musical les han convertido en referente de la escena independiente nacional.',
        'Surgidos en {ciudad} en {anyo}, combinan tradición y modernidad en cada actuación. Han publicado varios trabajos discográficos con muy buena acogida.',
    ];

    private array $tamanosDistribucion = [1, 1, 2, 2, 2, 3, 3, 3, 4, 5];

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir
    ) {}

    public function load(ObjectManager $manager): void
    {
        if ($manager->getRepository(Banda::class)->findOneBy(['nombre' => $this->nombres[0]])) {
            return;
        }

        $generos    = $manager->getRepository(Genero::class)->findAll();
        $musicos    = $manager->getRepository(Musico::class)->findAll();
        \shuffle($musicos);
        $candidatos = \array_slice($musicos, 0, 100);

        $bandasPorMusico = [];
        $pointer         = 0;

        foreach ($this->nombres as $i => $nombre) {
            $ciudad = $this->ciudades[$i % \count($this->ciudades)];
            $anyo   = 1975 + ($i % 46);

            $banda = new Banda();
            $banda->setNombre($nombre);
            $banda->setUbicacion($ciudad['nombre']);
            $banda->setLatitud($ciudad['lat']);
            $banda->setLongitud($ciudad['lon']);
            $banda->setAnyoFormacion($anyo);
            $banda->setBiografia($this->getBiografia($anyo, $ciudad['nombre'], $i));
            $banda->setImagenUrl($this->copiarFoto('band_' . ($i + 1) . '.jpg', 'banda_' . ($i + 1) . '.jpg'));

            $numGeneros = ($i % 3) + 1;
            for ($g = 0; $g < $numGeneros; $g++) {
                $banda->addGeneroMusical($generos[($i + $g * 7) % \count($generos)]);
            }

            $manager->persist($banda);

            $tamano          = $this->tamanosDistribucion[$i % \count($this->tamanosDistribucion)];
            $miembrosAgregados = 0;
            $intentos        = 0;
            $esPrimero       = true;
            $yaAgregados     = [];

            while ($miembrosAgregados < $tamano && $intentos < 200) {
                $musico = $candidatos[$pointer % \count($candidatos)];
                $pointer++;
                $intentos++;
                $mid = $musico->getId();

                if (!isset($yaAgregados[$mid]) && ($bandasPorMusico[$mid] ?? 0) < 2) {
                    $miembro = new MiembroBanda();
                    $miembro->setBanda($banda);
                    $miembro->setMusico($musico);
                    $miembro->setEstado('aceptado');
                    $miembro->setEsAdministrador($esPrimero);
                    $manager->persist($miembro);

                    $bandasPorMusico[$mid] = ($bandasPorMusico[$mid] ?? 0) + 1;
                    $yaAgregados[$mid]     = true;
                    $miembrosAgregados++;
                    $esPrimero = false;
                }
            }

            if (($i + 1) % 25 === 0) {
                $manager->flush();
            }
        }

        $manager->flush();
    }

    private function getBiografia(int $anyo, string $ciudad, int $i): string
    {
        $plantilla = $this->plantillasBio[$i % \count($this->plantillasBio)];
        return \str_replace(['{anyo}', '{ciudad}'], [(string) $anyo, $ciudad], $plantilla);
    }

    private function copiarFoto(string $origen, string $destino): ?string
    {
        $rutaOrigen = $this->projectDir . '/src/DataFixtures/assets/fotos/' . $origen;
        $dirDestino = $this->projectDir . '/public/uploads/bandas/';

        if (!\file_exists($rutaOrigen)) {
            return null;
        }

        if (!\is_dir($dirDestino)) {
            \mkdir($dirDestino, 0775, true);
        }

        \copy($rutaOrigen, $dirDestino . $destino);
        return $destino;
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
            GeneroFixtures::class,
            MusicoMasivoFixtures::class,
        ];
    }
}
