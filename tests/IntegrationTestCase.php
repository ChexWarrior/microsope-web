<?php

namespace App\Tests;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\Period;
use App\Entity\Player;
use App\Entity\Scene;
use App\Repository\EventRepository;
use App\Repository\HistoryRepository;
use App\Repository\PeriodRepository;
use App\Repository\PlayerRepository;
use App\Repository\SceneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class IntegrationTestCase extends WebTestCase {
    protected ?EntityManagerInterface $entityManager;
    protected SceneRepository $sceneRepository;
    protected HistoryRepository $historyRepository;
    protected EventRepository $eventRepository;
    protected PeriodRepository $periodRepository;
    protected PlayerRepository $playerRepository;
    protected KernelBrowser $client;

    protected function setUp(): void {
        $kernel = self::bootKernel();

        $client = $kernel->getContainer()->get('test.client');
        $this->client = self::getClient($client);
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')->getManager();
        $this->sceneRepository = $this->entityManager
            ->getRepository(Scene::class);
        $this->historyRepository = $this->entityManager
            ->getRepository(History::class);
        $this->eventRepository = $this->entityManager
            ->getRepository(Event::class);
        $this->periodRepository = $this->entityManager
            ->getRepository(Period::class);
        $this->playerRepository = $this->entityManager
            ->getRepository(Player::class);
    }

    protected function tearDown(): void {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
