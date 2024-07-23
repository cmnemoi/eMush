<?php

declare(strict_types=1);

namespace Mush\Project\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Skill\ConfigData\SkillConfigData;
use Mush\Skill\Entity\SkillConfig;

/** @codeCoverageIgnore */
final class SkillConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (SkillConfigData::getAll() as $skillConfigData) {
            $skillConfig = new SkillConfig($skillConfigData->name);
            $manager->persist($skillConfig);
        }

        $manager->flush();
    }
}
