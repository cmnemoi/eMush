<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\Config\DiseaseConfig;
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
        $agoraphobia
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::AGORAPHOBIA)
            ->setType(TypeEnum::DISORDER)
        ;

        $manager->persist($agoraphobia);

        $ailurophobia = new DiseaseConfig();
        $ailurophobia
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::AILUROPHOBIA)
            ->setType(TypeEnum::DISORDER)
        ;

        $manager->persist($ailurophobia);

        $chronicMigraine = new DiseaseConfig();
        $chronicMigraine
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::CHRONIC_MIGRAINE)
            ->setType(TypeEnum::DISORDER)
        ;

        $manager->persist($chronicMigraine);

        $chronicVertigo = new DiseaseConfig();
        $chronicVertigo
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::CHRONIC_VERTIGO)
            ->setType(TypeEnum::DISORDER)
        ;

        $manager->persist($chronicVertigo);

        $coprolalia = new DiseaseConfig();
        $coprolalia
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::COPROLALIA)
            ->setType(TypeEnum::DISORDER)
        ;

        $manager->persist($coprolalia);

        $crabism = new DiseaseConfig();
        $crabism
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::CRABISM)
            ->setType(TypeEnum::DISORDER)
        ;

        $manager->persist($crabism);

        $depression = new DiseaseConfig();
        $depression
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::DEPRESSION)
            ->setType(TypeEnum::DISORDER)
        ;

        $manager->persist($depression);

        $paranoia = new DiseaseConfig();
        $paranoia
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::PARANOIA)
            ->setType(TypeEnum::DISORDER)
        ;

        $manager->persist($paranoia);

        $psychoticEpisode = new DiseaseConfig();
        $psychoticEpisode
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::PSYCOTIC_EPISODE)
            ->setType(TypeEnum::DISORDER)
        ;

        $manager->persist($psychoticEpisode);

        $spleen = new DiseaseConfig();
        $spleen
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::SPLEEN)
            ->setType(TypeEnum::DISORDER)
        ;

        $manager->persist($spleen);

        $vertigo = new DiseaseConfig();
        $vertigo
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::VERTIGO)
            ->setType(TypeEnum::DISORDER)
        ;

        $manager->persist($vertigo);

        $weaponPhobia = new DiseaseConfig();
        $weaponPhobia
            ->setGameConfig($gameConfig)
            ->setName(DisorderEnum::WEAPON_PHOBIA)
            ->setType(TypeEnum::DISORDER)
        ;

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
