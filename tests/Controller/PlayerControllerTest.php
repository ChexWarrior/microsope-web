<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\Period;
use App\Entity\Player;
use App\Entity\Scene;
use App\Enum\Tone;
use App\Tests\IntegrationTestCase;

class PlayerControllerTest extends IntegrationTestCase
{
    public function dbSetup(): void {
        $player1 = new Player(
            name: "Player 1",
            history: null,
            active: true,
            legacy: null,
            isLens: false
        );

        $player2 = new Player(
            name: "Player 2",
            history: null,
            active: true,
            legacy: null,
            isLens: true
        );

        $history = History::build(desc: "Test History");
        $history->addPlayer($player1);
        $history->addPlayer($player2);

        $period = Period::build(
            desc: "Test Period",
            tone: Tone::LIGHT,
            place: 0,
            createdBy: $player1
        );
        $history->addPeriod($period);

        $event = Event::build(
            desc: "Test Event",
            tone: Tone::LIGHT,
            place: 0,
            createdBy: $player1
        );
        $period->addEvent($event);

        $scene = Scene::build(
            desc: "Test Scene",
            tone: Tone::LIGHT,
            place: 0,
            createdBy: $player1
        );
        $event->addScene($scene);

        $this->entityManager->persist($history);
        $this->entityManager->flush();
    }

    public function testLensEdit(): void {
        $this->dbSetup();
        [$history] = $this->historyRepository->findAll();
        $players = $this->playerRepository->findAllByHistory($history);
        [$player1] = array_values(array_filter($players, fn($p) => $p->getName() == "Player 1"));
        [$player2] = array_values(array_filter($players, fn($p) => $p->getName() == "Player 2"));

        // Verify player 2 is lens as initially set and player 1 is not.
        $this->assertTrue($player2->isLens());
        $this->assertFalse($player1->isLens());

        // Change player 1 to be lens.
        $lensEditData = [
            'name' => 'Player 1',
            'lens' => 'true',
            'active' => 'true',
        ];
        $this->client->request('POST', "/player/{$player1->getId()}/edit", $lensEditData);
        $this->assertResponseRedirects("/history/{$history->getId()}");

        // Grab updated players from db.
        $players = $this->playerRepository->findAllByHistory($history);
        [$player1] = array_values(array_filter($players, fn($p) => $p->getName() == "Player 1"));
        [$player2] = array_values(array_filter($players, fn($p) => $p->getName() == "Player 2"));

        // Verify player 2 is no longer lens and player 1 is.
        $this->assertFalse($player2->isLens());
        $this->assertTrue($player1->isLens());
    }
}