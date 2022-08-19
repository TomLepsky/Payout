<?php

namespace App\Repository;

use App\Entity\PaymentInstrument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PaymentInstrument|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentInstrument|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentInstrument[]    findAll()
 * @method PaymentInstrument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentInstrumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentInstrument::class);
    }

    // /**
    //  * @return PaymentInstrument[] Returns an array of PaymentInstrument objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PaymentInstrument
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
