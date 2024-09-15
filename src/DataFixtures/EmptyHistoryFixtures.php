<?php

namespace App\DataFixtures;

use App\Entity\History;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Creates a new history no players or terms.
 */
class EmptyHistoryFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['empty'];
    }

    public function load(ObjectManager $manager): void {
        $faker = \Faker\Factory::create();
        // Generate history.
        $history = History::build(
            desc: $faker->sentence(),
            focus: $faker->words(3, true),
            included: $faker->words(5),
            excluded: $faker->words(5)
        );

        $manager->persist($history);
        $manager->flush();
    }
}
