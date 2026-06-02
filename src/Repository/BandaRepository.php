<?php

namespace App\Repository;

use App\Entity\Banda;
use App\Repository\Traits\HasHaversine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Banda>
 */
class BandaRepository extends ServiceEntityRepository
{
    use HasHaversine;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Banda::class);
    }

    /**
     * @return Banda[]
     */
    public function findByFiltros(array $generoIds, ?float $lat, ?float $lng, ?int $radio): array
    {
        $qb = $this->createQueryBuilder('b');

        if (!empty($generoIds)) {
            $qb->join('b.generosMusicales', 'g')
               ->andWhere('g.id IN (:generos)')
               ->setParameter('generos', $generoIds);
        }

        $bandas = $qb->distinct()->getQuery()->getResult();

        if ($lat !== null && $lng !== null && $radio !== null && $radio > 0) {
            $bandas = array_values(array_filter($bandas, function (Banda $b) use ($lat, $lng, $radio) {
                if ($b->getLatitud() === null || $b->getLongitud() === null) {
                    return false;
                }
                return $this->haversine($lat, $lng, $b->getLatitud(), $b->getLongitud()) <= $radio;
            }));
        }

        return $bandas;
    }

}
