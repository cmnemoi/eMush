<?php

namespace Mush\Modifier\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\Repository\EventConfigRepository;
use Mush\Modifier\Repository\ModifierActivationRequirementRepository;
use Mush\Modifier\Repository\ModifierConfigRepository;

abstract class ModifierConfigDataLoader extends ConfigDataLoader
{
    protected ModifierConfigRepository $modifierConfigRepository;
    protected ModifierActivationRequirementRepository $modifierActivationRequirementRepository;
    protected EventConfigRepository $eventConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ModifierConfigRepository $modifierConfigRepository,
        ModifierActivationRequirementRepository $modifierActivationRequirementRepository,
        EventConfigRepository $eventConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->modifierConfigRepository = $modifierConfigRepository;
        $this->modifierActivationRequirementRepository = $modifierActivationRequirementRepository;
        $this->eventConfigRepository = $eventConfigRepository;
    }

    protected function getModifierConfigActivationRequirements(array $modifierConfigData, string $parameterName): array
    {
        $modifierActivationRequirements = [];
        foreach ($modifierConfigData[$parameterName] as $activationRequirementName) {
            $modifierActivationRequirement = $this->modifierActivationRequirementRepository->findOneBy(['name' => $activationRequirementName]);

            if ($modifierActivationRequirement === null) {
                throw new \Exception('Modifier activation requirement not found: ' . $activationRequirementName);
            }
            $modifierActivationRequirements[] = $modifierActivationRequirement;
        }

        return $modifierActivationRequirements;
    }
}
