<?php

namespace Mush\Action\Service\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\Action;
use Mush\Action\Repository\ActionRepository;
use Mush\Game\Service\ConfigData\ConfigDataLoader;

class ActionDataLoader extends ConfigDataLoader
{
    private EntityManagerInterface $entityManager;
    private ActionRepository $actionRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ActionRepository $actionRepository
    ) {
        $this->entityManager = $entityManager;
        $this->actionRepository = $actionRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (ActionData::$dataArray as $actionData) {
            $action = $this->actionRepository->findOneBy(['name' => $actionData['name']]);

            if ($action !== null) {
                continue;
            }

            $action = new Action();

            $action
                ->setName($actionData['name'])
                ->setActionName($actionData['action_name'])
                ->setTypes($actionData['types'])
                ->setTarget($actionData['target'])
                ->setScope($actionData['scope'])
            ;
            $this->setActionVisibility($action, $actionData['visibilities']);
            $this->setActionVariables($action, $actionData);

            $this->entityManager->persist($action);
        }

        $this->entityManager->flush();
    }

    private function setActionVisibility(Action $action, array $visibilityData): void
    {
        foreach ($visibilityData as $visibilityType => $visibility) {
            $action->setVisibility($visibilityType, $visibility);
        }
    }

    private function setActionVariables(Action $action, array $actionData): void
    {
        $this->setActionActionCost($action, $actionData['actionPoint']);
        $this->setActionMovementCost($action, $actionData['movementPoint']);
        $this->setActionMoralCost($action, $actionData['moralPoint']);
        $this->setActionCriticalRate($action, $actionData['percentageCritical']);
        $this->setActionDirtyRate($action, $actionData['percentageDirtiness']);
        $this->setActionInjuryRate($action, $actionData['percentageInjury']);
        $this->setActionSuccessRate($action, $actionData['percentageSuccess']);
    }

    private function setActionActionCost(Action $action, array $actionCostData): void
    {
        $action->setActionCost($actionCostData['value']);
    }

    private function setActionCriticalRate(Action $action, array $criticalRateData): void
    {
        $action->setCriticalRate($criticalRateData['value']);
    }

    private function setActionDirtyRate(Action $action, array $dirtinessRateData): void
    {
        $isSuperDirty = $dirtinessRateData['min_value'] !== 0;
        $action->setDirtyRate($dirtinessRateData['value'], $isSuperDirty);
    }

    private function setActionInjuryRate(Action $action, array $injuryRateData): void
    {
        $action->setInjuryRate($injuryRateData['value']);
    }

    private function setActionMoralCost(Action $action, array $moralCostData): void
    {
        $action->setMoralCost($moralCostData['value']);
    }

    private function setActionMovementCost(Action $action, array $movementCostData): void
    {
        $action->setMovementCost($movementCostData['value']);
    }

    private function setActionSuccessRate(Action $action, array $successRateData): void
    {
        $action->setSuccessRate($successRateData['value']);
    }
}
