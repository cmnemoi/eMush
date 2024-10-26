<?php

namespace Mush\Player\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\MushActionFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\DataFixtures\DaedalusConfigFixtures;
use Mush\Disease\DataFixtures\DisorderConfigFixtures;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Equipment\DataFixtures\PersonalEquipmentConfigFixtures;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Skill\DataFixtures\SkillConfigFixtures;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;

class CharacterConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var ItemConfig $talkieConfig */
        $talkieConfig = $this->getReference(PersonalEquipmentConfigFixtures::WALKIE_TALKIE);

        /** @var ItemConfig $trackerConfig */
        $trackerConfig = $this->getReference(PersonalEquipmentConfigFixtures::TRACKER);

        /** @var ItemConfig $iTrackieConfig */
        $iTrackieConfig = $this->getReference(PersonalEquipmentConfigFixtures::ITRACKIE);

        /** @var ArrayCollection $iTrackieCollection */
        $iTrackieCollection = new ArrayCollection([$iTrackieConfig]);

        /** @var ArrayCollection $trackerTalkieCollection */
        $trackerTalkieCollection = new ArrayCollection([$trackerConfig, $talkieConfig]);

        /** @var SkillConfig $pilotSkillConfig */
        $pilotSkillConfig = $this->getReference(SkillEnum::PILOT->value);

        /** @var SkillConfig $technicianSkillConfig */
        $technicianSkillConfig = $this->getReference(SkillEnum::TECHNICIAN->value);

        /** @var SkillConfig $mankindOnlyHopeSkillConfig */
        $mankindOnlyHopeSkillConfig = $this->getReference(SkillEnum::MANKIND_ONLY_HOPE->value);

        /** @var SkillConfig $shrinkSkillConfig */
        $shrinkSkillConfig = $this->getReference(SkillEnum::SHRINK->value);

        /** @var SkillConfig $conceptorSkillConfig */
        $conceptorSkillConfig = $this->getReference(SkillEnum::CONCEPTOR->value);

        /** @var SkillConfig $shooterSkillConfig */
        $shooterSkillConfig = $this->getReference(SkillEnum::SHOOTER->value);

        /** @var SkillConfig $leaderSkillConfig */
        $leaderSkillConfig = $this->getReference(SkillEnum::LEADER->value);

        /** @var SkillConfig $motivatorSkillConfig */
        $motivatorSkillConfig = $this->getReference(SkillEnum::MOTIVATOR->value);

        /** @var SkillConfig $itExpertSkillConfig */
        $itExpertSkillConfig = $this->getReference(SkillEnum::IT_EXPERT->value);

        /** @var SkillConfig $astrophysicistSkillConfig */
        $astrophysicistSkillConfig = $this->getReference(SkillEnum::ASTROPHYSICIST->value);

        /** @var SkillConfig $firefighterSkillConfig */
        $firefighterSkillConfig = $this->getReference(SkillEnum::FIREFIGHTER->value);

        /** @var SkillConfig $creativeSkillConfig */
        $creativeSkillConfig = $this->getReference(SkillEnum::CREATIVE->value);

        /** @var SkillConfig $sprinterSkillConfig */
        $sprinterSkillConfig = $this->getReference(SkillEnum::SPRINTER->value);

        /** @var SkillConfig $confidentSkillConfig */
        $confidentSkillConfig = $this->getReference(SkillEnum::CONFIDENT->value);

        /** @var SkillConfig $survivalistSkillConfig */
        $survivalistSkillConfig = $this->getReference(SkillEnum::SURVIVALIST->value);

        /** @var SkillConfig $botanistSkillConfig */
        $botanistSkillConfig = $this->getReference(SkillEnum::BOTANIST->value);

        /** @var SkillConfig $physicistSkillConfig */
        $physicistSkillConfig = $this->getReference(SkillEnum::PHYSICIST->value);

        /** @var SkillConfig $diplomatSkillConfig */
        $diplomatSkillConfig = $this->getReference(SkillEnum::DIPLOMAT->value);

        /** @var SkillConfig $nurseSkillConfig */
        $nurseSkillConfig = $this->getReference(SkillEnum::NURSE->value);

        /** @var SkillConfig $determinedSkillConfig */
        $determinedSkillConfig = $this->getReference(SkillEnum::DETERMINED->value);

        /** @var SkillConfig $optimistSkillConfig */
        $optimistSkillConfig = $this->getReference(SkillEnum::OPTIMIST->value);

        /** @var SkillConfig $gunnerSkillConfig */
        $gunnerSkillConfig = $this->getReference(SkillEnum::GUNNER->value);

        /** @var SkillConfig $logisticExpert */
        $logisticExpert = $this->getReference(SkillEnum::LOGISTICS_EXPERT->value);

        /** @var SkillConfig $apprenticeSkillConfig */
        $apprenticeSkillConfig = $this->getReference(SkillEnum::APPRENTICE->value);

        /** @var SkillConfig $solidSkillConfig */
        $solidSkillConfig = $this->getReference(SkillEnum::SOLID->value);

        /** @var SkillConfig $geniusSkillConfig */
        $geniusSkillConfig = $this->getReference(SkillEnum::GENIUS->value);

        /** @var SkillConfig $presentimentSkillConfig */
        $presentimentSkillConfig = $this->getReference(SkillEnum::PRESENTIMENT->value);

        /** @var SkillConfig $neronOnlyFriendSkillConfig */
        $neronOnlyFriendSkillConfig = $this->getReference(SkillEnum::NERON_ONLY_FRIEND->value);

        /** @var SkillConfig $sneakSkillConfig */
        $sneakSkillConfig = $this->getReference(SkillEnum::SNEAK->value);

        /** @var SkillConfig $wrestlerSkillConfig */
        $wrestlerSkillConfig = $this->getReference(SkillEnum::WRESTLER->value);

        /** @var SkillConfig $devotionSkillConfig */
        $devotionSkillConfig = $this->getReference(SkillEnum::DEVOTION->value);

        /** @var SkillConfig $trackerSkillConfig */
        $trackerSkillConfig = $this->getReference(SkillEnum::TRACKER->value);

        /** @var SkillConfig $chefSkillConfig */
        $chefSkillConfig = $this->getReference(SkillEnum::CHEF->value);

        /** @var SkillConfig $observantSkillConfig */
        $observantSkillConfig = $this->getReference(SkillEnum::OBSERVANT->value);

        /** @var SkillConfig $caffeineJunkieConfig */
        $caffeineJunkieConfig = $this->getReference(SkillEnum::CAFFEINE_JUNKIE->value);

        /** @var SkillConfig $detachedCrewmemberSkillConfig */
        $detachedCrewmemberSkillConfig = $this->getReference(SkillEnum::DETACHED_CREWMEMBER->value);

        /** @var SkillConfig $expertSkillConfig */
        $expertSkillConfig = $this->getReference(SkillEnum::EXPERT->value);

        /** @var SkillConfig $uTurnSkillConfig */
        $uTurnSkillConfig = $this->getReference(SkillEnum::U_TURN->value);

        /** @var SkillConfig $politicianSkillConfig */
        $politicianSkillConfig = $this->getReference(SkillEnum::POLITICIAN->value);

        /** @var SkillConfig $victimizerSkillConfig */
        $victimizerSkillConfig = $this->getReference(SkillEnum::VICTIMIZER->value);

        /** @var SkillConfig $frugivoreSkillConfig */
        $frugivoreSkillConfig = $this->getReference(SkillEnum::FRUGIVORE->value);

        /** @var SkillConfig $mycologistSkillConfig */
        $mycologistSkillConfig = $this->getReference(SkillEnum::MYCOLOGIST->value);

        /** @var SkillConfig $coldBloodedSkillConfig */
        $coldBloodedSkillConfig = $this->getReference(SkillEnum::COLD_BLOODED->value);

        /** @var SkillConfig $greenThumbSkillConfig */
        $greenThumbSkillConfig = $this->getReference(SkillEnum::GREEN_THUMB->value);

        /** @var SkillConfig $polyvalentSkillConfig */
        $polyvalentSkillConfig = $this->getReference(SkillEnum::POLYVALENT->value);

        /** @var SkillConfig $strateguruSkillConfig */
        $strateguruSkillConfig = $this->getReference(SkillEnum::STRATEGURU->value);

        /** @var SkillConfig $opportunistSkillConfig */
        $opportunistSkillConfig = $this->getReference(SkillEnum::OPPORTUNIST->value);

        /** @var SkillConfig $paranoidSkillConfig */
        $paranoidSkillConfig = $this->getReference(SkillEnum::PARANOID->value);

        /** @var SkillConfig $crazyEyeSkillConfig */
        $crazyEyeSkillConfig = $this->getReference(SkillEnum::CRAZY_EYE->value);

        /** @var SkillConfig $roboticsExpertSkillConfig */
        $roboticsExpertSkillConfig = $this->getReference(SkillEnum::ROBOTICS_EXPERT->value);

        /** @var SkillConfig $biologistSkillConfig */
        $biologistSkillConfig = $this->getReference(SkillEnum::BIOLOGIST->value);

        /** @var SkillConfig $medicSkillConfig */
        $medicSkillConfig = $this->getReference(SkillEnum::MEDIC->value);

        /** @var SkillConfig $radioExpertSkillConfig */
        $radioExpertSkillConfig = $this->getReference(SkillEnum::RADIO_EXPERT->value);

        /** @var SkillConfig $rebelSkillConfig */
        $rebelSkillConfig = $this->getReference(SkillEnum::REBEL->value);

        /** @var SkillConfig $torturerSkillConfig */
        $torturerSkillConfig = $this->getReference(SkillEnum::TORTURER->value);

        /** @var SkillConfig $ocdSkillConfig */
        $ocdSkillConfig = $this->getReference(SkillEnum::OCD->value);

        /** @var SkillConfig $lethargySkillConfig */
        $lethargySkillConfig = $this->getReference(SkillEnum::LETHARGY->value);

        /** @var SkillConfig $panicSkillConfig */
        $panicSkillConfig = $this->getReference(SkillEnum::PANIC->value);

        /** @var SkillConfig $hygienistSkillConfig */
        $hygienistSkillConfig = $this->getReference(SkillEnum::HYGIENIST->value);

        /** @var SkillConfig $antiquePerfumeSkillConfig */
        $antiquePerfumeSkillConfig = $this->getReference(SkillEnum::ANTIQUE_PERFUME->value);

        /** @var SkillConfig $selfSacrificeSkillConfig */
        $selfSacrificeSkillConfig = $this->getReference(SkillEnum::SELF_SACRIFICE->value);

        /** @var SkillConfig $intimidatingSkillConfig */
        $intimidatingSkillConfig = $this->getReference(SkillEnum::INTIMIDATING->value);

        $andie = $this->buildDefaultCharacterConfig();
        $andie
            ->setName(CharacterEnum::ANDIE)
            ->setCharacterName(CharacterEnum::ANDIE)
            ->setSkillConfigs([
                $pilotSkillConfig,
                $devotionSkillConfig,
                $polyvalentSkillConfig,
                $confidentSkillConfig,
                $expertSkillConfig,
            ])
            ->setStartingItems($trackerTalkieCollection);
        $manager->persist($andie);

        $chao = $this->buildDefaultCharacterConfig();
        $chao
            ->setName(CharacterEnum::CHAO)
            ->setCharacterName(CharacterEnum::CHAO)
            ->setSkillConfigs([
                $shooterSkillConfig,
                $survivalistSkillConfig,
                $wrestlerSkillConfig,
                $torturerSkillConfig,
                $crazyEyeSkillConfig,
                $intimidatingSkillConfig,
            ])
            ->setStartingItems($trackerTalkieCollection);
        $manager->persist($chao);

        /** @var StatusConfig $immunizedStatus */
        $immunizedStatus = $this->getReference(StatusFixtures::IMMUNIZED_STATUS);

        $chun = $this->buildDefaultCharacterConfig();
        $chun
            ->setName(CharacterEnum::CHUN)
            ->setCharacterName(CharacterEnum::CHUN)
            ->setSkillConfigs([
                $mankindOnlyHopeSkillConfig,
                $nurseSkillConfig,
                $presentimentSkillConfig,
                $sneakSkillConfig,
                $lethargySkillConfig,
            ])
            ->setInitStatuses([$immunizedStatus])
            ->setStartingItems($trackerTalkieCollection);
        $manager->persist($chun);

        /** @var StatusConfig $firstTimeStatus */
        $firstTimeStatus = $this->getReference(ChargeStatusFixtures::FIRST_TIME);

        $derek = $this->buildDefaultCharacterConfig();
        $derek
            ->setName(CharacterEnum::DEREK)
            ->setCharacterName(CharacterEnum::DEREK)
            ->setSkillConfigs([
                $shooterSkillConfig,
                $wrestlerSkillConfig,
                $firefighterSkillConfig,
                $hygienistSkillConfig,
                $motivatorSkillConfig,
            ])
            ->setInitStatuses([$firstTimeStatus])
            ->setStartingItems($trackerTalkieCollection);
        $manager->persist($derek);

        /** @var DiseaseConfig $chronicVertigo */
        $chronicVertigo = $this->getReference(DisorderEnum::CHRONIC_VERTIGO);

        $eleesha = $this->buildDefaultCharacterConfig();
        $eleesha
            ->setName(CharacterEnum::ELEESHA)
            ->setCharacterName(CharacterEnum::ELEESHA)
            ->setSkillConfigs([
                $trackerSkillConfig,
                $determinedSkillConfig,
                $observantSkillConfig,
                $technicianSkillConfig,
                $itExpertSkillConfig,
            ])
            ->setStartingItems($trackerTalkieCollection)
            ->setInitDiseases(new ArrayCollection([$chronicVertigo]));
        $manager->persist($eleesha);

        /** @var StatusConfig $ocdStatus */
        $ocdStatus = $this->getReference(StatusFixtures::GERMAPHOBE_STATUS);
        $finola = $this->buildDefaultCharacterConfig();
        $finola
            ->setName(CharacterEnum::FINOLA)
            ->setCharacterName(CharacterEnum::FINOLA)
            ->setSkillConfigs([
                $biologistSkillConfig,
                $medicSkillConfig,
                $nurseSkillConfig,
                $diplomatSkillConfig,
                $ocdSkillConfig,
            ])
            ->setInitStatuses([$ocdStatus]);
        $manager->persist($finola);

        $frieda = $this->buildDefaultCharacterConfig();
        $frieda
            ->setName(CharacterEnum::FRIEDA)
            ->setCharacterName(CharacterEnum::FRIEDA)
            ->setSkillConfigs([
                $astrophysicistSkillConfig,
                $pilotSkillConfig,
                $survivalistSkillConfig,
                $itExpertSkillConfig,
                $radioExpertSkillConfig,
                $antiquePerfumeSkillConfig,
            ])
            ->setStartingItems($trackerTalkieCollection);
        $manager->persist($frieda);

        $gioele = $this->buildDefaultCharacterConfig();
        $gioele
            ->setName(CharacterEnum::GIOELE)
            ->setCharacterName(CharacterEnum::GIOELE)
            ->setSkillConfigs([
                $solidSkillConfig,
                $paranoidSkillConfig,
                $caffeineJunkieConfig,
                $astrophysicistSkillConfig,
                $panicSkillConfig,
                $victimizerSkillConfig,
            ])
            ->setStartingItems($iTrackieCollection);
        $manager->persist($gioele);

        $hua = $this->buildDefaultCharacterConfig();
        $hua
            ->setName(CharacterEnum::HUA)
            ->setCharacterName(CharacterEnum::HUA)
            ->setSkillConfigs([
                $botanistSkillConfig,
                $pilotSkillConfig,
                $survivalistSkillConfig,
                $technicianSkillConfig,
                $determinedSkillConfig,
                $uTurnSkillConfig,
            ])
            ->setStartingItems($trackerTalkieCollection);
        $manager->persist($hua);

        /** @var StatusConfig $pacifistStatus */
        $pacifistStatus = $this->getReference(StatusFixtures::PACIFIST_STATUS);

        $ian = $this->buildDefaultCharacterConfig();
        $ian
            ->setName(CharacterEnum::IAN)
            ->setCharacterName(CharacterEnum::IAN)
            ->setSkillConfigs([
                $botanistSkillConfig,
                $biologistSkillConfig,
                $mycologistSkillConfig,
                $firefighterSkillConfig,
                $greenThumbSkillConfig,
                $frugivoreSkillConfig,
            ])
            ->setInitStatuses([$pacifistStatus])
            ->setStartingItems($trackerTalkieCollection);
        $manager->persist($ian);

        $janice = $this->buildDefaultCharacterConfig();
        $janice
            ->setName(CharacterEnum::JANICE)
            ->setCharacterName(CharacterEnum::JANICE)
            ->setSkillConfigs([
                $shrinkSkillConfig,
                $itExpertSkillConfig,
                $neronOnlyFriendSkillConfig,
                $diplomatSkillConfig,
                $radioExpertSkillConfig,
                $selfSacrificeSkillConfig,
            ])
            ->setStartingItems($iTrackieCollection);
        $manager->persist($janice);

        $jinSu = $this->buildDefaultCharacterConfig();
        $jinSu
            ->setName(CharacterEnum::JIN_SU)
            ->setCharacterName(CharacterEnum::JIN_SU)
            ->setSkillConfigs([
                $leaderSkillConfig,
                $pilotSkillConfig,
                $coldBloodedSkillConfig,
                $shooterSkillConfig,
                $logisticExpert,
                $strateguruSkillConfig,
            ])
            ->setStartingItems($iTrackieCollection);
        $manager->persist($jinSu);

        $kuanTi = $this->buildDefaultCharacterConfig();
        $kuanTi
            ->setName(CharacterEnum::KUAN_TI)
            ->setCharacterName(CharacterEnum::KUAN_TI)
            ->setSkillConfigs([
                $conceptorSkillConfig,
                $optimistSkillConfig,
                $astrophysicistSkillConfig,
                $technicianSkillConfig,
                $leaderSkillConfig,
                $politicianSkillConfig,
            ])
            ->setStartingItems($iTrackieCollection);
        $manager->persist($kuanTi);

        $paola = $this->buildDefaultCharacterConfig();
        $paola
            ->setName(CharacterEnum::PAOLA)
            ->setCharacterName(CharacterEnum::PAOLA)
            ->setSkillConfigs([
                $radioExpertSkillConfig,
                $logisticExpert,
                $shooterSkillConfig,
                $biologistSkillConfig,
                $rebelSkillConfig,
                $gunnerSkillConfig,
            ])
            ->setStartingItems($iTrackieCollection);
        $manager->persist($paola);

        /** @var StatusConfig $antisocialStatus */
        $antisocialStatus = $this->getReference(StatusFixtures::ANTISOCIAL_STATUS);

        /** @var StatusConfig $catOwnerStatus */
        $catOwnerStatus = $this->getReference(PlayerStatusEnum::CAT_OWNER);
        $raluca = $this->buildDefaultCharacterConfig();
        $raluca
            ->setName(CharacterEnum::RALUCA)
            ->setCharacterName(CharacterEnum::RALUCA)
            ->setSkillConfigs([
                $physicistSkillConfig,
                $detachedCrewmemberSkillConfig,
                $technicianSkillConfig,
                $geniusSkillConfig,
                $conceptorSkillConfig,
            ])
            ->setInitStatuses([$antisocialStatus, $catOwnerStatus])
            ->setStartingItems($trackerTalkieCollection);
        $manager->persist($raluca);

        $roland = $this->buildDefaultCharacterConfig();
        $roland
            ->setName(CharacterEnum::ROLAND)
            ->setCharacterName(CharacterEnum::ROLAND)
            ->setSkillConfigs([
                $pilotSkillConfig,
                $shooterSkillConfig,
                $firefighterSkillConfig,
                $optimistSkillConfig,
                $creativeSkillConfig,
                $sprinterSkillConfig,
            ])
            ->setStartingItems($trackerTalkieCollection);
        $manager->persist($roland);

        $stephen = $this->buildDefaultCharacterConfig();
        $stephen
            ->setName(CharacterEnum::STEPHEN)
            ->setCharacterName(CharacterEnum::STEPHEN)
            ->setSkillConfigs([
                $chefSkillConfig,
                $solidSkillConfig,
                $shooterSkillConfig,
                $apprenticeSkillConfig,
                $creativeSkillConfig,
                $opportunistSkillConfig,
            ])
            ->setStartingItems($trackerTalkieCollection);
        $manager->persist($stephen);

        /** @var StatusConfig $disabledStatus */
        $disabledStatus = $this->getReference(StatusFixtures::DISABLED_STATUS);

        $terrence = $this->buildDefaultCharacterConfig();
        $terrence
            ->setName(CharacterEnum::TERRENCE)
            ->setCharacterName(CharacterEnum::TERRENCE)
            ->setSkillConfigs([
                $roboticsExpertSkillConfig,
                $technicianSkillConfig,
                $pilotSkillConfig,
                $shooterSkillConfig,
                $itExpertSkillConfig,
            ])
            ->setInitStatuses([$disabledStatus])
            ->setStartingItems($iTrackieCollection);
        $manager->persist($terrence);

        /** @var ArrayCollection $characters */
        $characters = new ArrayCollection([
            $andie, $chun, $derek, $eleesha, $frieda, $gioele, $hua, $ian,
            $janice, $jinSu, $kuanTi, $paola, $raluca, $roland, $stephen, $terrence,
        ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);
        $gameConfig
            ->setCharactersConfig($characters);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            DaedalusConfigFixtures::class,
            ActionsFixtures::class,
            MushActionFixtures::class,
            StatusFixtures::class,
            PersonalEquipmentConfigFixtures::class,
            DisorderConfigFixtures::class,
            SkillConfigFixtures::class,
        ];
    }

    private function buildDefaultCharacterConfig(): CharacterConfig
    {
        // ADMIN ONLY
        /** @var ActionConfig $suicideAction */
        $suicideAction = $this->getReference(ActionsFixtures::SUICIDE);

        /** @var ActionConfig $autoDestroyAction */
        $autoDestroyAction = $this->getReference(ActionsFixtures::AUTO_DESTROY);

        /** @var ActionConfig $killPlayerAction */
        $killPlayerAction = $this->getReference(ActionsFixtures::KILL_PLAYER);

        // @TODO: remove when the game is ready
        /** @var ActionConfig $rejuvenateAlphaAction */
        $rejuvenateAlphaAction = $this->getReference(ActionsFixtures::REJUVENATE_ALPHA);

        /** @var ActionConfig $fakeDiseaseAction */
        $fakeDiseaseAction = $this->getReference(MushActionFixtures::FAKE_DISEASE);

        /** @var ActionConfig $hitAction */
        $hitAction = $this->getReference(ActionsFixtures::HIT_DEFAULT);

        /** @var ActionConfig $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);

        /** @var ActionConfig $searchAction */
        $searchAction = $this->getReference(ActionsFixtures::SEARCH_DEFAULT);

        /** @var ActionConfig $getUpAction */
        $getUpAction = $this->getReference(ActionsFixtures::GET_UP);

        /** @var ActionConfig $flirtAction */
        $flirtAction = $this->getReference(ActionsFixtures::FLIRT_DEFAULT);

        /** @var ActionConfig $doTheThingAction */
        $doTheThingAction = $this->getReference(ActionsFixtures::DO_THE_THING);

        /** @var ActionConfig $ungag */
        $ungag = $this->getReference(ActionsFixtures::UNGAG_DEFAULT);

        /** @var ActionConfig $healAction */
        $healAction = $this->getReference(ActionsFixtures::HEAL);

        /** @var ActionConfig $selfHealAction */
        $selfHealAction = $this->getReference(ActionsFixtures::SELF_HEAL);

        /** @var ActionConfig $extractSporeAction */
        $extractSporeAction = $this->getReference(MushActionFixtures::EXTRACT_SPORE);

        /** @var ActionConfig $infectAction */
        $infectAction = $this->getReference(MushActionFixtures::INFECT_PLAYER);

        // Skills actions
        // @TODO: after skill implementation, action will be given by skills

        /** @var ActionConfig $surgeryAction */
        $surgeryAction = $this->getReference(ActionsFixtures::SURGERY);

        /** @var ActionConfig $guardAction */
        $guardAction = $this->getReference(ActionEnum::GUARD->value);

        /** @var ActionConfig $commanderOrderAction */
        $commanderOrderAction = $this->getReference(ActionEnum::COMMANDER_ORDER->value);

        /** @var ActionConfig $acceptMissionAction */
        $acceptMissionAction = $this->getReference(ActionEnum::ACCEPT_MISSION->value);

        /** @var ActionConfig $rejectMissionAction */
        $rejectMissionAction = $this->getReference(ActionEnum::REJECT_MISSION->value);

        /** @var ArrayCollection<array-key, ActionConfig> $defaultActions */
        $defaultActions = new ArrayCollection([
            $hitAction,
            $hideAction,
            $searchAction,
            $infectAction,
            $extractSporeAction,
            $getUpAction,
            $healAction,
            $selfHealAction,
            $rejuvenateAlphaAction,
            $flirtAction,
            $doTheThingAction,
            $fakeDiseaseAction,
            $ungag,
            $autoDestroyAction,
            $suicideAction,
            $surgeryAction,
            $killPlayerAction,
            $guardAction,
            $commanderOrderAction,
            $acceptMissionAction,
            $rejectMissionAction,
        ]);

        $characterConfig = new CharacterConfig();

        $characterConfig
            ->setMaxNumberPrivateChannel(3)
            ->setInitHealthPoint(10)
            ->setMaxHealthPoint(14)
            ->setInitMoralPoint(10)
            ->setMaxMoralPoint(14)
            ->setInitSatiety(0)
            ->setInitActionPoint(8)
            ->setMaxActionPoint(12)
            ->setInitMovementPoint(10)
            ->setMaxMovementPoint(12)
            ->setMaxItemInInventory(3)
            ->setMaxDiscoverablePlanets(2)
            ->setActionConfigs($defaultActions);

        return $characterConfig;
    }
}
