<?php

namespace App\DataFixtures;

use App\Entity\Genero;
use App\Entity\InstrumentoSistema;
use App\Entity\Musico;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MusicoMasivoFixtures extends Fixture implements DependentFixtureInterface
{
    private array $nombresHombres = [
        'Alberto', 'Alejandro', 'Álvaro', 'Andrés', 'Antonio', 'Arturo', 'Borja', 'Carlos', 'César', 'Daniel',
        'David', 'Diego', 'Eduardo', 'Emilio', 'Enrique', 'Ernesto', 'Esteban', 'Federico', 'Felipe', 'Fernando',
        'Francisco', 'Gabriel', 'Gonzalo', 'Guillermo', 'Hugo', 'Ignacio', 'Iván', 'Jaime', 'Javier', 'Jesús',
        'Jorge', 'José', 'Juan', 'Julián', 'Julio', 'Luis', 'Manuel', 'Mario', 'Marcos', 'Martín',
        'Miguel', 'Nicolás', 'Óscar', 'Pablo', 'Pedro', 'Rafael', 'Ramón', 'Ricardo', 'Roberto', 'Rodrigo',
        'Rubén', 'Salvador', 'Santiago', 'Sergio', 'Tomás', 'Víctor', 'Adrián', 'Agustín', 'Alfonso', 'Alfredo',
        'Aurelio', 'Benito', 'Bruno', 'Cristian', 'Dario', 'Domingo', 'Edgar', 'Elías', 'Emiliano', 'Evaristo',
        'Fabián', 'Félix', 'Fermín', 'Genaro', 'Gerardo', 'Héctor', 'Horacio', 'Ismael', 'Joaquín', 'Joel',
        'Jonathan', 'Leonardo', 'Lorenzo', 'Lucas', 'Mateo', 'Mauricio', 'Maximiliano', 'Noel', 'Omar', 'Patricio',
        'Raúl', 'Renato', 'Samuel', 'Sebastián', 'Teodoro', 'Tristán', 'Valentín', 'Xavier', 'Ángel', 'Cristóbal',
    ];

    private array $nombresMujeres = [
        'Alba', 'Alejandra', 'Alicia', 'Ana', 'Andrea', 'Ángela', 'Beatriz', 'Blanca', 'Carla', 'Carmen',
        'Carolina', 'Claudia', 'Cristina', 'Daniela', 'Diana', 'Elena', 'Elisa', 'Eva', 'Fátima', 'Gloria',
        'Inés', 'Irene', 'Isabel', 'Jessica', 'Julia', 'Laura', 'Lola', 'Lucía', 'Luna', 'Marta',
        'María', 'Mercedes', 'Miriam', 'Mónica', 'Nadia', 'Natalia', 'Noemí', 'Nuria', 'Olga', 'Patricia',
        'Paula', 'Pilar', 'Raquel', 'Rebeca', 'Rosa', 'Sabrina', 'Sara', 'Silvia', 'Sofía', 'Sonia',
        'Susana', 'Teresa', 'Valeria', 'Vanesa', 'Verónica', 'Victoria', 'Yolanda', 'Zoe', 'Adriana', 'Almudena',
        'Amparo', 'Amelia', 'Antonia', 'Bárbara', 'Belén', 'Celia', 'Consuelo', 'Dolores', 'Encarna', 'Esther',
        'Eugenia', 'Felisa', 'Fernanda', 'Gemma', 'Helena', 'Josefa', 'Laia', 'Lara', 'Lorena', 'Lourdes',
        'Magdalena', 'Maite', 'Manuela', 'Margarita', 'Marina', 'Mireia', 'Montserrat', 'Nieves', 'Noelia', 'Paloma',
        'Regina', 'Remedios', 'Rocío', 'Rosario', 'Sandra', 'Tamara', 'Covadonga', 'Gema', 'Fuensanta', 'Esperanza',
    ];

    private array $apellidos = [
        'García', 'Martínez', 'López', 'Sánchez', 'González', 'Rodríguez', 'Fernández', 'Pérez', 'Gómez', 'Martín',
        'Jiménez', 'Ruiz', 'Hernández', 'Díaz', 'Moreno', 'Álvarez', 'Muñoz', 'Romero', 'Alonso', 'Gutiérrez',
        'Navarro', 'Torres', 'Domínguez', 'Vázquez', 'Ramos', 'Gil', 'Serrano', 'Blanco', 'Molina', 'Morales',
        'Suárez', 'Ortega', 'Delgado', 'Castro', 'Ortiz', 'Rubio', 'Marín', 'Sanz', 'Iglesias', 'Núñez',
        'Medina', 'Garrido', 'Cortés', 'Castillo', 'Santos', 'Lozano', 'Guerrero', 'Cano', 'Prieto', 'Méndez',
        'Cruz', 'Calvo', 'Gallego', 'Vidal', 'León', 'Herrera', 'Cabrera', 'Flores', 'Campos', 'Reyes',
    ];

    private array $ciudades = [
        ['nombre' => 'Madrid',                    'lat' => 40.4168,  'lon' => -3.7038],
        ['nombre' => 'Barcelona',                 'lat' => 41.3851,  'lon' =>  2.1734],
        ['nombre' => 'Valencia',                  'lat' => 39.4699,  'lon' => -0.3763],
        ['nombre' => 'Sevilla',                   'lat' => 37.3891,  'lon' => -5.9845],
        ['nombre' => 'Zaragoza',                  'lat' => 41.6488,  'lon' => -0.8891],
        ['nombre' => 'Málaga',                    'lat' => 36.7213,  'lon' => -4.4214],
        ['nombre' => 'Bilbao',                    'lat' => 43.2630,  'lon' => -2.9350],
        ['nombre' => 'Alicante',                  'lat' => 38.3452,  'lon' => -0.4810],
        ['nombre' => 'Córdoba',                   'lat' => 37.8882,  'lon' => -4.7794],
        ['nombre' => 'Valladolid',                'lat' => 41.6523,  'lon' => -4.7245],
        ['nombre' => 'Murcia',                    'lat' => 37.9922,  'lon' => -1.1307],
        ['nombre' => 'Palma',                     'lat' => 39.5696,  'lon' =>  2.6502],
        ['nombre' => 'Las Palmas de Gran Canaria','lat' => 28.1235,  'lon' => -15.4366],
        ['nombre' => 'Santa Cruz de Tenerife',    'lat' => 28.4636,  'lon' => -16.2518],
        ['nombre' => 'Granada',                   'lat' => 37.1773,  'lon' => -3.5986],
        ['nombre' => 'Salamanca',                 'lat' => 40.9701,  'lon' => -5.6635],
        ['nombre' => 'Toledo',                    'lat' => 39.8628,  'lon' => -4.0273],
        ['nombre' => 'Burgos',                    'lat' => 42.3440,  'lon' => -3.6969],
        ['nombre' => 'Pamplona',                  'lat' => 42.8169,  'lon' => -1.6432],
        ['nombre' => 'San Sebastián',             'lat' => 43.3183,  'lon' => -1.9812],
    ];

    private array $biografias = [
        'Guitarra Eléctrica' => [
            'Guitarrista eléctrico con {anyos} años de experiencia en rock y blues. He tocado en varias bandas locales y tengo experiencia en grabación en estudio.',
            'Apasionado de la guitarra eléctrica desde hace {anyos} años. Me especializo en rock clásico y metal, con numerosas actuaciones en directo por toda España.',
        ],
        'Guitarra Acústica' => [
            'Guitarrista acústico con {anyos} años de trayectoria. Me apasionan el folk y el flamenco, y he actuado en festivales y eventos privados de todo el país.',
            'Llevo {anyos} años tocando la guitarra acústica. Me muevo entre el fingerpicking, el pop y la música de raíz, con experiencia en grabaciones y directos.',
        ],
        'Bajo Eléctrico' => [
            'Bajista con {anyos} años de experiencia. Especializado en funk y jazz, he colaborado con múltiples proyectos musicales a lo largo de España.',
            'Llevo {anyos} años como bajista en bandas de rock, pop y jazz. Tengo sólida base rítmica y experiencia tanto en estudio como en directo.',
        ],
        'Batería' => [
            'Baterista con {anyos} años de experiencia en directo. He formado parte de bandas de rock, metal y pop, con grabaciones en estudio y giras nacionales.',
            'Percusionista y baterista con {anyos} años en activo. Me adapto a distintos estilos y he trabajado con artistas de varios géneros.',
        ],
        'Teclado / Piano' => [
            'Pianista y tecladista con formación clásica y {anyos} años de experiencia. Versátil en estilos que van desde la música clásica hasta el jazz y el pop.',
            'Con {anyos} años tocando el piano y los teclados, me muevo con soltura entre el pop, el jazz y la música de cámara.',
        ],
        'Voz' => [
            'Cantante con {anyos} años de trayectoria musical. He actuado en teatros, festivales y clubs en géneros que van del pop al soul y al flamenco.',
            'Llevo {anyos} años dedicándome al canto profesional. Tengo formación vocal clásica y experiencia en estilos modernos como el R&B y el pop.',
        ],
        'Saxofón' => [
            'Saxofonista con {anyos} años de experiencia en jazz y bossa nova. He actuado en clubs de jazz y colaborado con orquestas de big band por toda España.',
            'Llevo {anyos} años tocando el saxofón. Me especializo en jazz y blues, aunque también tengo experiencia en pop y música latina.',
        ],
        'Violín' => [
            'Violinista con formación clásica y {anyos} años de experiencia. Me apasiona la fusión de estilos, desde la música clásica hasta el folk celta y la música de cine.',
            'Con {anyos} años de trayectoria al violín, he participado en orquestas de cámara y proyectos de música contemporánea y folk.',
        ],
        'Sintetizador' => [
            'Productor y sintetizador con {anyos} años de experiencia en música electrónica. He compuesto bandas sonoras y colaborado con artistas de distintos géneros.',
            'Llevo {anyos} años explorando el sonido con sintetizadores y producción electrónica. Trabajo en proyectos de ambient, techno y pop experimental.',
        ],
        'Percusión' => [
            'Percusionista con {anyos} años de experiencia en música latina y jazz. Especializado en congas, bongós y cajón, he actuado en festivales de todo el país.',
            'Con {anyos} años tocando percusión, domino instrumentos como el cajón, el djembé y las congas en estilos que van del flamenco al jazz afrobrasileño.',
        ],
    ];

    public function __construct(
        private UserPasswordHasherInterface $hasher,
        #[Autowire('%kernel.project_dir%')] private string $projectDir
    ) {}

    public function load(ObjectManager $manager): void
    {
        $instrumentos = $manager->getRepository(InstrumentoSistema::class)->findAll();
        $generos      = $manager->getRepository(Genero::class)->findAll();

        $this->crearPerfiles($manager, $instrumentos, $generos, 'hombre');
        $this->crearPerfiles($manager, $instrumentos, $generos, 'mujer');

        $manager->flush();
    }

    private function crearPerfiles(ObjectManager $manager, array $instrumentos, array $generos, string $sexo): void
    {
        $nombres    = $sexo === 'hombre' ? $this->nombresHombres : $this->nombresMujeres;
        $prefijo    = $sexo === 'hombre' ? 'm' : 'f';
        $fotoPrefix = $sexo === 'hombre' ? 'men' : 'women';
        $total      = \count($nombres);

        for ($i = 0; $i < $total; $i++) {
            $email = \sprintf('musico_%s_%03d@musichub.com', $prefijo, $i + 1);

            if ($manager->getRepository(Usuario::class)->findOneBy(['email' => $email])) {
                continue;
            }

            $nombre   = $nombres[$i] . ' ' . $this->apellidos[$i % \count($this->apellidos)] . ' ' . $this->apellidos[($i + 7) % \count($this->apellidos)];
            $ciudad   = $this->ciudades[$i % \count($this->ciudades)];
            $anyos    = ($i % 30) + 1;

            $usuario = new Usuario();
            $usuario->setEmail($email);
            $usuario->setRoles(['ROLE_USER']);
            $usuario->setPassword($this->hasher->hashPassword($usuario, 'musichub123'));
            $usuario->setIsVerified(true);
            $manager->persist($usuario);

            $musico = new Musico();
            $musico->setUsuario($usuario);
            $musico->setNombre($nombre);
            $musico->setTelefono(600000000 + ($i * 1000003) % 99999999);
            $musico->setUbicacion($ciudad['nombre']);
            $musico->setLatitud($ciudad['lat']);
            $musico->setLongitud($ciudad['lon']);
            $musico->setAnyosExperiencia($anyos);
            $musico->setCreadoEn(new \DateTime());
            $musico->setActualizadoEn(new \DateTime());
            $musico->setEsBanda(false);

            $instrumento = $instrumentos[$i % \count($instrumentos)];
            $musico->setBiografia($this->getBiografia($instrumento->getNombre(), $anyos, $i));
            $musico->getInstrumentosSistema()->add($instrumento);

            if ($i % 3 !== 0) {
                $instrumento2 = $instrumentos[($i + 4) % \count($instrumentos)];
                if ($instrumento2 !== $instrumento) {
                    $musico->getInstrumentosSistema()->add($instrumento2);
                }
            }

            $numGeneros = ($i % 3) + 2;
            for ($g = 0; $g < $numGeneros; $g++) {
                $genero = $generos[($i + $g * 4) % \count($generos)];
                if (!$musico->getGenerosMusicales()->contains($genero)) {
                    $musico->addGeneroMusical($genero);
                }
            }

            $fotoOrigen  = $fotoPrefix . '_' . (($i % 50) + 1) . '.jpg';
            $fotoDestino = \sprintf('musico_%s_%03d.jpg', $prefijo, $i + 1);
            $musico->setImagenUrl($this->copiarFoto($fotoOrigen, $fotoDestino));

            $manager->persist($musico);

            if (($i + 1) % 25 === 0) {
                $manager->flush();
            }
        }
    }

    private function getBiografia(string $instrumento, int $anyos, int $i): string
    {
        $plantillas = $this->biografias[$instrumento] ?? ['Músico con {anyos} años de experiencia y gran pasión por la música en directo.'];
        $plantilla  = $plantillas[$i % \count($plantillas)];
        return \str_replace('{anyos}', (string) $anyos, $plantilla);
    }

    private function copiarFoto(string $origen, string $destino): ?string
    {
        $rutaOrigen = $this->projectDir . '/src/DataFixtures/assets/fotos/' . $origen;
        $dirDestino = $this->projectDir . '/public/uploads/perfiles/';

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
        ];
    }
}
