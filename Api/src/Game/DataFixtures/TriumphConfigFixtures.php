<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Daedalus\DataFixtures\DaedalusConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\TriumphConfig;
use Mush\Game\Enum\TriumphEnum;
use Mush\Game\Enum\VisibilityEnum;

class TriumphConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::FRENCH_DEFAULT_GAME_CONFIG);

        $alienScience = new TriumphConfig();
        $alienScience
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::ALIEN_SCIENCE)
            ->setTriumph(16)
            ->setTeam(VisibilityEnum::PUBLIC)
        ;
        $manager->persist($alienScience);

        $expedition = new TriumphConfig();
        $expedition
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EXPEDITION)
            ->setTriumph(3)
            ->setTeam(VisibilityEnum::PUBLIC)
        ;
        $manager->persist($expedition);

        $superNova = new TriumphConfig();
        $superNova
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::SUPER_NOVA)
            ->setTriumph(20)
            ->setTeam(VisibilityEnum::PUBLIC)
            ->setIsAllCrew(true)
        ;
        $manager->persist($superNova);

        $firstStarmap = new TriumphConfig();
        $firstStarmap
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::FIRST_STARMAP)
            ->setTriumph(6)
            ->setTeam(VisibilityEnum::PUBLIC)
            ->setIsAllCrew(true)
        ;
        $manager->persist($firstStarmap);

        $nextStarmap = new TriumphConfig();
        $nextStarmap
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::NEXT_STARMAP)
            ->setTriumph(1)
            ->setTeam(VisibilityEnum::PUBLIC)
            ->setIsAllCrew(true)
        ;
        $manager->persist($nextStarmap);

        $cycleMush = new TriumphConfig();
        $cycleMush
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::CYCLE_MUSH)
            ->setTriumph(-2)
            ->setTeam(VisibilityEnum::MUSH)
            ->setIsAllCrew(true)
        ;
        $manager->persist($cycleMush);

        $startingMush = new TriumphConfig();
        $startingMush
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::STARTING_MUSH)
            ->setTriumph(120)
            ->setTeam(VisibilityEnum::MUSH)
        ;
        $manager->persist($startingMush);

        $cycleMushLate = new TriumphConfig();
        $cycleMushLate
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::CYCLE_MUSH_LATE)
            ->setTriumph(-3)
            ->setTeam(VisibilityEnum::MUSH)
        ;
        $manager->persist($cycleMushLate);

        $conversionMush = new TriumphConfig();
        $conversionMush
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::CONVERSION)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::MUSH)
        ;
        $manager->persist($conversionMush);

        $infectionMush = new TriumphConfig();
        $infectionMush
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::INFECTION)
            ->setTriumph(1)
            ->setTeam(VisibilityEnum::MUSH)
        ;
        $manager->persist($infectionMush);

        $humanocideMush = new TriumphConfig();
        $humanocideMush
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::HUMANOCIDE)
            ->setTriumph(3)
            ->setTeam(VisibilityEnum::MUSH)
        ;
        $manager->persist($humanocideMush);

        $chunDead = new TriumphConfig();
        $chunDead
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::CHUN_DEAD)
            ->setTriumph(7)
            ->setTeam(VisibilityEnum::MUSH)
            ->setIsAllCrew(true)
        ;
        $manager->persist($chunDead);

        $returnSolMush = new TriumphConfig();
        $returnSolMush
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::SOL_RETURN_MUSH)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::MUSH)
            ->setIsAllCrew(true)
        ;
        $manager->persist($returnSolMush);

        $edenMush = new TriumphConfig();
        $edenMush
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN_MUSH)
            ->setTriumph(32)
            ->setTeam(VisibilityEnum::MUSH)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenMush);

        $cycleHuman = new TriumphConfig();
        $cycleHuman
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::CYCLE_HUMAN)
            ->setTriumph(1)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($cycleHuman);

        $cycleInactive = new TriumphConfig();
        $cycleInactive
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::CYCLE_INACTIVE)
            ->setTriumph(0)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($cycleInactive);

        $newPlanetOrbit = new TriumphConfig();
        $newPlanetOrbit
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::NEW_PLANET_ORBIT)
            ->setTriumph(5)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($newPlanetOrbit);

        $solContact = new TriumphConfig();
        $solContact
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::SOL_CONTACT)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($solContact);

        $smallResearch = new TriumphConfig();
        $smallResearch
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::SMALL_RESEARCH)
            ->setTriumph(3)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($smallResearch);

        $standardResearch = new TriumphConfig();
        $standardResearch
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::STANDARD_RESEARCH)
            ->setTriumph(6)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($standardResearch);

        $brilliantResearch = new TriumphConfig();
        $brilliantResearch
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::BRILLIANT_RESEARCH)
            ->setTriumph(16)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($brilliantResearch);

        $solReturn = new TriumphConfig();
        $solReturn
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::SOL_RETURN)
            ->setTriumph(20)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($solReturn);

        $solMushIntruder = new TriumphConfig();
        $solMushIntruder
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::SOL_MUSH_INTRUDER)
            ->setTriumph(-10)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($solMushIntruder);

        $hunterKilled = new TriumphConfig();
        $hunterKilled
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::HUNTER_KILLED)
            ->setTriumph(1)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($hunterKilled);

        $mushicide = new TriumphConfig();
        $mushicide
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::MUSHICIDE)
            ->setTriumph(3)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($mushicide);

        $rebelWolf = new TriumphConfig();
        $rebelWolf
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::REBEL_WOLF)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($rebelWolf);

        $niceSurgery = new TriumphConfig();
        $niceSurgery
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::NICE_SURGERY)
            ->setTriumph(5)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($niceSurgery);

        $edenByCrewAlive = new TriumphConfig();
        $edenByCrewAlive
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN_CREW_ALIVE)
            ->setTriumph(1)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenByCrewAlive);

        $edenByAlienPlant = new TriumphConfig();
        $edenByAlienPlant
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN_ALIEN_PLANT)
            ->setTriumph(1)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenByAlienPlant);

        $edenGender = new TriumphConfig();
        $edenGender
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN_GENDER)
            ->setTriumph(4)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenGender);

        $eden = new TriumphConfig();
        $eden
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN)
            ->setTriumph(6)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($eden);

        $edenCat = new TriumphConfig();
        $edenCat
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN_CAT)
            ->setTriumph(4)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenCat);

        $edenCatDead = new TriumphConfig();
        $edenCatDead
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN_CAT_DEAD)
            ->setTriumph(-4)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenCatDead);

        $edenCatMush = new TriumphConfig();
        $edenCatMush
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN_CAT_MUSH)
            ->setTriumph(-8)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenCatMush);

        $edenDisease = new TriumphConfig();
        $edenDisease
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN_DISEASE)
            ->setTriumph(-4)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenDisease);

        $edenEngineers = new TriumphConfig();
        $edenEngineers
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN_ENGINEERS)
            ->setTriumph(6)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($edenEngineers);

        $edenBiologist = new TriumphConfig();
        $edenBiologist
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN_BIOLOGIST)
            ->setTriumph(3)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($edenBiologist);

        $edenMushIntruder = new TriumphConfig();
        $edenMushIntruder
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN_MUSH_INTRUDER)
            ->setTriumph(-16)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenMushIntruder);

        $edenByPregnant = new TriumphConfig();
        $edenByPregnant
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN_BY_PREGNANT)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenByPregnant);

        $edenComputed = new TriumphConfig();
        $edenComputed
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::EDEN_COMPUTED)
            ->setTriumph(4)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenComputed);

        $anathem = new TriumphConfig();
        $anathem
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::ANATHEM)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($anathem);

        $pregnancy = new TriumphConfig();
        $pregnancy
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::PREGNANCY)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($pregnancy);

        $allPregnant = new TriumphConfig();
        $allPregnant
            ->setGameConfig($gameConfig)
            ->setName(TriumphEnum::ALL_PREGNANT)
            ->setTriumph(2)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($allPregnant);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            DaedalusConfigFixtures::class,
        ];
    }
}
