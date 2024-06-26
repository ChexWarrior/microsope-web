<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\Period;
use App\Entity\Scene;
use App\Entity\Term;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Scene>
 *
 * @method Scene|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scene|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scene[]    findAll()
 * @method Scene[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SceneRepository extends ServiceEntityRepository implements TermRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scene::class);
    }

    public function save(Scene $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Scene $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getNumScenesForEventsInHistory(History $history): array {
        $entityManager = $this->getEntityManager();
        $numScenesByEvent = [];
        $query = $entityManager->createQuery(
            'SELECT COUNT(s) numScenes, p.id period_id, e.id event_id
            FROM App\Entity\Period p
            JOIN p.events e
            JOIN e.scenes s
            WHERE p.history = :history
            GROUP BY e.id'
        );
        $query->setParameter(':history', $history);
        $results = $query->getArrayResult();

        // Convert to array indexed by event id
        foreach ($results as $result) {
            ['event_id' => $eventId, 'numScenes' => $numScenes] = $result;
            $numScenesByEvent[$eventId] = $numScenes;
        }

        return $numScenesByEvent;
    }

    public function getNumScenesForEventsInPeriod(Period $period): array {
        $entityManager = $this->getEntityManager();
        $numScenesByEvent = [];
        $query = $entityManager->createQuery(
            'SELECT COUNT(s) numScenes, e.id event_id
            FROM App\Entity\Event e
            JOIN e.scenes s
            WHERE e.period = :period
            GROUP BY e.id'
        );
        $query->setParameter(':period', $period);
        $results = $query->getArrayResult();

        // Convert to array indexed by event id
        foreach ($results as $result) {
            ['event_id' => $eventId, 'numScenes' => $numScenes] = $result;
            $numScenesByEvent[$eventId] = $numScenes;
        }

        return $numScenesByEvent;
    }

    public function findLastPlace(History|Term $event): int
    {

        if (!($event instanceof Event)) {
            throw new \InvalidArgumentException("You must pass a Event parent object to this method!");
        }

        $event = $this->createQueryBuilder('s')
            ->andWhere('s.event = :event')
            ->setParameter('event', $event)
            ->orderBy('s.place', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        return $event->getPlace();
    }

    public function findAllWithPlaceGreaterThanOrEqual(int $place, History|Term $event): array
    {

        if (!($event instanceof Event)) {
            throw new \InvalidArgumentException("You must pass a Event parent object to this method!");
        }

        return $this->createQueryBuilder('s')
            ->andWhere('s.event = :event')
            ->setParameter('event', $event)
            ->andWhere('s.place >= :place')
            ->setParameter('place', $place)
            ->getQuery()
            ->getResult();
    }

    public function findByPlace(int $place, History|Term $event): Term
    {
        if (!($event instanceof Event)) {
            throw new \InvalidArgumentException("You must pass a Event parent object to this method!");
        }

        return $this->createQueryBuilder('s')
            ->andWhere('s.event = :event')
            ->setParameter('event', $event)
            ->andWhere('s.place = :place')
            ->setParameter('place', $place)
            ->getQuery()
            ->getSingleResult();
    }
}
