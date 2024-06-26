<?php

declare(strict_types=1);

namespace Mush\Action\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Repository\ActionConfigRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Player\Enum\PlayerVariableEnum;

final class ActionDataLoader extends ConfigDataLoader
{
    private ActionConfigRepository $actionConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ActionConfigRepository $actionConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->actionConfigRepository = $actionConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (ActionData::$dataArray as $actionData) {
            /** @var null|ActionConfig $action */
            $action = $this->actionConfigRepository->findOneBy(['name' => $actionData['name']]);

            if ($action === null) {
                $action = new ActionConfig();
            }

            $action
                ->setName($actionData['name'])
                ->setActionName($actionData['action_name'])
                ->setTypes($actionData['types'])
                ->setDisplayHolder($actionData['target'])
                ->setRange($actionData['scope']);
            $this->setActionVariables($action, $actionData);
            $this->setActionVisibilities($action, $actionData['visibilities']);

            $this->entityManager->persist($action);
        }

        $this->entityManager->flush();
    }

    private function setActionVariables(ActionConfig $action, array $actionData): void
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

    private function setActionVisibilities(ActionConfig $action, array $visibilityData): void
    {
        foreach ($visibilityData as $visibilityType => $visibility) {
            $action->setVisibility($visibilityType, $visibility);
        }
    }
}
