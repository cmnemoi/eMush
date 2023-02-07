<?php

namespace Mush\Disease\Service\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Repository\DiseaseCauseConfigRepository;
use Mush\Game\Service\ConfigData\ConfigDataLoader;

class DiseaseCauseConfigDataLoader extends ConfigDataLoader
{
    private EntityManagerInterface $entityManager;
    private DiseaseCauseConfigRepository $diseaseCauseConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DiseaseCauseConfigRepository $diseaseCauseConfigRepository)
    {
        $this->entityManager = $entityManager;
        $this->diseaseCauseConfigRepository = $diseaseCauseConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (DiseaseCauseConfigData::$dataArray as $diseaseCauseConfigData) {
            $diseaseCauseConfig = $this->diseaseCauseConfigRepository->findOneBy(['name' => $diseaseCauseConfigData['name']]);

            if ($diseaseCauseConfig !== null) {
                continue;
            }

            $diseaseCauseConfig = new DiseaseCauseConfig();
            $diseaseCauseConfig
                ->setName($diseaseCauseConfigData['name'])
                ->setCauseName($diseaseCauseConfigData['causeName'])
                ->setDiseases($diseaseCauseConfigData['diseases'])
            ;

            $this->entityManager->persist($diseaseCauseConfig);
        }
        $this->entityManager->flush();
    }
}
