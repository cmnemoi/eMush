<?php

declare(strict_types=1);

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Equipment\Entity\Dto\WeaponEffect\InflictInjuryWeaponEffectConfigDto;
use Mush\Game\ConfigData\EventConfigData;

/** @codeCoverageIgnore */
final class WeaponEffectConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (EventConfigData::weaponEffectsConfigData() as $data) {
            if ($data instanceof InflictInjuryWeaponEffectConfigDto) {
                $config = $data->toEntityFixture();
            } else {
                $config = $data->toEntity();
            }

            $manager->persist($config);
            $this->addReference($config->getName(), $config);
        }

        $manager->flush();
    }
}
