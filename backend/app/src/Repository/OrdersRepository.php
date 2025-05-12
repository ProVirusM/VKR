<?php

namespace App\Repository;

use App\Entity\Orders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Orders>
 */
class OrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orders::class);
    }

    //    /**
    //     * @return Orders[] Returns an array of Orders objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Orders
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findByFilters($direction = null, $language = null, $stacks = [])
    {
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.ordersStacks', 'os')
            ->innerJoin('os.stc_id', 's');

        if ($language) {
            $qb->andWhere('s.lng_id = :language')
                ->setParameter('language', $language);
        }

        if ($direction) {
            $qb->andWhere('s.drc_id = :direction')
                ->setParameter('direction', $direction);
        }

        if (!empty($stacks)) {
            $qb->andWhere('s.id IN (:stacks)')
                ->setParameter('stacks', $stacks);
        }

        $qb->groupBy('o.id'); // Убираем дубли, если заказ связан с несколькими подходящими стеками

        return $qb->getQuery()->getResult();
    }


}
