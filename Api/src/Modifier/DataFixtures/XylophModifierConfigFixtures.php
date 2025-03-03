<?php

declare(strict_types=1);

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;

/** @codeCoverageIgnore */
final class XylophModifierConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $kivancModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::DOUBLE_DECODE_BASE_SIGNAL)
        );
        $manager->persist($kivancModifier);
        $this->addReference($kivancModifier->getName(), $kivancModifier);

        $manager->flush();
    }
}
