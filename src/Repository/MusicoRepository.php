<?php

namespace App\Repository;

use App\Entity\Musico;
use App\Repository\Traits\HasHaversine;
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
    use HasHaversine;

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
