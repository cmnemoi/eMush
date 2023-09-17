<?php

namespace Mush\Disease\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Repository\SymptomActivationRequirementRepository;
use Mush\Disease\Repository\SymptomConfigRepository;
use Mush\Game\ConfigData\ConfigDataLoader;

class SymptomConfigDataLoader extends ConfigDataLoader
{
    private SymptomConfigRepository $symptomConfigRepository;
    private SymptomActivationRequirementRepository $symptomActivationRequirementRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SymptomConfigRepository $symptomConfigRepository,
        SymptomActivationRequirementRepository $symptomActivationRequirementRepository)
    {
        parent::__construct($entityManager);
        $this->symptomConfigRepository = $symptomConfigRepository;
        $this->symptomActivationRequirementRepository = $symptomActivationRequirementRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (SymptomConfigData::$dataArray as $symptomConfigData) {
            $symptomConfig = $this->symptomConfigRepository->findOneBy(['name' => $symptomConfigData['name']]);

            if ($symptomConfig === null) {
                $symptomConfig = new SymptomConfig($symptomConfigData['symptomName']);
            }

            $symptomConfig
                ->setName($symptomConfigData['name'])
                ->setSymptomName($symptomConfigData['symptomName'])
                ->setTrigger($symptomConfigData['trigger'])
                ->setVisibility($symptomConfigData['visibility'])
            ;
            $this->setSymptomConfigActivationRequirements($symptomConfig, $symptomConfigData);

            $this->entityManager->persist($symptomConfig);
        }
        $this->entityManager->flush();
    }

    private function setSymptomConfigActivationRequirements(SymptomConfig $symptomConfig, array $symptomConfigData): void
    {
        $symptomActivationRequirements = [];
        foreach ($symptomConfigData['symptomActivationRequirements'] as $activationRequirementName) {
            $symptomActivationRequirement = $this->symptomActivationRequirementRepository->findOneBy(['name' => $activationRequirementName]);
            if ($symptomActivationRequirement === null) {
                throw new \Exception('Symptom activation requirement not found: ' . $activationRequirementName);
            }
            $symptomActivationRequirements[] = $symptomActivationRequirement;
        }
        $symptomConfig->setSymptomActivationRequirements($symptomActivationRequirements);
    }
}
