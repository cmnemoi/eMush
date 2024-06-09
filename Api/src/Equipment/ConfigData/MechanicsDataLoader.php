<?php

namespace Mush\Equipment\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Repository\ActionConfigRepository;
use Mush\Equipment\Entity\EquipmentMechanic as Mechanics;
use Mush\Equipment\Repository\MechanicsRepository;
use Mush\Game\ConfigData\ConfigDataLoader;

abstract class MechanicsDataLoader extends ConfigDataLoader
{
    protected MechanicsRepository $mechanicsRepository;
    protected ActionConfigRepository $actionConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        MechanicsRepository $mechanicsRepository,
        ActionConfigRepository $actionConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->mechanicsRepository = $mechanicsRepository;
        $this->actionConfigRepository = $actionConfigRepository;
    }

    abstract public function loadConfigsData(): void;

    protected function setMechanicsActions(Mechanics $mechanics, array $mechanicsData): void
    {
        $actions = [];
        foreach ($mechanicsData['actions'] as $actionName) {
            /** @var ActionConfig $action */
            $action = $this->actionConfigRepository->findOneBy(['name' => $actionName]);
            if ($action === null) {
                throw new \Exception('ActionConfig not found: ' . $actionName);
            }
            $actions[] = $action;
        }
        $mechanics->setActions(new ArrayCollection($actions));
    }
}
