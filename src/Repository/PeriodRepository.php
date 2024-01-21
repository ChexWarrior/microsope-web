<?php

namespace App\Repository;

use App\Entity\History;
use App\Entity\Period;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Period>
 *
 * @method Period|null find($id, $lockMode = null, $lockVersion = null)
 * @method Period|null findOneBy(array $criteria, array $orderBy = null)
 * @method Period[]    findAll()
 * @method Period[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Period::class);
    }

    public function save(Period $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Period $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLastPlaceByHistory(History $history): int
    {
        $period = $this->createQueryBuilder('p')
            ->andWhere('p.history = :history')
            ->setParameter('history', $history)
            ->orderBy('p.place', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        return $period->getPlace();
    }

    public function findByPlace(int $place, History $history): Period
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.history = :history')
            ->setParameter('history', $history)
            ->andWhere('p.place = :place')
            ->setParameter('place', $place)
            ->getQuery()
            ->getSingleResult();
    }

    public function findAllWithPlaceGreaterThanOrEqual(int $place, History $history): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.history = :history')
            ->setParameter('history', $history)
            ->andWhere('p.place >= :place')
            ->setParameter('place', $place)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Period[] Returns an array of Period objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Period
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
