<?php

namespace Mush\Equipment\CycleHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Modifier\Entity\Config\AbstractModifierConfig as ModifierConfig;
use Mush\Modifier\Repository\ModifierActivationRequirementRepository;
use Mush\Modifier\Repository\ModifierConfigRepository;

abstract class ModifierConfigDataLoader extends ConfigDataLoader
{
    protected EntityManagerInterface $entityManager;
    protected ModifierConfigRepository $modifierConfigRepository;
    protected ModifierActivationRequirementRepository $modifierActivationRequirementRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ModifierConfigRepository $modifierConfigRepository,
        ModifierActivationRequirementRepository $modifierActivationRequirementRepository)
    {
        $this->entityManager = $entityManager;
        $this->modifierConfigRepository = $modifierConfigRepository;
        $this->modifierActivationRequirementRepository = $modifierActivationRequirementRepository;
    }

    protected function setModifierConfigActivationRequirements(ModifierConfig $modifierConfig, array $modifierConfigData): void
    {
        $modifierActivationRequirements = [];
        foreach ($modifierConfigData['modifierActivationRequirements'] as $activationRequirementName) {
            $modifierActivationRequirement = $this->modifierActivationRequirementRepository->findOneBy(['name' => $activationRequirementName]);
            if ($modifierActivationRequirement === null) {
                throw new \Exception('Modifier activation requirement not found: ' . $activationRequirementName);
            }
            $modifierActivationRequirements[] = $modifierActivationRequirement;
        }
        $modifierConfig->setModifierActivationRequirements($modifierActivationRequirements);
    }
}
