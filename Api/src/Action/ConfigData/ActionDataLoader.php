<?php

declare(strict_types=1);

namespace Mush\Action\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Repository\ActionRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Player\Enum\PlayerVariableEnum;

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
                ->setScope($actionData['scope']);
            $this->setActionVariables($action, $actionData);
            $this->setActionVisibilities($action, $actionData['visibilities']);

            $this->entityManager->persist($action);
        }

        $this->entityManager->flush();
    }

    private function setActionVariables(Action $action, array $actionData): void
    {
        $gameVariables = $action->getGameVariables();
        $gameVariables->setValuesByName($actionData['percentageInjury'], ActionVariableEnum::PERCENTAGE_INJURY);
        $gameVariables->setValuesByName($actionData['percentageSuccess'], ActionVariableEnum::PERCENTAGE_SUCCESS);
        $gameVariables->setValuesByName($actionData['percentageCritical'], ActionVariableEnum::PERCENTAGE_CRITICAL);
        $gameVariables->setValuesByName($actionData['outputQuantity'], ActionVariableEnum::OUTPUT_QUANTITY);

        $gameVariables->setValuesByName($actionData['actionPoint'], PlayerVariableEnum::ACTION_POINT);
        $gameVariables->setValuesByName($actionData['moralPoint'], PlayerVariableEnum::MORAL_POINT);
        $gameVariables->setValuesByName($actionData['movementPoint'], PlayerVariableEnum::MOVEMENT_POINT);

        $gameVariables->setValuesByName($actionData['percentageDirtiness'], ActionVariableEnum::PERCENTAGE_DIRTINESS);
        if ($actionData['percentageDirtiness']['min_value'] >= 100) {
            $action->makeSuperDirty();
        }
    }

    private function setActionVisibilities(Action $action, array $visibilityData): void
    {
        foreach ($visibilityData as $visibilityType => $visibility) {
            $action->setVisibility($visibilityType, $visibility);
        }
    }
}
