<?php

namespace App\Tests\Respository;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\Period;
use App\Entity\Player;
use App\Entity\Scene;
use App\Enum\Tone;
use App\Tests\DatabaseTestCase;

class SceneRepositoryTest extends DatabaseTestCase
{
    /**
     * Sets up the database to test the SceneRepository methods which
     * get the number of scenes for each event in a History or a Period.
     *
     * @return void
     */
    public function getNumScenesDbSetup(): void {
        $player = new Player(
            name: "Test Player",
            history: null,
            active: true,
            legacy: null,
            isLens: false
        );

        $history = History::build(desc: "Test History");
        $history->addPlayer($player);

        // Create 2 periods.
        for ($x = 0; $x < 2; $x += 1) {
            $period = new Period();
            $period = Period::build(
                desc: "Test Period ($x)",
                tone: Tone::LIGHT,
                place: $x,
                createdBy: $player
            );
            $history->addPeriod($period);

            // Create 5 events for each period.
            for ($i = 0; $i < 5; $i += 1) {
                $event = Event::build(
                    desc: "Test Event ($i) for Period ($x)",
                    tone: Tone::LIGHT,
                    place: $i,
                    createdBy: $player
                );
                $period->addEvent($event);

                // Create $i scenes for each event.
                for ($j = 0; $j < $i; $j += 1) {
                    $scene = Scene::build(
                        desc: "Test Scene ($j) for Event ($i)",
                        tone: Tone::LIGHT,
                        place: $j,
                        createdBy: $player
                    );
                    $event->addScene($scene);
                }
            }
        }

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }

    /**
     * Sets up the database to test SceneRepository methods which
     * find Scenes based on their place property.
     *
     * Creates one history with two events, event 1 has 3 scenes
     * and event 2 has 5 scenes.
     *
     * @return void
     */
    public function findByPlaceDbSetup(): void {
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
            createdBy: $player,
            place: 0
        );
        $history->addPeriod($period);

        $event1 = Event::build(
            desc: "Test Event 1",
            tone: Tone::LIGHT,
            createdBy: $player,
            place: 0
        );
        $event2 = Event::build(
            desc: "Test Event 2",
            tone: Tone::DARK,
            createdBy: $player,
            place: 1
        );
        $period->addEvent($event1)->addEvent($event2);

        // Create 3 scenes for $event 1.
        for($i = 0; $i < 3; $i += 1) {
            $scene = Scene::build(
                desc: "Scene $i",
                tone: Tone::LIGHT,
                createdBy: $player,
                place: $i,
            );
            $event1->addScene($scene);
        }

        // Create 5 scenes for $event 2.
        for($j = 0; $j < 5; $j += 1) {
            $scene = Scene::build(
                desc: "Scene $j",
                tone: Tone::LIGHT,
                createdBy: $player,
                place: $j,
            );
            $event2->addScene($scene);
        }

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }

    public function testFindAllWithPlaceGreaterThanOrEqual(): void {
        $this->findByPlaceDbSetup();

        // Assuming only one history in db.
        $history = $this->historyRepository->findAll()[0];
        // Assume one period.
        [$event1, $event2] = $history->getPeriods()[0]->getEvents();

        for ($i = 0; $i < 5; $i += 1) {
            if ($i < 3) {
                $scenes = $this->sceneRepository->findAllWithPlaceGreaterThanOrEqual($i, $event1);
                $this->assertEquals(3 - $i, count($scenes));
            }

            $scenes = $this->sceneRepository->findAllWithPlaceGreaterThanOrEqual($i, $event2);
            $this->assertEquals(5 - $i, count($scenes));
        }
    }

    public function testLastPlaceByEvent(): void {
        $this->findByPlaceDbSetup();

        // Assuming only one history in db.
        $history = $this->historyRepository->findAll()[0];
        // Assume one period.
        [$event1, $event2] = $history->getPeriods()[0]->getEvents();

        $this->assertEquals(2, $this->sceneRepository->findLastPlaceByEvent($event1));
        $this->assertEquals(4, $this->sceneRepository->findLastPlaceByEvent($event2));
    }

    public function testGetByPlace(): void {
        $this->findByPlaceDbSetup();

        // Assuming only one history in db.
        $history = $this->historyRepository->findAll()[0];
        // Assume one period.
        [$event1, $event2] = $history->getPeriods()[0]->getEvents();

        // Verify can find each period in each event by place with expected title.
        for ($i = 0; $i < 5; $i += 1) {
            $expectedDesc = "Scene $i";
            // Check all 3 scenes in event 1.
            if ($i < 3) {
                $event1Scene = $this->sceneRepository->findByPlace($i, $event1);
                $this->assertEquals($expectedDesc, $event1Scene->getDescription());
            }

            $event2Scene = $this->sceneRepository->findByPlace($i, $event2);
            $this->assertEquals($expectedDesc, $event2Scene->getDescription());
        }
    }

    public function testGetNumScenesForEventsInHistory(): void {
        // Setup the database for test.
        $this->getNumScenesDbSetup();

        // We are assuming there is only one history in DB.
        $history = $this->historyRepository->findAll()[0];
        $events = $this->eventRepository->findAll();
        $result = $this->sceneRepository->getNumScenesForEventsInHistory($history);

        /** @var Event $event */
        foreach ($events as $event) {
            // Verify each event has the expected amount of scenes.
            if (array_key_exists($event->getId(), $result)) {
                $this->assertEquals(
                    count($event->getScenes()), $result[$event->getId()]
                );
            // If there isn't an entry for an event it should have no scenes.
            } else {
                $this->assertEquals(0, count($event->getScenes()));
            }
        }
    }

    public function testGetNumScenesForEventsInPeriod(): void {
        // Setup the database for test.
        $this->getNumScenesDbSetup();

        // We are assuming there is only one history in DB.
        $periods = $this->periodRepository->findAll();
        $events = $this->eventRepository->findAll();

        foreach ($periods as $period) {
            $result = $this->sceneRepository->getNumScenesForEventsInPeriod($period);

            /** @var Event $event */
            foreach ($events as $event) {
                // Only test events in this period.
                if ($event->getPeriod()->getId() == $period->getId()) {
                    // Verify each event has the expected amount of scenes.
                    if (array_key_exists($event->getId(), $result)) {
                        $this->assertEquals(
                            count($event->getScenes()), $result[$event->getId()]
                        );
                    // If there isn't an entry for an event it should have no scenes.
                    } else {
                        $this->assertEquals(0, count($event->getScenes()));
                    }
                }
            }
        }
    }
}