<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\Scene;
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
class SceneRepository extends ServiceEntityRepository
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

//    /**
//     * @return Scene[] Returns an array of Scene objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Scene
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
