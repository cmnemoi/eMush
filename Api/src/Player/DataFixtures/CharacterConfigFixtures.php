<?php

namespace Mush\Player\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\MushActionFixtures;
use Mush\Action\Entity\Action;
use Mush\Daedalus\DataFixtures\DaedalusConfigFixtures;
use Mush\Disease\DataFixtures\DisorderConfigFixtures;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Equipment\DataFixtures\PersonalEquipmentConfigFixtures;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\SkillEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;
use Mush\Status\Entity\Config\StatusConfig;

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

        /** @var StatusConfig $sporeStatus */
        $sporeStatus = $this->getReference(ChargeStatusFixtures::SPORES);

        $andie = $this->buildDefaultCharacterConfig();
        $andie
            ->setName(CharacterEnum::ANDIE)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::CONFIDENT,
                SkillEnum::DEVOTION,
                SkillEnum::EXPERT,
                SkillEnum::PILOT,
                SkillEnum::POLYVALENT,
            ])
            ->setStartingItem(new ArrayCollection([$trackerConfig, $talkieConfig]))
        ;
        $manager->persist($andie);

        $chao = $this->buildDefaultCharacterConfig();
        $chao
            ->setName(CharacterEnum::CHAO)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::CRAZY_EYE,
                SkillEnum::INTIMIDATING,
                SkillEnum::SHOOTER,
                SkillEnum::SURVIVALIST,
                SkillEnum::TORTURER,
                SkillEnum::WRESTLER,
            ])
        ->setStartingItem(new ArrayCollection([$trackerConfig, $talkieConfig]))
        ;
        $manager->persist($chao);

        /** @var StatusConfig $immunizedStatus */
        $immunizedStatus = $this->getReference(StatusFixtures::IMMUNIZED_STATUS);

        $chun = $this->buildDefaultCharacterConfig();
        $chun
            ->setName(CharacterEnum::CHUN)
            ->setInitStatuses(new ArrayCollection([$immunizedStatus]))
            ->setSkills([
                SkillEnum::LETHARGY,
                SkillEnum::MANKINDS_ONLY_HOPE,
                SkillEnum::NURSE,
                SkillEnum::PREMONITION,
                SkillEnum::SNEAK,
            ])
            ->setStartingItem(new ArrayCollection([$trackerConfig, $talkieConfig]))
        ;
        $manager->persist($chun);

        /** @var StatusConfig $firstTimeStatus */
        $firstTimeStatus = $this->getReference(ChargeStatusFixtures::FIRST_TIME);

        $derek = $this->buildDefaultCharacterConfig();
        $derek
            ->setName(CharacterEnum::DEREK)
            ->setInitStatuses(new ArrayCollection([$firstTimeStatus, $sporeStatus]))
            ->setSkills([
                SkillEnum::FIREFIGHTER,
                SkillEnum::MOTIVATOR,
                SkillEnum::SHOOTER,
                SkillEnum::WRESTLER,
                SkillEnum::HYGIENIST,
            ])
            ->setStartingItem(new ArrayCollection([$trackerConfig, $talkieConfig]))
        ;
        $manager->persist($derek);

        $eleesha = $this->buildDefaultCharacterConfig();
        $eleesha
            ->setName(CharacterEnum::ELEESHA)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::DETERMINED,
                SkillEnum::IT_EXPERT,
                SkillEnum::OBSERVANT,
                SkillEnum::POLYMATH,
                SkillEnum::TECHNICIAN,
                SkillEnum::TRACKER,
            ])
            ->setStartingItem(new ArrayCollection([$trackerConfig, $talkieConfig]))
            ->setInitDiseases([DisorderEnum::CHRONIC_VERTIGO])
        ;
        $manager->persist($eleesha);

        /** @var StatusConfig $ocdStatus */
        $ocdStatus = $this->getReference(StatusFixtures::GERMAPHOBE_STATUS);
        $finola = $this->buildDefaultCharacterConfig();
        $finola
            ->setName(CharacterEnum::FINOLA)
            ->setInitStatuses(new ArrayCollection([$sporeStatus, $ocdStatus]))
            ->setSkills([
                SkillEnum::BIOLOGIST,
                SkillEnum::DIPLOMAT,
                SkillEnum::MEDIC,
                SkillEnum::NURSE,
                SkillEnum::OCD,
            ])
        ;
        $manager->persist($finola);

        $frieda = $this->buildDefaultCharacterConfig();
        $frieda
            ->setName(CharacterEnum::FRIEDA)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::ANTIQUE_PERFUME,
                SkillEnum::ASTROPHYSICIST,
                SkillEnum::IT_EXPERT,
                SkillEnum::PILOT,
                SkillEnum::RADIO_EXPERT,
                SkillEnum::SURVIVALIST,
            ])
            ->setStartingItem(new ArrayCollection([$trackerConfig, $talkieConfig]))
        ;
        $manager->persist($frieda);

        $gioele = $this->buildDefaultCharacterConfig();
        $gioele
            ->setName(CharacterEnum::GIOELE)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::ASTROPHYSICIST,
                SkillEnum::CAFFEINE_JUNKIE,
                SkillEnum::PANIC,
                SkillEnum::PARANOID,
                SkillEnum::SOLID,
                SkillEnum::VICTIMIZER,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($gioele);

        $hua = $this->buildDefaultCharacterConfig();
        $hua
            ->setName(CharacterEnum::HUA)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::BOTANIST,
                SkillEnum::DETERMINED,
                SkillEnum::PILOT,
                SkillEnum::SURVIVALIST,
                SkillEnum::TECHNICIAN,
                SkillEnum::U_TURN,
            ])
            ->setStartingItem(new ArrayCollection([$trackerConfig, $talkieConfig]))
        ;
        $manager->persist($hua);

        /** @var StatusConfig $pacifistStatus */
        $pacifistStatus = $this->getReference(StatusFixtures::PACIFIST_STATUS);

        $ian = $this->buildDefaultCharacterConfig();
        $ian
            ->setName(CharacterEnum::IAN)
            ->setInitStatuses(new ArrayCollection([$pacifistStatus, $sporeStatus]))
            ->setSkills([
                SkillEnum::BIOLOGIST,
                SkillEnum::BOTANIST,
                SkillEnum::FIREFIGHTER,
                SkillEnum::FRUGIVORE,
                SkillEnum::GREEN_THUMB,
                SkillEnum::MYCOLOGIST,
            ])
            ->setStartingItem(new ArrayCollection([$trackerConfig, $talkieConfig]))
        ;
        $manager->persist($ian);

        $janice = $this->buildDefaultCharacterConfig();
        $janice
            ->setName(CharacterEnum::JANICE)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::DIPLOMAT,
                SkillEnum::IT_EXPERT,
                SkillEnum::NERON_ONLY_FRIEND,
                SkillEnum::RADIO_EXPERT,
                SkillEnum::SELF_SACRIFICE,
                SkillEnum::SHRINK,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($janice);

        $jinSu = $this->buildDefaultCharacterConfig();
        $jinSu
            ->setName(CharacterEnum::JIN_SU)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::COLD_BLOODED,
                SkillEnum::LEADER,
                SkillEnum::LOGISTICS_EXPERT,
                SkillEnum::PILOT,
                SkillEnum::SHOOTER,
                SkillEnum::STRATEGURU,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($jinSu);

        $kuanTi = $this->buildDefaultCharacterConfig();
        $kuanTi
            ->setName(CharacterEnum::KUAN_TI)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::ASTROPHYSICIST,
                SkillEnum::DESIGNER,
                SkillEnum::LEADER,
                SkillEnum::POLITICIAN,
                SkillEnum::TECHNICIAN,
                SkillEnum::OPTIMIST,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($kuanTi);

        $paola = $this->buildDefaultCharacterConfig();
        $paola
            ->setName(CharacterEnum::PAOLA)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::BIOLOGIST,
                SkillEnum::GUNNER,
                SkillEnum::LOGISTICS_EXPERT,
                SkillEnum::RADIO_EXPERT,
                SkillEnum::REBEL,
                SkillEnum::SHOOTER,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($paola);

        /** @var StatusConfig $antisocialStatus */
        $antisocialStatus = $this->getReference(StatusFixtures::ANTISOCIAL_STATUS);
        $raluca = $this->buildDefaultCharacterConfig();
        $raluca
            ->setName(CharacterEnum::RALUCA)
            ->setInitStatuses(new ArrayCollection([$antisocialStatus, $sporeStatus]))
            ->setSkills([
                SkillEnum::DESIGNER,
                SkillEnum::DETACHED_CREWMEMBER,
                SkillEnum::GENIUS,
                SkillEnum::PHYSICIST,
                SkillEnum::TECHNICIAN,
            ])
            ->setStartingItem(new ArrayCollection([$trackerConfig, $talkieConfig]))
        ;
        $manager->persist($raluca);

        $roland = $this->buildDefaultCharacterConfig();
        $roland
            ->setName(CharacterEnum::ROLAND)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::CREATIVE,
                SkillEnum::FIREFIGHTER,
                SkillEnum::PILOT,
                SkillEnum::SHOOTER,
                SkillEnum::SPRINTER,
                SkillEnum::OPTIMIST,
            ])
            ->setStartingItem(new ArrayCollection([$trackerConfig, $talkieConfig]))
        ;
        $manager->persist($roland);

        $stephen = $this->buildDefaultCharacterConfig();
        $stephen
            ->setName(CharacterEnum::STEPHEN)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::APPRENTICE,
                SkillEnum::CHEF,
                SkillEnum::CREATIVE,
                SkillEnum::OPPORTUNIST,
                SkillEnum::SHOOTER,
                SkillEnum::SOLID,
            ])
            ->setStartingItem(new ArrayCollection([$trackerConfig, $talkieConfig]))
        ;
        $manager->persist($stephen);

        /** @var StatusConfig $disabledStatus */
        $disabledStatus = $this->getReference(StatusFixtures::DISABLED_STATUS);

        $terrence = $this->buildDefaultCharacterConfig();
        $terrence
            ->setName(CharacterEnum::TERRENCE)
            ->setInitStatuses(new ArrayCollection([$disabledStatus, $sporeStatus]))
            ->setSkills([
                SkillEnum::IT_EXPERT,
                SkillEnum::METALWORKER,
                SkillEnum::PILOT,
                SkillEnum::ROBOTICS_EXPERT,
                SkillEnum::SHOOTER,
                SkillEnum::TECHNICIAN,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($terrence);

        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);
        $gameConfig
            ->setCharactersConfig(new ArrayCollection([
                $andie, $chun, $derek, $eleesha, $frieda, $gioele, $hua, $ian,
                $janice, $jinSu, $kuanTi, $paola, $raluca, $roland, $stephen, $terrence,
            ]))
        ;

        $manager->flush();
    }

    private function buildDefaultCharacterConfig(): CharacterConfig
    {
        // ADMIN ONLY
        $suicideAction = $this->getReference(ActionsFixtures::SUICIDE);

        // @TODO: remove when the game is ready
        /** @var Action $rejuvenateAlphaAction */
        $rejuvenateAlphaAction = $this->getReference(ActionsFixtures::REJUVENATE_ALPHA);
        /** @var Action $fakeDiseaseAction */
        $fakeDiseaseAction = $this->getReference(MushActionFixtures::FAKE_DISEASE);

        /** @var Action $hitAction */
        $hitAction = $this->getReference(ActionsFixtures::HIT_DEFAULT);
        /** @var Action $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $searchAction */
        $searchAction = $this->getReference(ActionsFixtures::SEARCH_DEFAULT);
        /** @var Action $phagocyteAction */
        $phagocyteAction = $this->getReference(ActionsFixtures::PHAGOCYTE);
        /** @var Action $reportFireAction */
        $reportFireAction = $this->getReference(ActionsFixtures::REPORT_FIRE);
        /** @var Action $getUpAction */
        $getUpAction = $this->getReference(ActionsFixtures::GET_UP);
        /** @var Action $flirtAction */
        $flirtAction = $this->getReference(ActionsFixtures::FLIRT_DEFAULT);
        /** @var Action $doTheThingAction */
        $doTheThingAction = $this->getReference(ActionsFixtures::DO_THE_THING);
        /** @var Action $ungag */
        $ungag = $this->getReference(ActionsFixtures::UNGAG_DEFAULT);
        /** @var Action $healAction */
        $healAction = $this->getReference(ActionsFixtures::HEAL);
        /** @var Action $selfHealAction */
        $selfHealAction = $this->getReference(ActionsFixtures::SELF_HEAL);

        /** @var Action $extractSporeAction */
        $extractSporeAction = $this->getReference(MushActionFixtures::EXTRACT_SPORE);
        /** @var Action $infectAction */
        $infectAction = $this->getReference(MushActionFixtures::SPREAD_FIRE);

        // Skills actions
        // @TODO: after skill implementation, action will be given by skills
        /** @var Action $comfortAction */
        $comfortAction = $this->getReference(ActionsFixtures::COMFORT_DEFAULT);
        /** @var Action $motivationalSpeechAction */
        $motivationalSpeechAction = $this->getReference(ActionsFixtures::MOTIVATIONAL_SPEECH);
        /** @var Action $boringSpeechAction */
        $boringSpeechAction = $this->getReference(ActionsFixtures::BORING_SPEECH);
        /** @var Action $extinguishManuallyAction */
        $extinguishManuallyAction = $this->getReference(ActionsFixtures::EXTINGUISH_MANUALLY);
        /** @var Action $surgeryAction */
        $surgeryAction = $this->getReference(ActionsFixtures::SURGERY);

        /** @var Action $makeSickAction */
        $makeSickAction = $this->getReference(MushActionFixtures::MAKE_SICK);
        /** @var Action $spreadFireAction */
        $spreadFireAction = $this->getReference(MushActionFixtures::INFECT_PLAYER);
        /** @var Action $screwTalkieAction */
        $screwTalkieAction = $this->getReference(MushActionFixtures::SCREW_TALKIE);

        $defaultActions = new ArrayCollection([
            $hitAction,
            $hideAction,
            $searchAction,
            $phagocyteAction,
            $reportFireAction,
            $infectAction,
            $extractSporeAction,
            $getUpAction,
            $comfortAction,
            $extinguishManuallyAction,
            $motivationalSpeechAction,
            $boringSpeechAction,
            $healAction,
            $selfHealAction,
            $rejuvenateAlphaAction,
            $spreadFireAction,
            $flirtAction,
            $doTheThingAction,
            $makeSickAction,
            $fakeDiseaseAction,
            $screwTalkieAction,
            $ungag,
            $suicideAction,
            $surgeryAction,
        ]);

        $characterConfig = new CharacterConfig();

        $characterConfig
            ->setMaxNumberPrivateChannel(3)
            ->setInitHealthPoint(14)
            ->setMaxHealthPoint(14)
            ->setInitMoralPoint(14)
            ->setMaxMoralPoint(14)
            ->setInitSatiety(0)
            ->setInitActionPoint(8)
            ->setMaxActionPoint(12)
            ->setInitMovementPoint(12)
            ->setMaxMovementPoint(12)
            ->setMaxItemInInventory(3)
            ->setActions($defaultActions)
        ;

        return $characterConfig;
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
        ];
    }
}
