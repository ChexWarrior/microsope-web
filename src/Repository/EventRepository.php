<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Period;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLastPlaceByPeriod(Period $period): int
    {
        $event = $this->createQueryBuilder('e')
            ->andWhere('e.period = :period')
            ->setParameter('period', $period)
            ->orderBy('e.place', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        return $event->getPlace();
    }

    public function findAllWithPlaceGreaterThanOrEqual(int $place, Period $period): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.period = :period')
            ->setParameter('period', $period)
            ->andWhere('e.period >= :place')
            ->setParameter('place', $place)
            ->getQuery()
            ->getResult();
    }

    public function findByPlace(int $place, Period $period): Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.period = :period')
            ->setParameter('period', $period)
            ->andWhere('e.place = :place')
            ->setParameter('place', $place)
            ->getQuery()
            ->getSingleResult();
    }


//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
