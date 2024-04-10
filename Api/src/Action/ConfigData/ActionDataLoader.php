<?php

namespace Mush\Action\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\Action;
use Mush\Action\Repository\ActionRepository;
use Mush\Game\ConfigData\ConfigDataLoader;

final class ActionDataLoader extends ConfigDataLoader
{
    private ActionRepository $actionRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ActionRepository $actionRepository
    ) {
        parent::__construct($entityManager);
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
        $action->setActionCost($actionData['actionPoint']);
        $action->setCriticalRate($actionData['percentageCritical']);
        $action->setDirtyRate($actionData['percentageDirtiness']);
        $action->setInjuryRate($actionData['percentageInjury']);
        $action->setMoralCost($actionData['moralPoint']);
        $action->setMovementCost($actionData['movementPoint']);
        $action->setOutputQuantity($actionData['outputQuantity']);
        $action->setSuccessRate($actionData['percentageSuccess']);
    }

    private function setActionVisibilities(Action $action, array $visibilityData): void
    {
        foreach ($visibilityData as $visibilityType => $visibility) {
            $action->setVisibility($visibilityType, $visibility);
        }
    }
}
