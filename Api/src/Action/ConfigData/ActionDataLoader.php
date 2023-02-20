<?php

namespace Mush\Action\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\Action;
use Mush\Action\Repository\ActionRepository;
use Mush\Game\ConfigData\ConfigDataLoader;

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

            if ($action === null) {
                $action = new Action();
            }

            $action
                ->setName($actionData['name'])
                ->setActionName($actionData['action_name'])
                ->setTypes($actionData['types'])
                ->setTarget($actionData['target'])
                ->setScope($actionData['scope'])
            ;
            $this->setActionVariables($action, $actionData);
            $this->setActionVisibilities($action, $actionData['visibilities']);

            $this->entityManager->persist($action);
        }

        $this->entityManager->flush();
    }

    private function setActionVariables(Action $action, array $actionData): void
    {
        $actionIsSuperDirty = $actionData['percentageDirtiness']['min_value'] !== 0;

        $action->setActionCost($actionData['actionPoint']['value']);
        $action->setCriticalRate($actionData['percentageCritical']['value']);
        $action->setDirtyRate($actionData['percentageDirtiness']['value'], $actionIsSuperDirty);
        $action->setInjuryRate($actionData['percentageInjury']['value']);
        $action->setMoralCost($actionData['moralPoint']['value']);
        $action->setMovementCost($actionData['movementPoint']['value']);
        $action->setSuccessRate($actionData['percentageSuccess']['value']);
    }

    private function setActionVisibilities(Action $action, array $visibilityData): void
    {
        foreach ($visibilityData as $visibilityType => $visibility) {
            $action->setVisibility($visibilityType, $visibility);
        }
    }
}
