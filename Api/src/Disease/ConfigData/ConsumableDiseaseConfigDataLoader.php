<?php

namespace Mush\Disease\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Disease\Entity\Config\ConsumableDiseaseConfig;
use Mush\Disease\Repository\ConsumableDiseaseConfigRepository;
use Mush\Game\ConfigData\ConfigDataLoader;

class ConsumableDiseaseConfigDataLoader extends ConfigDataLoader
{
    private ConsumableDiseaseConfigRepository $diseaseCauseConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ConsumableDiseaseConfigRepository $diseaseCauseConfigRepository)
    {
        parent::__construct($entityManager);
        $this->diseaseCauseConfigRepository = $diseaseCauseConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (ConsumableDiseaseConfigData::$dataArray as $diseaseCauseConfigData) {
            $diseaseCauseConfig = $this->diseaseCauseConfigRepository->findOneBy(['name' => $diseaseCauseConfigData['name']]);

            if ($diseaseCauseConfig === null) {
                $diseaseCauseConfig = new ConsumableDiseaseConfig();
            }

            $diseaseCauseConfig
                ->setName($diseaseCauseConfigData['name'])
                ->setCauseName($diseaseCauseConfigData['causeName'])
                ->setDiseasesName($diseaseCauseConfigData['diseasesName'])
                ->setCuresName($diseaseCauseConfigData['curesName'])
                ->setDiseasesChances($diseaseCauseConfigData['diseasesChances'])
                ->setCuresChances($diseaseCauseConfigData['curesChances'])
                ->setDiseasesDelayMin($diseaseCauseConfigData['diseasesDelayMin'])
                ->setDiseasesDelayLength($diseaseCauseConfigData['diseasesDelayLength'])
                ->setEffectNumber($diseaseCauseConfigData['effectNumber'])
            ;

            $this->entityManager->persist($diseaseCauseConfig);
        }
        $this->entityManager->flush();
    }
}
