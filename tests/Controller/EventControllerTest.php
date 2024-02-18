<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\Period;
use App\Entity\Player;
use App\Entity\Scene;
use App\Enum\Tone;
use App\Tests\IntegrationTestCase;

class EventControllerTest extends IntegrationTestCase
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

    public function testGetAddForm(): void {
        $this->dbSetup();

        [$period] = $this->periodRepository->findAll();

        $crawler = $this->client->request('GET', "/event/{$period->getId()}/add-form");

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains("strong", "Add New Event");
        $this->assertCount(2, $crawler->filter('select[name="order"] option'));
        $this->assertSelectorTextContains('textarea', "");
        $this->assertEquals(
            $period->getId(),
            $crawler->filter('input[name="parent"]')->extract(['value'])[0]
        );
    }

    public function testGetEditForm(): void {
        $this->dbSetup();

        [$event] = $this->eventRepository->findAll();

        $crawler = $this->client->request('GET', "/event/{$event->getId()}/edit-form");

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains("strong", "Edit Event: " . ($event->getPlace() + 1));
        $this->assertCount(1, $crawler->filter('select[name="order"] option'));
        $this->assertSelectorTextContains('textarea', $event->getDescription());
        $this->assertEquals(
            $event->getPeriod()->getId(),
            $crawler->filter('input[name="parent"]')->extract(['value'])[0]
        );

    }

    public function testAddEvent(): void {
        $this->dbSetup();

        // Assuming one player and period.
        [$player] = $this->playerRepository->findAll();
        [$period] = $this->periodRepository->findAll();

        $this->client->request('POST', '/event/add', [
            'description' => 'New Event',
            'tone' => 'dark',
            'order' => 1,
            'player' => $player->getId(),
            'parent' => $period->getId(),
        ]);

        $this->assertResponseRedirects("/period/{$period->getId()}");
        $crawler = $this->client->followRedirect();

        $this->assertSelectorExists("#period-{$period->getId()}");

        $this->assertCount(2, $crawler->filter('div.event.card'));

        $newEventCard = $crawler->filter('div.event.card')->last();
        $this->assertEquals('New Event', $newEventCard->filter('p')->text(null, true));
        $this->assertEquals('tone dark', $newEventCard->filter('div.tone')->attr('class'));
    }

    public function testEditEvent(): void {
        $this->dbSetup();

        // Assuming one player and period.
        [$player] = $this->playerRepository->findAll();
        [$period] = $this->periodRepository->findAll();
        [$event] = $this->eventRepository->findAll();

        $this->client->request('POST', "/event/{$event->getId()}/edit", [
            'description' => 'Updated Event',
            'tone' => 'dark',
            'order' => 0,
            'player' => $player->getId(),
            'parent' => $period->getId(),
        ]);

        $this->assertResponseRedirects("/period/{$period->getId()}");
        $crawler = $this->client->followRedirect();

        $this->assertSelectorExists("#period-{$period->getId()}");

        $this->assertCount(1, $crawler->filter('div.event.card'));

        $editedEventCard = $crawler->filter('div.event.card')->last();
        $this->assertEquals('Updated Event', $editedEventCard->filter('p')->text(null, true));
        $this->assertEquals('tone dark', $editedEventCard->filter('div.tone')->attr('class'));
    }

    public function testGetEvent(): void {
        $this->dbSetup();

        [$event] = $this->eventRepository->findAll();

        $crawler = $this->client->request('GET', "/event/{$event->getId()}?showScenes=0");

        $this->assertSelectorExists("#event-{$event->getId()}");

        $eventCard = $crawler->filter('div.event.card')->last();
        $this->assertEquals('Test Event', $eventCard->filter('p')->text(null, true));
        $this->assertEquals('tone light', $eventCard->filter('div.tone')->attr('class'));
    }
}
