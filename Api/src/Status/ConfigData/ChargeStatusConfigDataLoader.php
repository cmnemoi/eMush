<?php

namespace Mush\Status\ConfigData;

use Mush\Status\Entity\Config\ChargeStatusConfig;

class ChargeStatusConfigDataLoader extends StatusConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (StatusConfigData::$dataArray as $statusConfigData) {
            if ($statusConfigData['type'] !== 'charge_status_config') {
                continue;
            }
            $statusConfig = $this->statusConfigRepository->findOneBy(['name' => $statusConfigData['name']]);

            if ($statusConfig === null) {
                $statusConfig = new ChargeStatusConfig();
            } elseif (!$statusConfig instanceof ChargeStatusConfig) {
                $this->entityManager->remove($statusConfig);
                $statusConfig = new ChargeStatusConfig();
            }

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
            $this->setStatusConfigModifierConfigs($statusConfig, $statusConfigData['modifierConfigs']);
            $this->setStatusConfigActionConfigs($statusConfig, $statusConfigData['actionConfigs']);
            $this->setStatusConfigSkillConfigs($statusConfig, $statusConfigData['skillConfigs']);

            $this->entityManager->persist($statusConfig);
        }
        $this->entityManager->flush();
    }
}
