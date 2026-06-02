<?php

namespace App\Repository;

use App\Entity\Musico;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Musico>
 */

/**
 * @extends ServiceEntityRepository<Musico>
 */
class MusicoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Musico::class);
    }

    /**
     * @return Musico[]
     */
    public function findByFiltros(array $generoIds, array $instrumentoIds, ?float $lat, ?float $lng, ?int $radio): array
    {
        $qb = $this->createQueryBuilder('m');

        if (!empty($generoIds)) {
            $qb->join('m.generosMusicales', 'g')
               ->andWhere('g.id IN (:generos)')
               ->setParameter('generos', $generoIds);
        }

        if (!empty($instrumentoIds)) {
            $qb->join('m.instrumentosSistema', 'i')
               ->andWhere('i.id IN (:instrumentos)')
               ->setParameter('instrumentos', $instrumentoIds);
        }

        $musicos = $qb->distinct()->getQuery()->getResult();

        if ($lat !== null && $lng !== null && $radio !== null && $radio > 0) {
            $musicos = array_values(array_filter($musicos, function (Musico $m) use ($lat, $lng, $radio) {
                if ($m->getLatitud() === null || $m->getLongitud() === null) {
                    return false;
                }
                return $this->haversine($lat, $lng, $m->getLatitud(), $m->getLongitud()) <= $radio;
            }));
        }

        return $musicos;
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    //    /**
    //     * @return Musico[] Returns an array of Musico objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Musico
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
