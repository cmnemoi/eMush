<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Daedalus\DataFixtures\DaedalusConfigFixtures;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Disease\DataFixtures\DiseaseCausesConfigFixtures;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Equipment\DataFixtures\ItemConfigFixtures;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;

class GameConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const DEFAULT_GAME_CONFIG = 'default.game.config';

    public function load(ObjectManager $manager): void
    {
        $gameConfig = new GameConfig();

        $gameConfig
            ->setName(GameConfigEnum::DEFAULT)
        ;

        $manager->persist($gameConfig);

        // adding statusConfigs (only adding most essentials)
        $gameConfig = $this->addStatusConfig(StatusFixtures::DIRTY_STATUS, $gameConfig);
        $gameConfig = $this->addStatusConfig(ChargeStatusFixtures::MUSH_STATUS, $gameConfig, ChargeStatusConfig::class);
        $gameConfig = $this->addStatusConfig(ChargeStatusFixtures::FIRE_STATUS, $gameConfig, ChargeStatusConfig::class);
        $gameConfig = $this->addStatusConfig(StatusFixtures::BROKEN_STATUS, $gameConfig);

        // adding diseaseCause
        /** @var $diseaseContactCause $diseaseContactCause */
        $diseaseContactCause = $this->getReference(DiseaseCausesConfigFixtures::CONTACT_DISEASE_CAUSE_TEST, DiseaseCauseConfig::class);
        /** @var $diseaseCycleCause $diseaseCycleCause */
        $diseaseCycleCause = $this->getReference(DiseaseCausesConfigFixtures::CONTACT_DISEASE_CAUSE_TEST, DiseaseCauseConfig::class);
        $gameConfig->addDiseaseCauseConfig($diseaseCycleCause)->addDiseaseCauseConfig($diseaseCycleCause);

        // adding difficulty config
        $difficultyConfig = $this->getReference(DifficultyConfigFixtures::DEFAULT_DIFFICULTY_CONFIG, DifficultyConfig::class);
        $gameConfig->setDifficultyConfig($difficultyConfig);

        // adding item configs
        $gameConfig = $this->addEquipmentConfig(ItemConfigFixtures::METAL_SCRAPS, $gameConfig, ItemConfig::class);
        $gameConfig = $this->addEquipmentConfig(ItemConfigFixtures::PLASTIC_SCRAPS, $gameConfig, ItemConfig::class);

        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $this->getReference(DaedalusConfigFixtures::DEFAULT_DAEDALUS, DaedalusConfig::class);
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $manager->flush();

        $this->addReference(self::DEFAULT_GAME_CONFIG, $gameConfig);
    }

    public function addStatusConfig(string $name, GameConfig $gameConfig, string $class = StatusConfig::class): GameConfig
    {
        /** @var StatusConfig $config */
        $config = $this->getReference($name, $class);

        return $gameConfig->addStatusConfig($config);
    }

    public function addEquipmentConfig(string $name, GameConfig $gameConfig, string $class = EquipmentConfig::class): GameConfig
    {
        /** @var EquipmentConfig $config */
        $config = $this->getReference($name, $class);

        return $gameConfig->addEquipmentConfig($config);
    }

    public function getDependencies(): array
    {
        return [
            StatusFixtures::class,
            ChargeStatusFixtures::class,
            DaedalusConfigFixtures::class,
            DifficultyConfigFixtures::class,
            DiseaseCausesConfigFixtures::class,
            ItemConfigFixtures::class,
        ];
    }
}
