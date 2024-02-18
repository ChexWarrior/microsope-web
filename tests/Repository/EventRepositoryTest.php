<?php

namespace App\Tests\Repository;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\Period;
use App\Entity\Player;
use App\Enum\Tone;
use App\Tests\IntegrationTestCase;

class EventRepositoryTest extends IntegrationTestCase
{
    /**
     * Creates db setup for repo tests using place as condition.
     *
     * Creates a history with one player and 2 periods.
     * Period 1 has 3 events.
     * Period 2 has 5 events.
     */
    public function findByPlaceDbSetup(): void {
        $player = new Player(
            name: "Test Player",
            history: null,
            active: true,
            legacy: null,
            isLens: false,
        );

        $history = History::build(desc: "Test history");
        $history->addPlayer($player);

        $period1 = Period::build(
            desc: "Period 1",
            tone: Tone::LIGHT,
            createdBy: $player,
            place: 0
        );
        $period2 = Period::build(
            desc: "Period 2",
            tone: Tone::LIGHT,
            createdBy: $player,
            place: 1
        );
        $history->addPeriod($period1)->addPeriod($period2);

        for ($i = 0; $i < 5; $i += 1) {
            if ($i < 3) {
                $event = Event::build(
                    desc: "Event $i",
                    tone: Tone::LIGHT,
                    createdBy: $player,
                    place: $i
                );
                $period1->addEvent($event);
            }

            $event = Event::build(
                desc: "Event $i",
                tone: Tone::LIGHT,
                createdBy: $player,
                place: $i
            );
            $period2->addEvent($event);
        }

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }

    public function testFindLastPlaceByPeriod(): void {
        $this->findByPlaceDbSetup();

        // Assuming one history.
        $history = $this->historyRepository->findAll()[0];
        [$period1, $period2] = $history->getPeriods();

        $this->assertEquals(2, $this->eventRepository->findLastPlaceByPeriod($period1));
        $this->assertEquals(4, $this->eventRepository->findLastPlaceByPeriod($period2));
    }

    public function testFindAllWithPlaceGreaterThanOrEqual(): void {
        $this->findByPlaceDbSetup();

        // Assuming one history.
        $history = $this->historyRepository->findAll()[0];
        [$period1, $period2] = $history->getPeriods();

        for ($i = 0; $i < 5; $i += 1) {
            if ($i < 3) {
                $events = $this->eventRepository->findAllWithPlaceGreaterThanOrEqual($i, $period1);
                $this->assertEquals(3 - $i, count($events));
            }

            $events = $this->eventRepository->findAllWithPlaceGreaterThanOrEqual($i, $period2);
            $this->assertEquals(5 - $i, count($events));
        }
    }

    public function testFindByPlace(): void {
        $this->findByPlaceDbSetup();

        // Assuming one history.
        $history = $this->historyRepository->findAll()[0];
        [$period1, $period2] = $history->getPeriods();

        for ($i = 0; $i < 5; $i += 1) {
            $expectedDesc = "Event $i";

            if ($i < 3) {
                $period1Event = $this->eventRepository->findByPlace($i, $period1);
                $this->assertEquals($expectedDesc, $period1Event->getDescription());
            }

            $period2Event = $this->eventRepository->findByPlace($i, $period2);
            $this->assertEquals($expectedDesc, $period2Event->getDescription());
        }
    }
}
