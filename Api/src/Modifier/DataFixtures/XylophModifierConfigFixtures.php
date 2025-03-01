<?php

declare(strict_types=1);

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/** @codeCoverageIgnore */
final class XylophModifierConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // TODO: Add modifiers from XylophEntry here!
    }
}
