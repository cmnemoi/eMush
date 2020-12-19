<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Daedalus\DataFixtures\DaedalusConfigFixtures;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class CharacterConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $andie = new CharacterConfig();
        $andie
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::ANDIE)
            ->setStatuses([])
            ->setSkills([
                SkillEnum::CONFIDENT,
                SkillEnum::DEVOTION,
                SkillEnum::EXPERT,
                SkillEnum::PILOT,
                SkillEnum::POLYVALENT,
            ])
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

        $chun = new CharacterConfig();
        $chun
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::CHUN)
            ->setStatuses([PlayerStatusEnum::IMMUNIZED])
            ->setSkills([
                SkillEnum::LETHARGY,
                SkillEnum::MANKINDS_ONLY_HOPE,
                SkillEnum::NURSE,
                SkillEnum::PREMONITION,
                SkillEnum::SNEAK,
            ])
        ;
        $manager->persist($chun);

        $derek = new CharacterConfig();
        $derek
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::DEREK)
            ->setStatuses([])
            ->setSkills([
                SkillEnum::FIREFIGHTER,
                SkillEnum::MOTIVATOR,
                SkillEnum::SHOOTER,
                SkillEnum::WRESTLER,
                SkillEnum::HYGIENIST,
            ])
        ;
        $manager->persist($derek);

        $eleesha = new CharacterConfig();
        $eleesha
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::ELEESHA)
            ->setStatuses([])
            ->setSkills([
                SkillEnum::DETERMINED,
                SkillEnum::IT_EXPERT,
                SkillEnum::OBSERVANT,
                SkillEnum::POLYMATH,
                SkillEnum::TECHNICIAN,
                SkillEnum::TRACKER,
            ])
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
            ->setStatuses([])
            ->setSkills([
                SkillEnum::ANTIQUE_PERFUME,
                SkillEnum::ASTROPHYSICIST,
                SkillEnum::IT_EXPERT,
                SkillEnum::PILOT,
                SkillEnum::RADIO_EXPERT,
                SkillEnum::SURVIVALIST,
            ])
        ;
        $manager->persist($frieda);

        $gioele = new CharacterConfig();
        $gioele
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::GIOELE)
            ->setStatuses([])
            ->setSkills([
                SkillEnum::ASTROPHYSICIST,
                SkillEnum::CAFFEINE_JUNKIE,
                SkillEnum::PANIC,
                SkillEnum::PARANOID,
                SkillEnum::SOLID,
                SkillEnum::VICTIMIZER,
            ])
        ;
        $manager->persist($gioele);

        $hua = new CharacterConfig();
        $hua
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::HUA)
            ->setStatuses([])
            ->setSkills([
                SkillEnum::BOTANIST,
                SkillEnum::DETERMINED,
                SkillEnum::PILOT,
                SkillEnum::SURVIVALIST,
                SkillEnum::TECHNICIAN,
                SkillEnum::U_TURN,
            ])
        ;
        $manager->persist($hua);

        $ian = new CharacterConfig();
        $ian
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::IAN)
            ->setStatuses([PlayerStatusEnum::PACIFIST])
            ->setSkills([
                SkillEnum::BIOLOGIST,
                SkillEnum::BOTANIST,
                SkillEnum::FIREFIGHTER,
                SkillEnum::FRUGIVORE,
                SkillEnum::GREEN_THUMB,
                SkillEnum::MYCOLOGIST,
            ])
        ;
        $manager->persist($ian);

        $janice = new CharacterConfig();
        $janice
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::JANICE)
            ->setStatuses([])
            ->setSkills([
                SkillEnum::DIPLOMAT,
                SkillEnum::IT_EXPERT,
                SkillEnum::NERON_ONLY_FRIEND,
                SkillEnum::RADIO_EXPERT,
                SkillEnum::SELF_SACRIFICE,
                SkillEnum::SHRINK,
            ])
        ;
        $manager->persist($janice);

        $jinSu = new CharacterConfig();
        $jinSu
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::KIM_JUN_SU)
            ->setStatuses([])
            ->setSkills([
                SkillEnum::COLD_BLOODED,
                SkillEnum::LEADER,
                SkillEnum::LOGISTICS_EXPERT,
                SkillEnum::PILOT,
                SkillEnum::SHOOTER,
                SkillEnum::STRATEGURU,
            ])
        ;
        $manager->persist($jinSu);

        $kuanTi = new CharacterConfig();
        $kuanTi
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::KUAN_TI)
            ->setStatuses([])
            ->setSkills([
                    SkillEnum::ASTROPHYSICIST,
                    SkillEnum::DESIGNER,
                    SkillEnum::LEADER,
                    SkillEnum::POLITICIAN,
                    SkillEnum::TECHNICIAN,
                    SkillEnum::OPTIMIST,
            ])
        ;
        $manager->persist($kuanTi);

        $paola = new CharacterConfig();
        $paola
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::PAOLA)
            ->setStatuses([])
            ->setSkills([
                SkillEnum::BIOLOGIST,
                SkillEnum::GUNNER,
                SkillEnum::LOGISTICS_EXPERT,
                SkillEnum::RADIO_EXPERT,
                SkillEnum::REBEL,
                SkillEnum::SHOOTER,
            ])
        ;
        $manager->persist($paola);

        $raluca = new CharacterConfig();
        $raluca
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::RALUCA)
            ->setStatuses([PlayerStatusEnum::ANTISOCIAL])
            ->setSkills([
                SkillEnum::DESIGNER,
                SkillEnum::DETACHED_CREWMEMBER,
                SkillEnum::GENIUS,
                SkillEnum::PHYSICIST,
                SkillEnum::TECHNICIAN,
            ])
        ;
        $manager->persist($raluca);

        $roland = new CharacterConfig();
        $roland
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::ROLAND)
            ->setStatuses([])
            ->setSkills([
                SkillEnum::CREATIVE,
                SkillEnum::FIREFIGHTER,
                SkillEnum::PILOT,
                SkillEnum::SHOOTER,
                SkillEnum::SPRINTER,
                SkillEnum::OPTIMIST,
            ])
        ;
        $manager->persist($roland);

        $stephen = new CharacterConfig();
        $stephen
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::STEPHEN)
            ->setStatuses([])
            ->setSkills([
                SkillEnum::APPRENTICE,
                SkillEnum::CHEF,
                SkillEnum::CREATIVE,
                SkillEnum::OPPORTUNIST,
                SkillEnum::SHOOTER,
                SkillEnum::SOLID,
            ])
        ;
        $manager->persist($stephen);

        $terrence = new CharacterConfig();
        $terrence
            ->setGameConfig($gameConfig)
            ->setName(CharacterEnum::TERRENCE)
            ->setStatuses([PlayerStatusEnum::DISABLED])
            ->setSkills([
                SkillEnum::IT_EXPERT,
                SkillEnum::METALWORKER,
                SkillEnum::PILOT,
                SkillEnum::ROBOTICS_EXPERT,
                SkillEnum::SHOOTER,
                SkillEnum::TECHNICIAN,
            ])
        ;
        $manager->persist($terrence);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            DaedalusConfigFixtures::class,
        ];
    }
}
