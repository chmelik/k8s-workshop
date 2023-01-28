<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Qwerty;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QwertyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (range(1, 10) as $i) {
            $qwerty = new Qwerty($i, sprintf('Name %d', $i));
            $manager->persist($qwerty);
        }

        $manager->flush();
    }
}
