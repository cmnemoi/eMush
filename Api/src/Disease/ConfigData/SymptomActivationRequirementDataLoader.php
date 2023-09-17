<?php

namespace Mush\Disease\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Disease\Entity\Config\SymptomActivationRequirement;
use Mush\Disease\Repository\SymptomActivationRequirementRepository;
use Mush\Game\ConfigData\ConfigDataLoader;

class SymptomActivationRequirementDataLoader extends ConfigDataLoader
{
    private SymptomActivationRequirementRepository $modifierActivationRequirementRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SymptomActivationRequirementRepository $modifierActivationRequirementRepository)
    {
        parent::__construct($entityManager);
        $this->modifierActivationRequirementRepository = $modifierActivationRequirementRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (SymptomActivationRequirementData::$dataArray as $modifierActivationRequirementData) {
            $modifierActivationRequirement = $this->modifierActivationRequirementRepository->findOneBy(['name' => $modifierActivationRequirementData['name']]);

            if ($modifierActivationRequirement === null) {
                $modifierActivationRequirement = new SymptomActivationRequirement($modifierActivationRequirementData['activationRequirementName']);
            }

            $modifierActivationRequirement
                ->setName($modifierActivationRequirementData['name'])
                ->setActivationRequirement($modifierActivationRequirementData['activationRequirement'])
                ->setValue($modifierActivationRequirementData['value'])
            ;

            $this->entityManager->persist($modifierActivationRequirement);
        }
        $this->entityManager->flush();
    }
}
