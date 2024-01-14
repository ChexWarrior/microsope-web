<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\Period;
use App\Entity\Player;
use App\Entity\Scene;
use App\Enum\Tone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Creates just one period and a few events with Scenes, making it
 * easier to test Scene layout.
 *
 * @package App\DataFixtures
 */
class SceneTestFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['scene'];
    }

    public function load(ObjectManager $manager): void
    {
        $numPlayers = 3;
        $faker = Factory::create();
        $players = [];

        // Gen players.
        for ($i = 0; $i < $numPlayers; $i += 1) {
            $player = new Player(name: $faker->firstName(), history: null, active: true, legacy: $faker->sentence(4), isLens: false);
            $players[] = $player;
            $manager->persist($player);
        }

        // Gen history.
        $history = new History();
        $history->setDescription($faker->sentence());
        $history->setFocus($faker->words(3, true));
        $history->setExcluded($faker->words(5));
        $history->setIncluded($faker->words(5));
        array_walk($players, fn (Player $p) => $history->addPlayer($p));

        // Gen two periods.
        $firstPeriod = new Period();
        $firstPeriod->setPlace(0);
        $firstPeriod->setCreatedBy($players[array_rand($players)]);
        $firstPeriod->setDescription($faker->paragraph());
        $firstPeriod->setTone($faker->boolean() ? Tone::LIGHT : Tone::DARK);
        $firstPeriod->setHistory($history);
        $manager->persist($firstPeriod);

        $secondPeriod = new Period();
        $secondPeriod->setPlace(1);
        $secondPeriod->setCreatedBy($players[array_rand($players)]);
        $secondPeriod->setDescription($faker->paragraph());
        $secondPeriod->setTone($faker->boolean() ? Tone::LIGHT : Tone::DARK);
        $secondPeriod->setHistory($history);
        $manager->persist($secondPeriod);

        // Generate two events for first period.
        for ($x = 0; $x < 2; $x += 1) {
            $event = new Event();
            $numScenes = $faker->numberBetween(2, 3);
            $event->setPlace($x);
            $event->setPeriod($firstPeriod);
            $event->setCreatedBy($players[array_rand($players)]);
            $event->setDescription($faker->paragraph());
            $event->setTone($faker->boolean() ? Tone::LIGHT : Tone::DARK);
            $manager->persist($event);

            // Create scenes for each event.
            for ($z = 0; $z < $numScenes; $z += 1) {
                $scene = new Scene();
                $scene->setPlace($z);
                $scene->setEvent($event);
                $scene->setCreatedBy($players[array_rand($players)]);
                $scene->setDescription($faker->paragraph());
                $scene->setTone($faker->boolean() ? Tone::LIGHT : Tone::DARK);
                $manager->persist($scene);
            }
        }

        $manager->persist($history);
        $manager->flush();
    }
}