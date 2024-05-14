<?php

namespace Mush\Status\ConfigData;

use Mush\Status\Entity\Config\ContentStatusConfig;

class ContentStatusConfigDataLoader extends StatusConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (StatusConfigData::$dataArray as $statusConfigData) {
            if ($statusConfigData['type'] !== 'content_status_config') {
                continue;
            }
            $statusConfig = $this->statusConfigRepository->findOneBy(['name' => $statusConfigData['name']]);

            if ($statusConfig === null) {
                $statusConfig = new ContentStatusConfig();
            } elseif (!$statusConfig instanceof ContentStatusConfig) {
                $this->entityManager->remove($statusConfig);
                $statusConfig = new ContentStatusConfig();
            }

            $statusConfig
                ->setName($statusConfigData['name'])
                ->setStatusName($statusConfigData['statusName'])
                ->setVisibility($statusConfigData['visibility']);
            $this->setStatusConfigModifierConfigs($statusConfig, $statusConfigData['modifierConfigs']);
            $this->setStatusConfigActionConfigs($statusConfig, $statusConfigData['actionConfigs']);

            $this->entityManager->persist($statusConfig);
        }
        $this->entityManager->flush();
    }
}
