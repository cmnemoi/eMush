<?php

declare(strict_types=1);

namespace Mush\Equipment\DataFixtures\WeaponEffect;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\ConfigData\EventConfigData;

/** @codeCoverageIgnore */
final class ModifyMaxDamageWeaponEffectConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (EventConfigData::modifyMaxDamageWeaponEffectConfigData() as $data) {
            $config = $data->toEntity();

            $manager->persist($config);
            $this->addReference($config->getName(), $config);
        }

        $manager->flush();
    }
}
