<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\TitleConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\TitleEnum;

class TitleConfigFixtures extends Fixture
{
    /**
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $commander = new TitleConfig();
        $commander
            ->setName(TitleEnum::COMMANDER)
            ->setPriority([CharacterEnum::JIN_SU, CharacterEnum::CHAO, CharacterEnum::GIOELE, CharacterEnum::STEPHEN, CharacterEnum::FRIEDA, CharacterEnum::KUAN_TI, CharacterEnum::HUA, CharacterEnum::DEREK, CharacterEnum::ROLAND, CharacterEnum::RALUCA, CharacterEnum::FINOLA, CharacterEnum::PAOLA, CharacterEnum::TERRENCE, CharacterEnum::ELEESHA, CharacterEnum::ANDIE, CharacterEnum::IAN, CharacterEnum::JANICE, CharacterEnum::CHUN])
        ;
        $manager->persist($commander);

        $neronManager = new TitleConfig();
        $neronManager
            ->setName(TitleEnum::NERON_MANAGER)
            ->setPriority([CharacterEnum::JANICE, CharacterEnum::TERRENCE, CharacterEnum::ELEESHA, CharacterEnum::RALUCA, CharacterEnum::FINOLA, CharacterEnum::ANDIE, CharacterEnum::FRIEDA, CharacterEnum::IAN, CharacterEnum::STEPHEN, CharacterEnum::PAOLA, CharacterEnum::JIN_SU, CharacterEnum::HUA, CharacterEnum::KUAN_TI, CharacterEnum::GIOELE, CharacterEnum::CHUN, CharacterEnum::ROLAND, CharacterEnum::CHAO, CharacterEnum::DEREK])
        ;
        $manager->persist($neronManager);

        $comManager = new TitleConfig();
        $comManager
            ->setName(TitleEnum::COM_MANAGER)
            ->setPriority([CharacterEnum::PAOLA, CharacterEnum::ELEESHA, CharacterEnum::ANDIE, CharacterEnum::STEPHEN, CharacterEnum::JANICE, CharacterEnum::ROLAND, CharacterEnum::HUA, CharacterEnum::DEREK, CharacterEnum::JIN_SU, CharacterEnum::KUAN_TI, CharacterEnum::GIOELE, CharacterEnum::CHUN, CharacterEnum::IAN, CharacterEnum::FINOLA, CharacterEnum::TERRENCE, CharacterEnum::FRIEDA, CharacterEnum::CHAO, CharacterEnum::RALUCA])
        ;
        $manager->persist($comManager);

        /** @var ArrayCollection $titleConfigs */
        $titleConfigs = new ArrayCollection([
            $commander, $neronManager, $comManager,
        ]);
        $gameConfig->setTitleConfigs($titleConfigs);

        $manager->flush();
    }
}
