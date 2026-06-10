<?php

namespace App\Repository;

use App\Entity\Conversacion;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversacion>
 */
class ConversacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversacion::class);
    }

    //    /**
    //     * @return Conversacion[] Returns an array of Conversacion objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Conversacion
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function buscarEntreUsuarios(Usuario $usuarioA, Usuario $usuarioB): ?Conversacion
    {
        return $this->createQueryBuilder('c')
            ->where(
                '(c.usuarioUno = :usuarioA AND c.usuarioDos = :usuarioB)
             OR
             (c.usuarioUno = :usuarioB AND c.usuarioDos = :usuarioA)'
            )
            ->setParameter('usuarioA', $usuarioA)
            ->setParameter('usuarioB', $usuarioB)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function buscarPorUsuario(Usuario $usuario): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.usuarioUno = :usuario')
            ->orWhere('c.usuarioDos = :usuario')
            ->setParameter('usuario', $usuario)
            ->orderBy('c.fechaUltimoMensaje', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
