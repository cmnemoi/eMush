<?php

namespace App\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;

class GameConfigFactory extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        /** @var Module\DataFactory $factory */
        $factory = $this->getModule('DataFactory');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getModule('Doctrine2')->_getEntityManager();

        $factory->_define(DifficultyConfig::class, [
            'name' => GameConfigEnum::TEST,
            'equipmentBreakRate' => 0,
            'doorBreakRate' => 0,
            'equipmentFireBreakRate' => 0,
            'startingFireRate' => 0,
            'propagatingFireRate' => 0,
            'hullFireDamageRate' => 0,
            'tremorRate' => 0,
            'metalPlateRate' => 0,
            'electricArcRate' => 0,
            'panicCrisisRate' => 0,
            'fireHullDamage' => [2 => 1],
            'firePlayerDamage' => [2 => 1],
            'electricArcPlayerDamage' => [3 => 1],
            'tremorPlayerDamage' => [2 => 1],
            'metalPlatePlayerDamage' => [6 => 1],
            'panicCrisisPlayerDamage' => [3 => 1],
            'plantDiseaseRate' => 0,
            'cycleDiseaseRate' => 0,
        ]);
        $factory->_define(DaedalusConfig::class, [
            'name' => 'testDaedalusConfig',
            'maxOxygen' => 32,
            'maxFuel' => 32,
            'maxHull' => 100,
            'initOxygen' => 10,
            'initFuel' => 10,
            'initHull' => 100,
            'initShield' => -2,
            'dailySporeNb' => 4,
            'nbMush' => 2,
            'cycleLength' => 3 * 60,
            'cyclePerGameDay' => 8,
        ]);

        $statusRepository = $entityManager->getRepository(StatusConfig::class);
        $factory->_define(GameConfig::class, [
            'name' => GameConfigEnum::TEST,
            'difficultyConfig' => 'entity|' . DifficultyConfig::class,
            'daedalusConfig' => 'entity|' . DaedalusConfig::class,
            'statusConfigs' => function($entity) use($statusRepository) {return $statusRepository->findBy(['statusName' => StatusEnum::ATTEMPT]);},
                // $statusRepository->findOneBy(['name' => StatusEnum::ATTEMPT]),
                // $statusRepository->findOneBy(['name' => StatusEnum::FIRE]),
                // $statusRepository->findOneBy(['name' => StatusEnum::FIRE])
        ]);

        $factory->_define(LocalizationConfig::class, [
            'timeZone' => 'UTC',
            'language' => LanguageEnum::FRENCH,
            'name' => 'localization_test',
        ]);
    }

    private function defineStatusConfig(Module $factory, EntityManagerInterface $entityManager): void
    {
        $factory->_define(StatusConfig::class, [
            'name' => 'dirty_test',
            'statusName' => PlayerStatusEnum::DIRTY,
        ]);

        $factory->_define(StatusConfig::class, [
            'name' => 'broken_test',
            'statusName' => EquipmentStatusEnum::BROKEN,
        ]);

        $factory->_define(StatusConfig::class, [
            'name' => 'unstable_test',
            'statusName' => EquipmentStatusEnum::UNSTABLE,
        ]);
        $factory->_define(StatusConfig::class, [
            'name' => 'hazardous_test',
            'statusName' => EquipmentStatusEnum::HAZARDOUS,
        ]);

        $factory->_define(StatusConfig::class, [
            'name' => 'thirsty_test',
            'statusName' => EquipmentStatusEnum::PLANT_THIRSTY,
        ]);
        $factory->_define(StatusConfig::class, [
            'name' => 'dry_test',
            'statusName' => EquipmentStatusEnum::PLANT_DRY,
        ]);
        $factory->_define(StatusConfig::class, [
            'name' => 'plant_diseased_test',
            'statusName' => EquipmentStatusEnum::PLANT_DISEASED,
        ]);

        $factory->_define(StatusConfig::class, [
            'name' => 'full_stomach_test',
            'statusName' => PlayerStatusEnum::FULL_STOMACH,
        ]);
        $factory->_define(StatusConfig::class, [
            'name' => 'starving_test',
            'statusName' => PlayerStatusEnum::STARVING,
        ]);
        $factory->_define(StatusConfig::class, [
            'name' => 'demoralized_test',
            'statusName' => PlayerStatusEnum::DEMORALIZED,
        ]);
        $factory->_define(StatusConfig::class, [
            'name' => 'suicidal_test',
            'statusName' => PlayerStatusEnum::SUICIDAL,
        ]);

        $factory->_define(ChargeStatusConfig::class, [
            'name' => 'mush_test',
            'maxCharge' => 1,
            'startCharge' => 1,
            'statusName' => PlayerStatusEnum::MUSH,
        ]);
        $factory->_define(ChargeStatusConfig::class, [
            'name' => 'fire_test',
            'chargeStrategy' => ChargeStrategyTypeEnum::CYCLE_INCREMENT,
            'statusName' => StatusEnum::FIRE,
        ]);
        $factory->_define(Attempt::class, [
            'name' => 'attempt_test',
            'startCharge' => 0,
            'statusName' => StatusEnum::ATTEMPT,
        ]);
    }
}
