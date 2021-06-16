<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\TypeEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class DisorderConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $agoraphobia = new DiseaseConfig();
        $agoraphobia->setGameConfig($gameConfig);
        $agoraphobia->setName(DisorderEnum::AGORAPHOBIA);
        $agoraphobia->setType(TypeEnum::DISORDER);

        $manager->persist($agoraphobia);

        $ailurophobia = new DiseaseConfig();
        $ailurophobia->setGameConfig($gameConfig);
        $ailurophobia->setName(DisorderEnum::AILUROPHOBIA);
        $ailurophobia->setType(TypeEnum::DISORDER);

        $manager->persist($ailurophobia);

        $chronicMigraine = new DiseaseConfig();
        $chronicMigraine->setGameConfig($gameConfig);
        $chronicMigraine->setName(DisorderEnum::CHRONIC_MIGRAINE);
        $chronicMigraine->setType(TypeEnum::DISORDER);

        $manager->persist($chronicMigraine);

        $chronicVertigo = new DiseaseConfig();
        $chronicVertigo->setGameConfig($gameConfig);
        $chronicVertigo->setName(DisorderEnum::CHRONIC_VERTIGO);
        $chronicVertigo->setType(TypeEnum::DISORDER);

        $manager->persist($chronicVertigo);

        $coprolalia = new DiseaseConfig();
        $coprolalia->setGameConfig($gameConfig);
        $coprolalia->setName(DisorderEnum::COPROLALIA);
        $coprolalia->setType(TypeEnum::DISORDER);

        $manager->persist($coprolalia);

        $crabism = new DiseaseConfig();
        $crabism->setGameConfig($gameConfig);
        $crabism->setName(DisorderEnum::CRABISM);
        $crabism->setType(TypeEnum::DISORDER);

        $manager->persist($crabism);

        $depression = new DiseaseConfig();
        $depression->setGameConfig($gameConfig);
        $depression->setName(DisorderEnum::DEPRESSION);
        $depression->setType(TypeEnum::DISORDER);

        $manager->persist($depression);

        $paranoia = new DiseaseConfig();
        $paranoia->setGameConfig($gameConfig);
        $paranoia->setName(DisorderEnum::PARANOIA);
        $paranoia->setType(TypeEnum::DISORDER);

        $manager->persist($paranoia);

        $psychoticEpisode = new DiseaseConfig();
        $psychoticEpisode->setGameConfig($gameConfig);
        $psychoticEpisode->setName(DisorderEnum::PSYCOTIC_EPISODE);
        $psychoticEpisode->setType(TypeEnum::DISORDER);

        $manager->persist($psychoticEpisode);

        $spleen = new DiseaseConfig();
        $spleen->setGameConfig($gameConfig);
        $spleen->setName(DisorderEnum::SPLEEN);
        $spleen->setType(TypeEnum::DISORDER);

        $manager->persist($spleen);

        $vertigo = new DiseaseConfig();
        $vertigo->setGameConfig($gameConfig);
        $vertigo->setName(DisorderEnum::VERTIGO);
        $vertigo->setType(TypeEnum::DISORDER);

        $manager->persist($vertigo);

        $weaponPhobia = new DiseaseConfig();
        $weaponPhobia->setGameConfig($gameConfig);
        $weaponPhobia->setName(DisorderEnum::WEAPON_PHOBIA);
        $weaponPhobia->setType(TypeEnum::DISORDER);

        $manager->persist($weaponPhobia);

        $manager->flush();

        $this->addReference(DisorderEnum::AGORAPHOBIA, $agoraphobia);
        $this->addReference(DisorderEnum::AILUROPHOBIA, $ailurophobia);
        $this->addReference(DisorderEnum::CHRONIC_MIGRAINE, $chronicMigraine);
        $this->addReference(DisorderEnum::CHRONIC_VERTIGO, $chronicVertigo);
        $this->addReference(DisorderEnum::COPROLALIA, $coprolalia);
        $this->addReference(DisorderEnum::CRABISM, $crabism);
        $this->addReference(DisorderEnum::DEPRESSION, $depression);
        $this->addReference(DisorderEnum::PARANOIA, $paranoia);
        $this->addReference(DisorderEnum::PSYCOTIC_EPISODE, $psychoticEpisode);
        $this->addReference(DisorderEnum::SPLEEN, $spleen);
        $this->addReference(DisorderEnum::VERTIGO, $vertigo);
        $this->addReference(DisorderEnum::WEAPON_PHOBIA, $weaponPhobia);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
