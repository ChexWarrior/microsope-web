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

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['main'];
    }

    public function load(ObjectManager $manager): void
    {
        $numPlayers = 5;
        $faker = \Faker\Factory::create();
        $players = [];

        // Generate players.
        for ($i = 0; $i < $numPlayers; $i += 1) {
            $player = new Player(
                name: $faker->firstName(),
                history: null,
                active: true,
                legacy: $faker->sentence(4),
                isLens: false
            );
            $players[] = $player;
        }

        $players[$numPlayers - 1]->setActive(false);

        // Generate history.
        $history = History::build(
            desc: $faker->sentence(),
            focus: $faker->words(3, true),
            included: $faker->words(5),
            excluded: $faker->words(5)
        );
        array_walk($players, fn (Player $p) => $history->addPlayer($p));

        // Generate periods.
        $numPeriods = 10;
        for ($i = 0; $i < $numPeriods; $i += 1) {
            $numEvents = $faker->numberBetween(1, 7);
            $period = Period::build(
                desc: $i === 0 ? $faker->paragraph(10) : $faker->paragraph(),
                tone: $faker->boolean() ? Tone::LIGHT : Tone::DARK,
                place: $i,
                createdBy: $players[array_rand($players)]
            );
            $history->addPeriod($period);

            // Create events for this period.
            for ($x = 0; $x < $numEvents; $x += 1) {
                $numScenes = $faker->numberBetween(1, 5);
                $event = Event::build(
                    desc: $x === 0 ? $faker->paragraph(10) : $faker->paragraph(),
                    tone: $faker->boolean() ? Tone::LIGHT : Tone::DARK,
                    place: $x,
                    createdBy: $players[array_rand($players)]
                );
                $period->addEvent($event);

                // Create scenes for this event.
                for ($z = 0; $z < $numScenes; $z += 1) {
                    $scene = Scene::build(
                        desc: $faker->paragraph(),
                        tone: $faker->boolean() ? Tone::LIGHT : Tone::DARK,
                        place: $z,
                        createdBy:$players[array_rand($players)]
                    );
                    $event->addScene($scene);
                }
            }
        }

        $manager->persist($history);
        $manager->flush();
    }
}
