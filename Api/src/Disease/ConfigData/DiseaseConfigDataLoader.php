<?php

namespace Mush\Disease\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Disease\Repository\SymptomConfigRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Repository\ModifierConfigRepository;

class DiseaseConfigDataLoader extends ConfigDataLoader
{
    private DiseaseConfigRepository $diseaseConfigRepository;
    private ModifierConfigRepository $modifierConfigRepository;
    private SymptomConfigRepository $symptomConfigRepository;

    // @TODO : remove SymptomConfig logic when it will be definitely deprecated

    public function __construct(
        EntityManagerInterface $entityManager,
        DiseaseConfigRepository $diseaseConfigRepository,
        ModifierConfigRepository $modifierConfigRepository,
        SymptomConfigRepository $symptomConfigRepository)
    {
        parent::__construct($entityManager);
        $this->diseaseConfigRepository = $diseaseConfigRepository;
        $this->modifierConfigRepository = $modifierConfigRepository;
        $this->symptomConfigRepository = $symptomConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (DiseaseConfigData::$dataArray as $diseaseConfigData) {
            $diseaseConfig = $this->diseaseConfigRepository->findOneBy(['name' => $diseaseConfigData['name']]);

            if ($diseaseConfig === null) {
                $diseaseConfig = new DiseaseConfig();
            }

            $diseaseConfig
                ->setName($diseaseConfigData['name'])
                ->setDiseaseName($diseaseConfigData['diseaseName'])
                ->setType($diseaseConfigData['type'])
                ->setResistance($diseaseConfigData['resistance'])
                ->setDelayMin($diseaseConfigData['delayMin'])
                ->setDelayLength($diseaseConfigData['delayLength'])
                ->setDiseasePointMin($diseaseConfigData['diseasePointMin'])
                ->setDiseasePointLength($diseaseConfigData['diseasePointLength'])
                ->setOverride($diseaseConfigData['override'])
            ;
            $this->setDiseaseConfigModifierConfigs($diseaseConfig, $diseaseConfigData);
            $this->setDiseaseConfigSymptomConfigs($diseaseConfig, $diseaseConfigData);

            $this->entityManager->persist($diseaseConfig);
        }
        $this->entityManager->flush();
    }

    private function setDiseaseConfigModifierConfigs(DiseaseConfig $diseaseConfig, array $diseaseConfigData): void
    {
        $modifierConfigs = [];
        foreach ($diseaseConfigData['modifierConfigs'] as $modifierConfigName) {
            /** @var AbstractModifierConfig $modifierConfig */
            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $modifierConfigName]);
            if ($modifierConfig === null) {
                throw new \Exception('Modifier config not found: ' . $modifierConfigName);
            }
            $modifierConfigs[] = $modifierConfig;
        }
        $diseaseConfig->setModifierConfigs($modifierConfigs);
    }

    private function setDiseaseConfigSymptomConfigs(DiseaseConfig $diseaseConfig, array $diseaseConfigData): void
    {
        $symptomConfigs = [];
        foreach ($diseaseConfigData['symptomConfigs'] as $symptomConfigName) {
            $symptomConfig = $this->symptomConfigRepository->findOneBy(['name' => $symptomConfigName]);
            if ($symptomConfig === null) {
                throw new \Exception('Symptom config not found: ' . $symptomConfigName);
            }
            $symptomConfigs[] = $symptomConfig;
        }
        $diseaseConfig->setSymptomConfigs($symptomConfigs);
    }
}
