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
            $languageIds = explode(',', $language);
            $qb->andWhere('s.lng_id IN (:languages)')
                ->setParameter('languages', $languageIds);
        }

        if ($direction) {
            $directionIds = explode(',', $direction);
            $qb->andWhere('s.drc_id IN (:directions)')
                ->setParameter('directions', $directionIds);
        }

        if (!empty($stacks)) {
            // Создаем подзапрос для поиска заказов, которые содержат ВСЕ выбранные технологии
            $subQuery = $this->createQueryBuilder('sub_o')
                ->select('sub_o.id')
                ->innerJoin('sub_o.ordersStacks', 'sub_os')
                ->innerJoin('sub_os.stc_id', 'sub_s')
                ->where('sub_s.id IN (:stacks)')
                ->groupBy('sub_o.id')
                ->having('COUNT(DISTINCT sub_s.id) = :stackCount');

            $qb->andWhere('o.id IN (' . $subQuery->getDQL() . ')')
                ->setParameter('stacks', $stacks)
                ->setParameter('stackCount', count($stacks));
        }

        $qb->groupBy('o.id'); // Убираем дубли, если заказ связан с несколькими подходящими стеками

        return $qb->getQuery()->getResult();
    }


}
