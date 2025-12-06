<?php

namespace Mush\Status\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\ModifierConfigFixtures;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Status\ConfigData\StatusConfigData;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;

class ChargeStatusFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        foreach (StatusConfigData::$dataArray as $statusConfigData) {
            if ($statusConfigData['type'] !== 'charge_status_config') {
                continue;
            }

            $statusConfig = new ChargeStatusConfig();

            $statusConfig
                ->setName($statusConfigData['name'])
                ->setStatusName($statusConfigData['statusName'])
                ->setVisibility($statusConfigData['visibility'])
                ->setChargeVisibility($statusConfigData['chargeVisibility'])
                ->setChargeStrategy($statusConfigData['chargeStrategy'])
                ->setMaxCharge($statusConfigData['maxCharge'])
                ->setStartCharge($statusConfigData['startCharge'])
                ->setDischargeStrategies($statusConfigData['dischargeStrategies'])
                ->setAutoRemove($statusConfigData['autoRemove']);
            $this->setStatusConfigModifierConfigs($statusConfig, $statusConfigData['modifierConfigs'], $manager);
            $this->setStatusConfigActionConfigs($statusConfig, $statusConfigData['actionConfigs'], $manager);

            $manager->persist($statusConfig);

            $this->addReference($statusConfig->getName(), $statusConfig);

            $gameConfig->addStatusConfig($statusConfig);
        }

        $manager->persist($gameConfig);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            ActionsFixtures::class,
            ModifierConfigFixtures::class,
        ];
    }

    protected function setStatusConfigModifierConfigs(StatusConfig $statusConfig, array $modifierConfigsArray, ObjectManager $manager): void
    {
        $modifierConfigs = [];
        foreach ($modifierConfigsArray as $modifierConfigName) {
            /** @var AbstractModifierConfig $modifierConfig */
            $modifierConfig = $manager->getRepository(AbstractModifierConfig::class)->findOneBy(['name' => $modifierConfigName]);
            if ($modifierConfig === null) {
                throw new \Exception("Modifier config {$modifierConfigName} not found");
            }
            $modifierConfigs[] = $modifierConfig;
        }
        $statusConfig->setModifierConfigs($modifierConfigs);
    }

    protected function setStatusConfigActionConfigs(StatusConfig $statusConfig, array $actionConfigsArray, ObjectManager $manager): void
    {
        $actionConfigs = [];
        foreach ($actionConfigsArray as $actionConfigName) {
            /** @var ActionConfig $actionConfig */
            $actionConfig = $manager->getRepository(ActionConfig::class)->findOneBy(['name' => $actionConfigName]);
            if ($actionConfig === null) {
                throw new \Exception("Action config {$actionConfigName} not found");
            }
            $actionConfigs[] = $actionConfig;
        }
        $statusConfig->setActionConfigs($actionConfigs);
    }
}
