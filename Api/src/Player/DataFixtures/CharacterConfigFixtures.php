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
use Mush\Status\Enum\PlayerStatusEnum;

class CharacterConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        // @TODO: remove when the game is ready
        /** @var Action $rejuvenateAlphaAction */
        $rejuvenateAlphaAction = $this->getReference(ActionsFixtures::REJUVENATE_ALPHA);
        /** @var Action $comfortAction */
        $comfortAction = $this->getReference(ActionsFixtures::COMFORT_DEFAULT);
        /** @var Action $healAction */
        $healAction = $this->getReference(ActionsFixtures::HEAL);
        /** @var Action $selfHealAction */
        $selfHealAction = $this->getReference(ActionsFixtures::SELF_HEAL);
        /** @var Action $fakeDiseaseAction */
        $fakeDiseaseAction = $this->getReference(MushActionFixtures::FAKE_DISEASE);

        /** @var Action $hitAction */
        $hitAction = $this->getReference(ActionsFixtures::HIT_DEFAULT);
        /** @var Action $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $searchAction */
        $searchAction = $this->getReference(ActionsFixtures::SEARCH_DEFAULT);
        /** @var Action $reportFireAction */
        $reportFireAction = $this->getReference(ActionsFixtures::REPORT_FIRE);
        /** @var Action $getUpAction */
        $getUpAction = $this->getReference(ActionsFixtures::GET_UP);
        /** @var Action $flirtAction */
        $flirtAction = $this->getReference(ActionsFixtures::FLIRT_DEFAULT);
        /** @var Action $doTheThingAction */
        $doTheThingAction = $this->getReference(ActionsFixtures::DO_THE_THING);

        /** @var Action $extractSporeAction */
        $extractSporeAction = $this->getReference(MushActionFixtures::EXTRACT_SPORE);
        /** @var Action $spreadFireAction */
        $spreadFireAction = $this->getReference(MushActionFixtures::INFECT_PLAYER);
        /** @var Action $infectAction */
        $infectAction = $this->getReference(MushActionFixtures::SPREAD_FIRE);
        /** @var Action $makeSickAction */
        $makeSickAction = $this->getReference(MushActionFixtures::MAKE_SICK);

        // Skills actions
        // @TODO: find another way to handle this ?
        /** @var Action $comfortAction */
        $comfortAction = $this->getReference(ActionsFixtures::COMFORT_DEFAULT);
        /** @var Action $motivationalSpeechAction */
        $motivationalSpeechAction = $this->getReference(ActionsFixtures::MOTIVATIONAL_SPEECH);
        /** @var Action $boringSpeechAction */
        $boringSpeechAction = $this->getReference(ActionsFixtures::BORING_SPEECH);
        /** @var Action $extinguishManuallyAction */
        $extinguishManuallyAction = $this->getReference(ActionsFixtures::EXTINGUISH_MANUALLY);

        /** @var ItemConfig $iTrackieConfig */
        $iTrackieConfig = $this->getReference(PersonalEquipmentConfigFixtures::ITRACKIE);

        $defaultActions = new ArrayCollection([
            $hitAction,
            $hideAction,
            $searchAction,
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
        ]);

        /** @var StatusConfig $sporeStatus */
        $sporeStatus = $this->getReference(ChargeStatusFixtures::SPORES);

        $andie = new CharacterConfig();
        $andie
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::ANDIE)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setActions($defaultActions)
            ->setSkills([
                SkillEnum::CONFIDENT,
                SkillEnum::DEVOTION,
                SkillEnum::EXPERT,
                SkillEnum::PILOT,
                SkillEnum::POLYVALENT,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($andie);

//        $chao = new CharacterConfig();
//        $chao
//            ->setGameConfig($gameConfig)
//            ->setName(CharacterEnum::CHAO)
//            ->setStatuses([])
//            ->setSkills([
//                SkillEnum::CRAZY_EYE,
//                SkillEnum::INTIMIDATING,
//                SkillEnum::SHOOTER,
//                SkillEnum::SURVIVALIST,
//                SkillEnum::TORTURER,
//                SkillEnum::WRESTLER,
//            ])
//        ;
//        $manager->persist($chao);

        /** @var StatusConfig $immunizedStatus */
        $immunizedStatus = $this->getReference(StatusFixtures::IMMUNIZED_STATUS);

        $chun = new CharacterConfig();
        $chun
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::CHUN)
            ->setInitStatuses(new ArrayCollection([$immunizedStatus]))
            ->setActions($defaultActions)
            ->setSkills([
                SkillEnum::LETHARGY,
                SkillEnum::MANKINDS_ONLY_HOPE,
                SkillEnum::NURSE,
                SkillEnum::PREMONITION,
                SkillEnum::SNEAK,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($chun);

        /** @var StatusConfig $firstTimeStatus */
        $firstTimeStatus = $this->getReference(ChargeStatusFixtures::FIRST_TIME);

        $derek = new CharacterConfig();
        $derek
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::DEREK)
            ->setActions($defaultActions)
            ->setInitStatuses(new ArrayCollection([$firstTimeStatus, $sporeStatus]))
            ->setSkills([
                SkillEnum::FIREFIGHTER,
                SkillEnum::MOTIVATOR,
                SkillEnum::SHOOTER,
                SkillEnum::WRESTLER,
                SkillEnum::HYGIENIST,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($derek);

        $eleesha = new CharacterConfig();
        $eleesha
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::ELEESHA)
            ->setActions($defaultActions)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::DETERMINED,
                SkillEnum::IT_EXPERT,
                SkillEnum::OBSERVANT,
                SkillEnum::POLYMATH,
                SkillEnum::TECHNICIAN,
                SkillEnum::TRACKER,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($eleesha);

//        $finola = new CharacterConfig();
//        $finola
//            ->setGameConfig($gameConfig)
//            ->setName(CharacterEnum::FINOLA)
//            ->setStatuses([PlayerStatusEnum::GERMAPHOBE])
//            ->setSkills([
//                SkillEnum::BIOLOGIST,
//                SkillEnum::DIPLOMAT,
//                SkillEnum::MEDIC,
//                SkillEnum::NURSE,
//                SkillEnum::OCD,
//            ])
//        ;
//        $manager->persist($finola);

        $frieda = new CharacterConfig();
        $frieda
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::FRIEDA)
            ->setActions($defaultActions)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::ANTIQUE_PERFUME,
                SkillEnum::ASTROPHYSICIST,
                SkillEnum::IT_EXPERT,
                SkillEnum::PILOT,
                SkillEnum::RADIO_EXPERT,
                SkillEnum::SURVIVALIST,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($frieda);

        $gioele = new CharacterConfig();
        $gioele
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::GIOELE)
            ->setActions($defaultActions)
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

        $hua = new CharacterConfig();
        $hua
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::HUA)
            ->setActions($defaultActions)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::BOTANIST,
                SkillEnum::DETERMINED,
                SkillEnum::PILOT,
                SkillEnum::SURVIVALIST,
                SkillEnum::TECHNICIAN,
                SkillEnum::U_TURN,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($hua);

        /** @var StatusConfig $pacifistStatus */
        $pacifistStatus = $this->getReference(StatusFixtures::PACIFIST_STATUS
        );
        $ian = new CharacterConfig();
        $ian
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::IAN)
            ->setActions($defaultActions)
            ->setInitStatuses(new ArrayCollection([$pacifistStatus, $sporeStatus]))
            ->setSkills([
                SkillEnum::BIOLOGIST,
                SkillEnum::BOTANIST,
                SkillEnum::FIREFIGHTER,
                SkillEnum::FRUGIVORE,
                SkillEnum::GREEN_THUMB,
                SkillEnum::MYCOLOGIST,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($ian);

        $janice = new CharacterConfig();
        $janice
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::JANICE)
            ->setActions($defaultActions)
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

        $jinSu = new CharacterConfig();
        $jinSu
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::JIN_SU)
            ->setActions($defaultActions)
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

        $kuanTi = new CharacterConfig();
        $kuanTi
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::KUAN_TI)
            ->setActions($defaultActions)
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

        $paola = new CharacterConfig();
        $paola
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::PAOLA)
            ->setActions($defaultActions)
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
        $raluca = new CharacterConfig();
        $raluca
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::RALUCA)
            ->setActions($defaultActions)
            ->setInitStatuses(new ArrayCollection([$antisocialStatus, $sporeStatus]))
            ->setSkills([
                SkillEnum::DESIGNER,
                SkillEnum::DETACHED_CREWMEMBER,
                SkillEnum::GENIUS,
                SkillEnum::PHYSICIST,
                SkillEnum::TECHNICIAN,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($raluca);

        $roland = new CharacterConfig();
        $roland
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::ROLAND)
            ->setActions($defaultActions)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::CREATIVE,
                SkillEnum::FIREFIGHTER,
                SkillEnum::PILOT,
                SkillEnum::SHOOTER,
                SkillEnum::SPRINTER,
                SkillEnum::OPTIMIST,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($roland);

        $stephen = new CharacterConfig();
        $stephen
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::STEPHEN)
            ->setActions($defaultActions)
            ->setInitStatuses(new ArrayCollection([$sporeStatus]))
            ->setSkills([
                SkillEnum::APPRENTICE,
                SkillEnum::CHEF,
                SkillEnum::CREATIVE,
                SkillEnum::OPPORTUNIST,
                SkillEnum::SHOOTER,
                SkillEnum::SOLID,
            ])
            ->setStartingItem(new ArrayCollection([$iTrackieConfig]))
        ;
        $manager->persist($stephen);

        /** @var StatusConfig $disabledStatus */
        $disabledStatus = $this->getReference(StatusFixtures::DISABLED_STATUS);

        $terrence = new CharacterConfig();
        $terrence
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::TERRENCE)
            ->setActions($defaultActions)
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
        ];
    }
}
