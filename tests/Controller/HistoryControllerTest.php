<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\Period;
use App\Entity\Player;
use App\Entity\Scene;
use App\Enum\Tone;
use App\Tests\IntegrationTestCase;

class HistoryControllerTest extends IntegrationTestCase
{
    public function dbSetup(): void {
        $player = new Player(
            name: "Test Player",
            history: null,
            active: true,
            legacy: null,
            isLens: false
        );

        $history = History::build(desc: "Test History");
        $history->addPlayer($player);

        $period = Period::build(
            desc: "Test Period",
            tone: Tone::LIGHT,
            place: 0,
            createdBy: $player
        );
        $history->addPeriod($period);

        $event = Event::build(
            desc: "Test Event",
            tone: Tone::LIGHT,
            place: 0,
            createdBy: $player
        );
        $period->addEvent($event);

        $scene = Scene::build(
            desc: "Test Scene",
            tone: Tone::LIGHT,
            place: 0,
            createdBy: $player
        );
        $event->addScene($scene);

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }

    public function testViewHistory(): void {
        $this->dbSetup();

        [$history] = $this->historyRepository->findAll();

        $crawler = $this->client->request('GET', "/history/{$history->getId()}");

        $this->assertSelectorExists('#information');
        $this->assertSelectorExists('#board');
        $this->assertSelectorTextContains("#information .history", "Seed: Test History");
        $this->assertCount(1, $crawler->filter('.players-list > li'));
    }

    public function testGetBoard(): void {
        $this->dbSetup();

        [$history] = $this->historyRepository->findAll();
        [$period] = $this->periodRepository->findAll();

        $crawler = $this->client->request('GET', "/history/{$history->getId()}/board");

        $this->assertSelectorExists("#period-{$period->getId()}");
    }
}
