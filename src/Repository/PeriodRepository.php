<?php

namespace App\Repository;

use App\Entity\History;
use App\Entity\Period;
use App\Entity\Term;
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
class PeriodRepository extends ServiceEntityRepository implements TermRepositoryInterface
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

    public function findLastPlace(History|Term $history): int
    {
        if (!($history instanceof History)) {
            throw new \InvalidArgumentException("You must pass a History parent object to this method!");
        }

        $period = $this->createQueryBuilder('p')
            ->andWhere('p.history = :history')
            ->setParameter('history', $history)
            ->orderBy('p.place', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        return $period->getPlace();
    }

    public function findByPlace(int $place, History|Term $history): Period
    {
        if (!($history instanceof History)) {
            throw new \InvalidArgumentException("You must pass a History parent object to this method!");
        }

        return $this->createQueryBuilder('p')
            ->andWhere('p.history = :history')
            ->setParameter('history', $history)
            ->andWhere('p.place = :place')
            ->setParameter('place', $place)
            ->getQuery()
            ->getSingleResult();
    }

    public function findAllWithPlaceGreaterThanOrEqual(int $place, History|Term $history): array
    {
        if (!($history instanceof History)) {
            throw new \InvalidArgumentException("You must pass a History parent object to this method!");
        }

        return $this->createQueryBuilder('p')
            ->andWhere('p.history = :history')
            ->setParameter('history', $history)
            ->andWhere('p.place >= :place')
            ->setParameter('place', $place)
            ->getQuery()
            ->getResult();
    }
}
