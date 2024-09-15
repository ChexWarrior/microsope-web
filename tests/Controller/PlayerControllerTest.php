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


    public function testValidPlayerUpdate() {
        $this->dbSetup();
        [$history] = $this->historyRepository->findAll();
        $players = $this->playerRepository->findAllByHistory($history);
        [$player1] = array_values(array_filter($players, fn($p) => $p->getName() == "Player 1"));
        $editData = [
            'name' => 'Player Alpha',
            'legacy' => 'Test Legacy',
            'active' => 'true',
        ];

        // Update player 1.
        $this->client->request('POST', "/player/{$player1->getId()}/edit", $editData);
        $this->assertResponseRedirects("/history/{$history->getId()}");

        $players = $this->playerRepository->findAllByHistory($history);
        [$player1] = array_values(array_filter($players, fn($p) => $p->getId() == 1));

        $this->assertEquals('Player Alpha', $player1->getName());
        $this->assertEquals('Test Legacy', $player1->getLegacy());
        $this->assertTrue($player1->isActive());
        $this->assertFalse($player1->isLens());
    }

    public function testInvalidPlayerUpdate() {
        $this->dbSetup();
        [$history] = $this->historyRepository->findAll();
        $players = $this->playerRepository->findAllByHistory($history);
        [$player1] = array_values(array_filter($players, fn($p) => $p->getName() == "Player 1"));
        $invalidData = [
            'name' => '',
            'legacy' => 'Test Legacy',
            'active' => 'true',
        ];
        $expectedErrors = [
            'name -',
        ];

        // Update player 1.
        $this->client->request('POST', "/player/{$player1->getId()}/edit", $invalidData);
        $this->assertResponseStatusCodeSame(400);
        foreach ($expectedErrors as $error) {
            $this->assertAnySelectorTextContains('.form-errors', $error);
        }
    }

    public function testValidPlayerAdd() {
        $this->dbSetup();
        [$history] = $this->historyRepository->findAll();
        $players = $this->playerRepository->findAllByHistory($history);

        // Two players exist with default db setup.
        $this->assertCount(2, $players);
        $data = [
            'name' => 'Player 3',
            'legacy' => 'New Legacy',
            'active' => 'true',
            'history_id' => $history->getId(),
        ];

        // Add player 2.
        $this->client->request('POST', "/player/add", $data);
        $this->assertResponseIsSuccessful();

        $players = $this->playerRepository->findAllByHistory($history);
        $this->assertCount(3, $players);

        [$player3] = array_values(array_filter($players, fn($p) => $p->getId() == 3));
        $this->assertEquals('Player 3', $player3->getName());
        $this->assertEquals('New Legacy', $player3->getLegacy());
        $this->assertTrue($player3->isActive());
    }

    public function testInvalidPlayerAdd() {
        $this->dbSetup();
        [$history] = $this->historyRepository->findAll();
        $players = $this->playerRepository->findAllByHistory($history);

        // Two players exist with default db setup.
        $this->assertCount(2, $players);
        $data = [
            'name' => '',
            'legacy' => 'New Legacy',
            'active' => 'true',
            'history_id' => $history->getId(),
        ];

        $expectedErrors = [
            'name -',
        ];

        $this->client->request('POST', "/player/add", $data);
        $this->assertResponseStatusCodeSame(400);
        foreach ($expectedErrors as $error) {
            $this->assertAnySelectorTextContains('.form-errors', $error);
        }
    }

    public function testPlayerAddNewLens() {
        $this->dbSetup();
        [$history] = $this->historyRepository->findAll();
        $players = $this->playerRepository->findAllByHistory($history);

        // Two players exist with default db setup.
        $this->assertCount(2, $players);
        $data = [
            'name' => 'Player 3',
            'legacy' => 'New Legacy',
            'active' => 'true',
            'history_id' => $history->getId(),
            'lens' => true,
        ];

        // Add player 2.
        $this->client->request('POST', "/player/add", $data);
        $this->assertResponseIsSuccessful();

        $players = $this->playerRepository->findAllByHistory($history);
        $this->assertCount(3, $players);

        [$player3] = array_values(array_filter($players, fn($p) => $p->getId() == 3));
        $otherPlayers = array_values(array_filter($players, fn($p) => $p->getId() != 3));
        $this->assertEquals('Player 3', $player3->getName());
        $this->assertEquals('New Legacy', $player3->getLegacy());
        $this->assertTrue($player3->isActive());
        $this->assertTrue($player3->isLens());
        foreach ($otherPlayers as $otherPlayer) {
            $this->assertFalse($otherPlayer->isLens());
        }

    }
}