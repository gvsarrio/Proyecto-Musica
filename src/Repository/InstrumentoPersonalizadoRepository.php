<?php

namespace App\Repository;

use App\Entity\InstrumentoPersonalizado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InstrumentoPersonalizado>
 */
class InstrumentoPersonalizadoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstrumentoPersonalizado::class);
    }
}
