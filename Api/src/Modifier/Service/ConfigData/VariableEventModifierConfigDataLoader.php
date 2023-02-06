<?php

namespace Mush\Modifier\Service\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Service\ConfigData\ConfigDataLoader;
use Mush\Modifier\Entity\Config\AbstractModifierConfig as ModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Repository\ModifierActivationRequirementRepository;
use Mush\Modifier\Repository\ModifierConfigRepository;

class VariableEventModifierConfigDataLoader extends ConfigDataLoader
{
    private EntityManagerInterface $entityManager;
    private ModifierConfigRepository $modifierConfigRepository;
    private ModifierActivationRequirementRepository $modifierActivationRequirementRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ModifierConfigRepository $modifierConfigRepository,
        ModifierActivationRequirementRepository $modifierActivationRequirementRepository)
    {
        $this->entityManager = $entityManager;
        $this->modifierConfigRepository = $modifierConfigRepository;
        $this->modifierActivationRequirementRepository = $modifierActivationRequirementRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (ModifierConfigData::$dataArray as $modifierConfigData) {
            if ($modifierConfigData['type'] !== 'variable_event_modifier') {
                continue;
            }

            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $modifierConfigData['name']]);

            if ($modifierConfig !== null) {
                continue;
            }

            $modifierConfig = new VariableEventModifierConfig();
            $modifierConfig
                ->setDelta($modifierConfigData['delta'])
                ->setTargetVariable($modifierConfigData['targetVariable'])
                ->setMode($modifierConfigData['mode'])
                ->setAppliesOn($modifierConfigData['appliesOn'])
                ->setName($modifierConfigData['name'])
                ->setModifierName($modifierConfigData['modifierName'])
                ->setTargetEvent($modifierConfigData['targetEvent'])
                ->setApplyOnParameterOnly($modifierConfigData['applyOnActionParameter'])
                ->setModifierHolderClass($modifierConfigData['modifierHolderClass'])
            ;
            $this->setModifierConfigActivationRequirements($modifierConfig, $modifierConfigData);

            $this->entityManager->persist($modifierConfig);
        }
        $this->entityManager->flush();
    }

    private function setModifierConfigActivationRequirements(ModifierConfig $modifierConfig, array $modifierConfigData): void
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
