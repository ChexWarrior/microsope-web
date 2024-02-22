<?php

namespace App\DataFixtures;

use App\Entity\History;
use App\Entity\Player;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Creates a new history with no periods, events or scenes.
 */
class EmptyHistoryFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['empty'];
    }

    public function load(ObjectManager $manager): void {
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

        $manager->persist($history);
        $manager->flush();
    }
}
