<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\History;
use App\Entity\Period;
use App\Entity\Player;
use App\Entity\Scene;
use App\Enum\Tone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $numPlayers = 5;
        $faker = \Faker\Factory::create();
        $players = [];

        // Generate players.
        for ($i = 0; $i < $numPlayers; $i += 1) {
            $player = new Player(name: $faker->firstName(), history: null, active: true, legacy: $faker->sentence(4), isLens: false);
            $players[] = $player;
            $manager->persist($player);
        }

        $players[$numPlayers - 1]->setActive(false);

        // Generate history.
        $history = new History();
        $history->setDescription($faker->sentence());
        $history->setFocus($faker->words(3, true));
        $history->setExcluded($faker->words(5));
        $history->setIncluded($faker->words(5));
        array_walk($players, fn (Player $p) => $history->addPlayer($p));

        // Generate periods.
        $numPeriods = 10;
        for ($i = 0; $i < $numPeriods; $i += 1) {
            $numEvents = $faker->numberBetween(1, 7);
            $period = new Period();
            $period->setPlace($i);
            $period->setCreatedBy($players[array_rand($players)]);
            $period->setDescription($i === 0 ? $faker->paragraph(10) : $faker->paragraph());
            $period->setTone($faker->boolean() ? Tone::LIGHT : Tone::DARK);
            $period->setHistory($history);
            $manager->persist($period);

            // Create events for this period.
            for ($x = 0; $x < $numEvents; $x += 1) {
                $event = new Event();
                $numScenes = $faker->numberBetween(1, 5);
                $event->setPlace($x);
                $event->setPeriod($period);
                $event->setCreatedBy($players[array_rand($players)]);
                $event->setDescription($x === 0 ? $faker->paragraph(10) : $faker->paragraph());
                $event->setTone($faker->boolean() ? Tone::LIGHT : Tone::DARK);
                $manager->persist($event);

                // Create scenes for this event.
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
        }


        $manager->persist($history);
        $manager->flush();
    }
}
