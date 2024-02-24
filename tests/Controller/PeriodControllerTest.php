<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\Period;
use App\Entity\Player;
use App\Entity\Scene;
use App\Enum\Tone;
use App\Tests\IntegrationTestCase;

class PeriodControllerTest extends IntegrationTestCase
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

        [$history] = $this->historyRepository->findAll();

        $crawler = $this->client->request('GET', "/period/{$history->getId()}/add-form");

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains("strong", "Add New Period");
        $this->assertCount(2, $crawler->filter('select[name="order"] option'));
        $this->assertSelectorTextContains('textarea', "");
        $this->assertEquals(
            $history->getId(),
            $crawler->filter('input[name="parent"]')->extract(['value'])[0]
        );
    }

    public function testGetEditForm(): void {
        $this->dbSetup();

        [$period] = $this->periodRepository->findAll();

        $crawler = $this->client->request('GET', "/period/{$period->getId()}/edit-form");

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains("strong", "Edit Period: " . ($period->getPlace() + 1));
        $this->assertCount(1, $crawler->filter('select[name="order"] option'));
        $this->assertSelectorTextContains('textarea', $period->getDescription());
        $this->assertEquals(
            $period->getHistory()->getId(),
            $crawler->filter('input[name="parent"]')->extract(['value'])[0]
        );
    }

    public function testAddPeriod(): void {
        $this->dbSetup();

        // Assuming one player and period.
        [$player] = $this->playerRepository->findAll();
        [$history] = $this->historyRepository->findAll();

        $this->client->request('POST', '/period/add', [
            'description' => 'New Period',
            'tone' => 'dark',
            'order' => 1,
            'player' => $player->getId(),
            'parent' => $history->getId(),
        ]);

        $this->assertResponseRedirects("/history/{$history->getId()}/board");
        $crawler = $this->client->followRedirect();

        $this->assertSelectorExists("#period-{$history->getId()}");

        $this->assertCount(2, $crawler->filter('div.period.card'));

        $newPeriodCard = $crawler->filter('div.period.card')->last();
        $this->assertEquals('New Period', $newPeriodCard->filter('p')->text(null, true));
        $this->assertEquals('tone dark', $newPeriodCard->filter('div.tone')->attr('class'));
    }

    public function testEditPeriod(): void {
        $this->dbSetup();

        // Assuming one player and period.
        [$player] = $this->playerRepository->findAll();
        [$history] = $this->historyRepository->findAll();
        [$period] = $this->periodRepository->findAll();

        $this->client->request('POST', "/period/{$period->getId()}/edit", [
            'description' => 'Updated Period',
            'tone' => 'dark',
            'order' => 0,
            'player' => $player->getId(),
            'parent' => $history->getId(),
        ]);

        $this->assertResponseRedirects("/history/{$history->getId()}/board");
        $crawler = $this->client->followRedirect();

        $this->assertSelectorExists("#period-{$history->getId()}");

        $this->assertCount(1, $crawler->filter('div.period.card'));

        $editedPeriodCard = $crawler->filter('div.period.card')->last();
        $this->assertEquals('Updated Period', $editedPeriodCard->filter('p')->text(null, true));
        $this->assertEquals('tone dark', $editedPeriodCard->filter('div.tone')->attr('class'));
    }

        /**
     * @dataProvider invalidPeriodDataProvider
     */
    public function testInvalidEditPeriod(array $editData, array $expectedErrors): void {
        $this->dbSetup();

        // Assuming one player and period.
        [$history] = $this->historyRepository->findAll();
        [$period] = $this->periodRepository->findAll();

        $dataToSend = array_merge(['parent' => $history->getId()], $editData);
        $crawler = $this->client->request('POST', "/period/{$period->getId()}/edit", $dataToSend);

        $this->assertResponseStatusCodeSame(400);
        foreach ($expectedErrors as $errorMsg) {
            $this->assertAnySelectorTextContains('#term-errors', $errorMsg);
        }
    }

    /**
     * @dataProvider invalidPeriodDataProvider
     */
    public function testInvalidAddPeriod(array $addData, array $expectedErrors): void {
        $this->dbSetup();

        // Assuming one player and period.
        [$history] = $this->historyRepository->findAll();

        $dataToSend = array_merge(['parent' => $history->getId()], $addData);
        $crawler = $this->client->request('POST', "/period/add", $dataToSend);

        $this->assertResponseStatusCodeSame(400);
        foreach ($expectedErrors as $errorMsg) {
            $this->assertAnySelectorTextContains('#term-errors', $errorMsg);
        }
    }

    public function testGetPeriod(): void {
        $this->dbSetup();

        [$period] = $this->periodRepository->findAll();

        $crawler = $this->client->request('GET', "/period/{$period->getId()}");

        $this->assertSelectorExists("#period-{$period->getId()}");

        $periodCard = $crawler->filter('div.period.card')->last();
        $this->assertEquals('Test Period', $periodCard->filter('p')->text(null, true));
        $this->assertEquals('tone light', $periodCard->filter('div.tone')->attr('class'));
    }

    public function invalidPeriodDataProvider() {
        $validPlaceWithBadData = [
            'description' => '',
            'tone' => '',
            'order' => 0,
            'player' => null,
        ];

        $validPlaceWithBadDataMsgs = [
            'description -',
            'tone -',
            'createdBy -',
        ];

        $invalidPlaceData = [
            'description' => '',
            'tone' => '',
            'order' => -1,
            'player' => null,
        ];

        $invalidPlaceDataMsgs = [
            'place -',
        ];

        return [
            [$validPlaceWithBadData, $validPlaceWithBadDataMsgs],
            [$invalidPlaceData, $invalidPlaceDataMsgs],
        ];
    }
}
