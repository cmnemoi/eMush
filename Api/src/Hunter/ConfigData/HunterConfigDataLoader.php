<?php

namespace Mush\Hunter\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Repository\HunterConfigRepository;
use Mush\Status\Repository\StatusConfigRepository;

final class HunterConfigDataLoader extends ConfigDataLoader
{
    public function __construct(
        EntityManagerInterface $entityManager,
        private HunterConfigRepository $hunterConfigRepository,
        private StatusConfigRepository $statusConfigRepository,
    ) {
        parent::__construct($entityManager);
    }

    public function loadConfigsData(): void
    {
        foreach (HunterConfigData::$dataArray as $hunterConfigData) {
            $hunterConfig = $this->hunterConfigRepository->findOneBy(['name' => $hunterConfigData['name']]);

            if ($hunterConfig === null) {
                $hunterConfig = new HunterConfig();
            } elseif (!$hunterConfig instanceof HunterConfig) {
                $this->entityManager->remove($hunterConfig);
                $hunterConfig = new HunterConfig();
            }

            $hunterConfig
                ->setName($hunterConfigData['name'])
                ->setHunterName($hunterConfigData['hunterName'])
                ->setInitialHealth($hunterConfigData['initialHealth'])
                ->setDamageRange(new ProbaCollection($hunterConfigData['damageRange']))
                ->setHitChance($hunterConfigData['hitChance'])
                ->setDodgeChance($hunterConfigData['dodgeChance'])
                ->setDrawCost($hunterConfigData['drawCost'])
                ->setMaxPerWave($hunterConfigData['maxPerWave'])
                ->setDrawWeight($hunterConfigData['drawWeight'])
                ->setSpawnDifficulty($hunterConfigData['spawnDifficulty'])
                ->setScrapDropTable(new ProbaCollection($hunterConfigData['scrapDropTable']))
                ->setNumberOfDroppedScrap($hunterConfigData['numberOfDroppedScrap'])
                ->setTargetProbabilities($hunterConfigData['targetProbabilities'])
                ->setBonusAfterFailedShot($hunterConfigData['bonusAfterFailedShot'])
                ->setNumberOfActionsPerCycle($hunterConfigData['numberOfActionsPerCycle']);
            $this->setHunterConfigInitialStatuses($hunterConfig, $hunterConfigData);

            $this->entityManager->persist($hunterConfig);
        }
        $this->entityManager->flush();
    }

    private function setHunterConfigInitialStatuses(HunterConfig $hunterConfig, array $hunterConfigData): void
    {
        $statusConfigs = [];
        foreach ($hunterConfigData['initialStatuses'] as $statusConfigName) {
            $statusConfig = $this->statusConfigRepository->findOneBy(['name' => $statusConfigName]);
            if ($statusConfig === null) {
                throw new \Exception('Status config not found: ' . $statusConfigName);
            }
            $statusConfigs[] = $statusConfig;
        }
        $hunterConfig->setInitialStatuses($statusConfigs);
    }
}
