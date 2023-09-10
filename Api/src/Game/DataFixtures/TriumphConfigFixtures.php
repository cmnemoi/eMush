<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Daedalus\DataFixtures\DaedalusConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\TriumphConfig;
use Mush\Game\Enum\TriumphEnum;
use Mush\Game\Enum\VisibilityEnum;

class TriumphConfigFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $alienScience = new TriumphConfig();
        $alienScience
            ->setName(TriumphEnum::ALIEN_SCIENCE)
            ->setTriumph(16)
            ->setTeam(VisibilityEnum::PUBLIC)
        ;
        $manager->persist($alienScience);

        $expedition = new TriumphConfig();
        $expedition
            ->setName(TriumphEnum::EXPEDITION)
            ->setTriumph(3)
            ->setTeam(VisibilityEnum::PUBLIC)
        ;
        $manager->persist($expedition);

        $superNova = new TriumphConfig();
        $superNova
            ->setName(TriumphEnum::SUPER_NOVA)
            ->setTriumph(20)
            ->setTeam(VisibilityEnum::PUBLIC)
            ->setIsAllCrew(true)
        ;
        $manager->persist($superNova);

        $firstStarmap = new TriumphConfig();
        $firstStarmap
            ->setName(TriumphEnum::FIRST_STARMAP)
            ->setTriumph(6)
            ->setTeam(VisibilityEnum::PUBLIC)
            ->setIsAllCrew(true)
        ;
        $manager->persist($firstStarmap);

        $nextStarmap = new TriumphConfig();
        $nextStarmap
            ->setName(TriumphEnum::NEXT_STARMAP)
            ->setTriumph(1)
            ->setTeam(VisibilityEnum::PUBLIC)
            ->setIsAllCrew(true)
        ;
        $manager->persist($nextStarmap);

        $cycleMush = new TriumphConfig();
        $cycleMush
            ->setName(TriumphEnum::CYCLE_MUSH)
            ->setTriumph(-2)
            ->setTeam(VisibilityEnum::MUSH)
            ->setIsAllCrew(true)
        ;
        $manager->persist($cycleMush);

        $startingMush = new TriumphConfig();
        $startingMush
            ->setName(TriumphEnum::STARTING_MUSH)
            ->setTriumph(120)
            ->setTeam(VisibilityEnum::MUSH)
        ;
        $manager->persist($startingMush);

        $cycleMushLate = new TriumphConfig();
        $cycleMushLate
            ->setName(TriumphEnum::CYCLE_MUSH_LATE)
            ->setTriumph(-3)
            ->setTeam(VisibilityEnum::MUSH)
        ;
        $manager->persist($cycleMushLate);

        $conversionMush = new TriumphConfig();
        $conversionMush
            ->setName(TriumphEnum::CONVERSION)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::MUSH)
        ;
        $manager->persist($conversionMush);

        $infectionMush = new TriumphConfig();
        $infectionMush
            ->setName(TriumphEnum::INFECTION)
            ->setTriumph(1)
            ->setTeam(VisibilityEnum::MUSH)
        ;
        $manager->persist($infectionMush);

        $humanocideMush = new TriumphConfig();
        $humanocideMush
            ->setName(TriumphEnum::HUMANOCIDE)
            ->setTriumph(3)
            ->setTeam(VisibilityEnum::MUSH)
        ;
        $manager->persist($humanocideMush);

        $chunDead = new TriumphConfig();
        $chunDead
            ->setName(TriumphEnum::CHUN_DEAD)
            ->setTriumph(7)
            ->setTeam(VisibilityEnum::MUSH)
            ->setIsAllCrew(true)
        ;
        $manager->persist($chunDead);

        $returnSolMush = new TriumphConfig();
        $returnSolMush
            ->setName(TriumphEnum::SOL_RETURN_MUSH)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::MUSH)
            ->setIsAllCrew(true)
        ;
        $manager->persist($returnSolMush);

        $edenMush = new TriumphConfig();
        $edenMush
            ->setName(TriumphEnum::EDEN_MUSH)
            ->setTriumph(32)
            ->setTeam(VisibilityEnum::MUSH)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenMush);

        $cycleHuman = new TriumphConfig();
        $cycleHuman
            ->setName(TriumphEnum::CYCLE_HUMAN)
            ->setTriumph(1)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($cycleHuman);

        $cycleInactive = new TriumphConfig();
        $cycleInactive
            ->setName(TriumphEnum::CYCLE_INACTIVE)
            ->setTriumph(0)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($cycleInactive);

        $newPlanetOrbit = new TriumphConfig();
        $newPlanetOrbit
            ->setName(TriumphEnum::NEW_PLANET_ORBIT)
            ->setTriumph(5)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($newPlanetOrbit);

        $solContact = new TriumphConfig();
        $solContact
            ->setName(TriumphEnum::SOL_CONTACT)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($solContact);

        $smallResearch = new TriumphConfig();
        $smallResearch
            ->setName(TriumphEnum::SMALL_RESEARCH)
            ->setTriumph(3)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($smallResearch);

        $standardResearch = new TriumphConfig();
        $standardResearch
            ->setName(TriumphEnum::STANDARD_RESEARCH)
            ->setTriumph(6)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($standardResearch);

        $brilliantResearch = new TriumphConfig();
        $brilliantResearch
            ->setName(TriumphEnum::BRILLIANT_RESEARCH)
            ->setTriumph(16)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($brilliantResearch);

        $solReturn = new TriumphConfig();
        $solReturn
            ->setName(TriumphEnum::SOL_RETURN)
            ->setTriumph(20)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($solReturn);

        $solMushIntruder = new TriumphConfig();
        $solMushIntruder
            ->setName(TriumphEnum::SOL_MUSH_INTRUDER)
            ->setTriumph(-10)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($solMushIntruder);

        $hunterKilled = new TriumphConfig();
        $hunterKilled
            ->setName(TriumphEnum::HUNTER_KILLED)
            ->setTriumph(1)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($hunterKilled);

        $mushicide = new TriumphConfig();
        $mushicide
            ->setName(TriumphEnum::MUSHICIDE)
            ->setTriumph(3)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($mushicide);

        $rebelWolf = new TriumphConfig();
        $rebelWolf
            ->setName(TriumphEnum::REBEL_WOLF)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($rebelWolf);

        $niceSurgery = new TriumphConfig();
        $niceSurgery
            ->setName(TriumphEnum::NICE_SURGERY)
            ->setTriumph(5)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($niceSurgery);

        $edenByCrewAlive = new TriumphConfig();
        $edenByCrewAlive
            ->setName(TriumphEnum::EDEN_CREW_ALIVE)
            ->setTriumph(1)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenByCrewAlive);

        $edenByAlienPlant = new TriumphConfig();
        $edenByAlienPlant
            ->setName(TriumphEnum::EDEN_ALIEN_PLANT)
            ->setTriumph(1)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenByAlienPlant);

        $edenGender = new TriumphConfig();
        $edenGender
            ->setName(TriumphEnum::EDEN_GENDER)
            ->setTriumph(4)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenGender);

        $eden = new TriumphConfig();
        $eden
            ->setName(TriumphEnum::EDEN)
            ->setTriumph(6)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($eden);

        $edenCat = new TriumphConfig();
        $edenCat
            ->setName(TriumphEnum::EDEN_CAT)
            ->setTriumph(4)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenCat);

        $edenCatDead = new TriumphConfig();
        $edenCatDead
            ->setName(TriumphEnum::EDEN_CAT_DEAD)
            ->setTriumph(-4)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenCatDead);

        $edenCatMush = new TriumphConfig();
        $edenCatMush
            ->setName(TriumphEnum::EDEN_CAT_MUSH)
            ->setTriumph(-8)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenCatMush);

        $edenDisease = new TriumphConfig();
        $edenDisease
            ->setName(TriumphEnum::EDEN_DISEASE)
            ->setTriumph(-4)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenDisease);

        $edenEngineers = new TriumphConfig();
        $edenEngineers
            ->setName(TriumphEnum::EDEN_ENGINEERS)
            ->setTriumph(6)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($edenEngineers);

        $edenBiologist = new TriumphConfig();
        $edenBiologist
            ->setName(TriumphEnum::EDEN_BIOLOGIST)
            ->setTriumph(3)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($edenBiologist);

        $edenMushIntruder = new TriumphConfig();
        $edenMushIntruder
            ->setName(TriumphEnum::EDEN_MUSH_INTRUDER)
            ->setTriumph(-16)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenMushIntruder);

        $edenByPregnant = new TriumphConfig();
        $edenByPregnant
            ->setName(TriumphEnum::EDEN_BY_PREGNANT)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenByPregnant);

        $edenComputed = new TriumphConfig();
        $edenComputed
            ->setName(TriumphEnum::EDEN_COMPUTED)
            ->setTriumph(4)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($edenComputed);

        $anathem = new TriumphConfig();
        $anathem
            ->setName(TriumphEnum::ANATHEM)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($anathem);

        $pregnancy = new TriumphConfig();
        $pregnancy
            ->setName(TriumphEnum::PREGNANCY)
            ->setTriumph(8)
            ->setTeam(VisibilityEnum::HUMAN)
        ;
        $manager->persist($pregnancy);

        $allPregnant = new TriumphConfig();
        $allPregnant
            ->setName(TriumphEnum::ALL_PREGNANT)
            ->setTriumph(2)
            ->setTeam(VisibilityEnum::HUMAN)
            ->setIsAllCrew(true)
        ;
        $manager->persist($allPregnant);

        /** @var ArrayCollection $triumphConfigs */
        $triumphConfigs = new ArrayCollection([
            $alienScience, $expedition, $superNova,
            $firstStarmap, $nextStarmap,
            $cycleMush, $startingMush, $cycleMushLate, $conversionMush, $infectionMush,
            $humanocideMush, $chunDead, $returnSolMush, $edenMush,
            $cycleHuman, $cycleInactive,
            $newPlanetOrbit, $solContact,
            $smallResearch, $standardResearch, $brilliantResearch,
            $solReturn, $solMushIntruder, $hunterKilled, $mushicide,
            $rebelWolf, $niceSurgery,
            $edenByCrewAlive, $edenByAlienPlant, $edenGender, $eden, $edenCat, $edenCatDead,
            $edenCatMush, $edenDisease, $edenEngineers, $edenBiologist, $edenMushIntruder, $edenByPregnant, $edenComputed,
            $anathem, $pregnancy, $allPregnant,
        ]);
        $gameConfig->setTriumphConfig($triumphConfigs);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            DaedalusConfigFixtures::class,
        ];
    }
}
