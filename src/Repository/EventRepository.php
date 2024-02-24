<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\Period;
use App\Entity\Term;
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
class EventRepository extends ServiceEntityRepository implements TermRepositoryInterface
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

    public function findLastPlace(History|Term $period): int
    {
        if (!($period instanceof Period)) {
            throw new \InvalidArgumentException("You must pass a Period parent object to this method!");
        }

        $event = $this->createQueryBuilder('e')
            ->andWhere('e.period = :period')
            ->setParameter('period', $period)
            ->orderBy('e.place', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        return $event->getPlace();
    }

    public function findAllWithPlaceGreaterThanOrEqual(int $place, History|Term $period): array
    {
        if (!($period instanceof Period)) {
            throw new \InvalidArgumentException("You must pass a Period parent object to this method!");
        }

        return $this->createQueryBuilder('e')
            ->andWhere('e.period = :period')
            ->setParameter('period', $period)
            ->andWhere('e.place >= :place')
            ->setParameter('place', $place)
            ->getQuery()
            ->getResult();
    }

    public function findByPlace(int $place, History|Term $period): Event
    {
        if (!($period instanceof Period)) {
            throw new \InvalidArgumentException("You must pass a Period parent object to this method!");
        }

        return $this->createQueryBuilder('e')
            ->andWhere('e.period = :period')
            ->setParameter('period', $period)
            ->andWhere('e.place = :place')
            ->setParameter('place', $place)
            ->getQuery()
            ->getSingleResult();
    }
}
