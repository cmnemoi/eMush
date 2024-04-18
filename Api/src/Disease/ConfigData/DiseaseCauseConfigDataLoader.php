<?php

namespace Mush\Disease\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Repository\DiseaseCauseConfigRepository;
use Mush\Game\ConfigData\ConfigDataLoader;

class DiseaseCauseConfigDataLoader extends ConfigDataLoader
{
    private DiseaseCauseConfigRepository $diseaseCauseConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DiseaseCauseConfigRepository $diseaseCauseConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->diseaseCauseConfigRepository = $diseaseCauseConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (DiseaseCauseConfigData::$dataArray as $diseaseCauseConfigData) {
            $diseaseCauseConfig = $this->diseaseCauseConfigRepository->findOneBy(['name' => $diseaseCauseConfigData['name']]);

            if ($diseaseCauseConfig === null) {
                $diseaseCauseConfig = new DiseaseCauseConfig();
            }

            $diseaseCauseConfig
                ->setName($diseaseCauseConfigData['name'])
                ->setCauseName($diseaseCauseConfigData['causeName'])
                ->setDiseases($diseaseCauseConfigData['diseases']);

            $this->entityManager->persist($diseaseCauseConfig);
        }
        $this->entityManager->flush();
    }
}
