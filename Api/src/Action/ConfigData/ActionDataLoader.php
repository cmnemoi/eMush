<?php

declare(strict_types=1);

namespace Mush\Action\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Repository\ActionConfigRepository;
use Mush\Game\ConfigData\ConfigDataLoader;

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
                $action = ActionConfig::fromConfigData($actionData);
            } else {
                $action->updateFromConfigData($actionData);
            }

            $this->entityManager->persist($action);
        }

        $this->entityManager->flush();
    }
}
