<?php

namespace Mush\Disease\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Modifier\Repository\ModifierConfigRepository;

class DiseaseConfigDataLoader extends ConfigDataLoader
{
    private DiseaseConfigRepository $diseaseConfigRepository;
    private ModifierConfigRepository $modifierConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DiseaseConfigRepository $diseaseConfigRepository,
        ModifierConfigRepository $modifierConfigRepository,
    ) {
        parent::__construct($entityManager);
        $this->entityManager = $entityManager;
        $this->diseaseConfigRepository = $diseaseConfigRepository;
        $this->modifierConfigRepository = $modifierConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (DiseaseConfigData::getAll() as $diseaseConfigDto) {
            $diseaseConfig = $this->diseaseConfigRepository->findOneBy(['name' => $diseaseConfigDto->key]);

            if ($diseaseConfig instanceof DiseaseConfig) {
                $diseaseConfig->updateFromDto($diseaseConfigDto);
            } else {
                $diseaseConfig = DiseaseConfig::fromDto($diseaseConfigDto);
            }

            $this->entityManager->persist($diseaseConfig);
        }
        $this->entityManager->flush();
    }
}
