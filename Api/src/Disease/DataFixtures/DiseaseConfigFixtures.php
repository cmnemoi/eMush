<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Enum\DiseaseEnum;

class DiseaseConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $acidReflux = new DiseaseConfig();
        $acidReflux->setName(DiseaseEnum::ACID_REFLUX);

        $manager->persist($acidReflux);

        $foodPoisoning = new DiseaseConfig();
        $foodPoisoning->setName(DiseaseEnum::FOOD_POISONING);

        $manager->persist($foodPoisoning);

        $tapeworm = new DiseaseConfig();
        $tapeworm->setName(DiseaseEnum::TAPEWORM);

        $manager->persist($tapeworm);

        $gastroenteritis = new DiseaseConfig();
        $gastroenteritis->setName(DiseaseEnum::GASTROENTERITIS);

        $manager->persist($gastroenteritis);

        $manager->flush();

        $this->addReference(DiseaseEnum::ACID_REFLUX, $acidReflux);
        $this->addReference(DiseaseEnum::FOOD_POISONING, $foodPoisoning);
        $this->addReference(DiseaseEnum::TAPEWORM, $tapeworm);
        $this->addReference(DiseaseEnum::GASTROENTERITIS, $gastroenteritis);
    }
}
