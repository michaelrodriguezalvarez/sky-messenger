<?php

namespace App\Repository;

use App\Entity\Mensaje;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Mensaje|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mensaje|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mensaje[]    findAll()
 * @method Mensaje[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MensajeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mensaje::class);
    }    

    public function getPublicMessages(){
        return $this->createQueryBuilder('m')
            ->andWhere('m.destinatario IS NULL')           
            //->setParameter('val', null)
            ->orderBy('m.fecha', 'ASC')
            //->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getPrivateMessages($id_usuario_seleccionado, $id_usuario_logueado){
        return $this->createQueryBuilder('m')
        ->andWhere('m.destinatario = :id_usuario_seleccionado AND m.remitente = :id_usuario_logueado')
        ->orWhere('m.destinatario = :id_usuario_logueado AND m.remitente = :id_usuario_seleccionado')           
        ->setParameter('id_usuario_seleccionado', $id_usuario_seleccionado)
        ->setParameter('id_usuario_logueado', $id_usuario_logueado)
        ->orderBy('m.fecha', 'ASC')
        //->setMaxResults(10)
        ->getQuery()
        ->getResult()
    ;
    }

    // /**
    //  * @return Mensaje[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Mensaje
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
