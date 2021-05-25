<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\DiseaseCause;
use Mush\Disease\Enum\DiseaseCauseEnum;

class DiseaseCauseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $spoiledFood = new DiseaseCause();
        $spoiledFood->setName(DiseaseCauseEnum::SPOILED_FOOD);
        $spoiledFood->setRate(50);

        $manager->persist($spoiledFood);

        $manager->flush();

        $this->addReference(DiseaseCauseEnum::SPOILED_FOOD, $spoiledFood);
    }
}
