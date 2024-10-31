<?php

declare(strict_types=1);

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\ConfigData\EventConfigData;

/** @codeCoverageIgnore */
final class WeaponEventConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (EventConfigData::weaponEventConfigData() as $data) {
            $weaponEventConfig = $data->toEntity();

            $manager->persist($weaponEventConfig);
            $this->addReference($weaponEventConfig->getName(), $weaponEventConfig);
        }

        $manager->flush();
    }
}
