<?php

namespace Mush\Hunter\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Repository\HunterConfigRepository;

class HunterConfigDataLoader extends ConfigDataLoader
{
    private HunterConfigRepository $hunterConfigRepository;

    public function __construct(EntityManagerInterface $entityManager, HunterConfigRepository $hunterConfigRepository)
    {
        parent::__construct($entityManager);
        $this->hunterConfigRepository = $hunterConfigRepository;
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
                ->setInitialCharge($hunterConfigData['initialCharge'])
                ->setInitialArmor($hunterConfigData['initialArmor'])
                ->setMinDamage($hunterConfigData['minDamage'])
                ->setMaxDamage($hunterConfigData['maxDamage'])
                ->setHitChance($hunterConfigData['hitChance'])
                ->setDodgeChance($hunterConfigData['dodgeChance'])
                ->setDrawCost($hunterConfigData['drawCost'])
                ->setMaxPerWave($hunterConfigData['maxPerWave'])
                ->setDrawWeight($hunterConfigData['drawWeight'])
            ;

            $this->entityManager->persist($hunterConfig);
        }
        $this->entityManager->flush();
    }
}
