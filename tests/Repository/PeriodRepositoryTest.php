<?php

namespace App\Tests\Repository;

use App\Entity\History;
use App\Entity\Period;
use App\Entity\Player;
use App\Enum\Tone;
use App\Tests\IntegrationTestCase;

class PeriodRepositoryTest extends IntegrationTestCase
{
    /**
     * Creates db setup for tests using place as condition.
     *
     * Creates a history with one player and 5 periods.
     */
    public function findByPlaceDbSetup(): void {
        $player = new Player(
            name: "Test Player",
            history: null,
            active: true,
            legacy: null,
            isLens: false,
        );

        $history = History::build(desc: "Test History");
        $history->addPlayer($player);

        // Create 5 periods for history.
        for ($i = 0; $i < 5; $i += 1) {
            $period = Period::build(
                desc: "Period $i",
                tone: Tone::LIGHT,
                createdBy: $player,
                place: $i
            );
            $history->addPeriod($period);
        }

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }

    public function testFindLastPlace(): void {
        $this->findByPlaceDbSetup();

        $history = $this->historyRepository->findAll()[0];

        $this->assertEquals(4, $this->periodRepository->findLastPlace($history));
    }

    public function testFindAllWithPlaceGreaterThanOrEqual(): void {
        $this->findByPlaceDbSetup();

        $history = $this->historyRepository->findAll()[0];

        for ($i = 0; $i < 5; $i += 1) {
            $periods = $this->periodRepository->findAllWithPlaceGreaterThanOrEqual($i, $history);
            $this->assertEquals(5 - $i, count($periods));
        }
    }

    public function testFindByPlace(): void {
        $this->findByPlaceDbSetup();

        $history = $this->historyRepository->findAll()[0];

        for ($i = 0; $i < 5; $i += 1) {
            $period = $this->periodRepository->findByPlace($i, $history);
            $this->assertEquals("Period $i", $period->getDescription());
        }
    }
}
