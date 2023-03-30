<?php

namespace Mush\Hunter\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\Entity\ProbaCollection;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Repository\HunterConfigRepository;
use Mush\Status\Repository\StatusConfigRepository;

class HunterConfigDataLoader extends ConfigDataLoader
{
    private HunterConfigRepository $hunterConfigRepository;
    private StatusConfigRepository $statusConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        HunterConfigRepository $hunterConfigRepository,
        StatusConfigRepository $statusConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->hunterConfigRepository = $hunterConfigRepository;
        $this->statusConfigRepository = $statusConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (HunterConfigData::$dataArray as $hunterConfigData) {
            $hunterConfig = $this->hunterConfigRepository->findOneBy(['name' => $hunterConfigData['name']]);

            if ($hunterConfig === null) {
                $hunterConfig = new HunterConfig();
            } elseif (!($hunterConfig instanceof HunterConfig)) {
                $this->entityManager->remove($hunterConfig);
                $hunterConfig = new HunterConfig();
            }

            $hunterConfig
                ->setName($hunterConfigData['name'])
                ->setHunterName($hunterConfigData['hunterName'])
                ->setInitialHealth($hunterConfigData['initialHealth'])
                ->setInitialArmor($hunterConfigData['initialArmor'])
                ->setDamageRange(new ProbaCollection($hunterConfigData['damageRange']))
                ->setHitChance($hunterConfigData['hitChance'])
                ->setDodgeChance($hunterConfigData['dodgeChance'])
                ->setDrawCost($hunterConfigData['drawCost'])
                ->setMaxPerWave($hunterConfigData['maxPerWave'])
                ->setDrawWeight($hunterConfigData['drawWeight'])
                ->setSpawnDifficulty($hunterConfigData['spawnDifficulty'])
            ;
            $this->setHunterConfigInitialStatuses($hunterConfig, $hunterConfigData);

            $this->entityManager->persist($hunterConfig);
        }
        $this->entityManager->flush();
    }

    private function setHunterConfigInitialStatuses(HunterConfig $hunterConfig, array $hunterConfigData): void
    {
        $statusConfigs = [];
        foreach ($hunterConfigData['initialStatuses'] as $statusConfigName) {
            $statusConfig = $this->statusConfigRepository->findOneBy(['statusName' => $statusConfigName]);
            if ($statusConfig === null) {
                throw new \Exception('Status config not found: ' . $statusConfigName);
            }
            $statusConfigs[] = $statusConfig;
        }
        $hunterConfig->setInitialStatuses(new ArrayCollection($statusConfigs));
    }
}
