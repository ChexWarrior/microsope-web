<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\Period;
use App\Entity\Player;
use App\Entity\Scene;
use App\Enum\Tone;
use App\Tests\IntegrationTestCase;

class SceneControllerTest extends IntegrationTestCase
{
    /**
     * Setups the database for scene controller tests.
     *
     * Creates a history, with one player, with one period which has one event
     * and that event has one existing scene.
     *
     */
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

    public function testGetAddForm(): void {
        $this->dbSetup();

        // Assuming one event in one history.
        $event = $this->eventRepository->findAll()[0];

        // Get add form for a new scene.
        $crawler = $this->client->request('GET', "/scene/{$event->getId()}/add-form");

        $this->assertResponseIsSuccessful();

        // Assert title says "Add New Scene".
        $this->assertSelectorTextContains("strong", "Add New Scene");

        // Assert there are only 2 order options.
        $this->assertCount(2, $crawler->filter('select[name="order"] option'));

        // Assert here is only one player option.
        $this->assertCount(1, $crawler->filter('select[name="player"] option'));

        // Assert description is empty.
        $this->assertSelectorTextContains('textarea', "");

        // Assert hidden parent value is equal to event id.
        $this->assertEquals(
            $event->getId(),
            $crawler->filter('input[name="parent"]')->extract(['value'])[0]
        );
    }

    public function testGetEditForm(): void {
        $this->dbSetup();

        // Assuming only one existing scene.
        [$scene] = $this->sceneRepository->findAll();

        // Get add form for a new scene.
        $crawler = $this->client->request('GET', "/scene/{$scene->getId()}/edit-form");

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains("strong", "Edit Scene " . ($scene->getPlace() + 1));

        $this->assertCount(1, $crawler->filter('select[name="order"] option'));

        $this->assertCount(1, $crawler->filter('select[name="player"] option'));

        $this->assertSelectorTextContains('textarea', $scene->getDescription());

        $this->assertEquals(
            $scene->getEvent()->getId(),
            $crawler->filter('input[name="parent"]')->extract(['value'])[0]
        );
    }

    public function testAddScene(): void {
        $this->dbSetup();

        // Assuming only one player and event.
        [$player] = $this->playerRepository->findAll();
        [$event] = $this->eventRepository->findAll();

        $this->client->request('POST', '/scene/add', [
            'description' => 'New Scene',
            'tone' => 'dark',
            'order' => 1,
            'player' => $player->getId(),
            'parent' => $event->getId(),
        ]);

        // Assert successful creation of scene redirects to getting parent event route.
        $this->assertResponseRedirects("/event/{$event->getId()}?showScenes=1");
        $crawler = $this->client->followRedirect();

        // Verify event container has expected id attribute.
        $this->assertSelectorExists("#event-{$event->getId()}");

        // Verify we have two scene cards.
        $this->assertCount(2, $crawler->filter('div.scene.card'));

        // Verify new card has expected properties.
        $newSceneCard = $crawler->filter('div.scene.card')->last();
        $this->assertEquals('New Scene', $newSceneCard->filter('p')->text(null, true));
        $this->assertEquals('tone dark', $newSceneCard->filter('div.tone')->attr('class'));
    }

    public function testEditScene(): void {
        $this->dbSetup();

        // Assuming only one player and event.
        [$player] = $this->playerRepository->findAll();
        [$event] = $this->eventRepository->findAll();
        [$scene] = $this->sceneRepository->findAll();

        $this->client->request('POST', "/scene/{$scene->getId()}/edit", [
            'description' => 'Updated Scene',
            'tone' => 'dark',
            'order' => 0,
            'player' => $player->getId(),
            'parent' => $event->getId(),
        ]);

        // Assert successful creation of scene redirects to getting parent event route.
        $this->assertResponseRedirects("/event/{$event->getId()}?showScenes=1");
        $crawler = $this->client->followRedirect();

        // Verify event container has expected id attribute.
        $this->assertSelectorExists("#event-{$event->getId()}");

        // Verify we have two scene cards.
        $this->assertCount(1, $crawler->filter('div.scene.card'));

        // Verify new card has expected properties.
        $newSceneCard = $crawler->filter('div.scene.card')->first();
        $this->assertEquals('Updated Scene', $newSceneCard->filter('p')->text(null, true));
        $this->assertEquals('tone dark', $newSceneCard->filter('div.tone')->attr('class'));
    }
}
