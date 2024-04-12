<?php

namespace Mush\Equipment\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\Action;
use Mush\Action\Repository\ActionRepository;
use Mush\Equipment\Entity\EquipmentMechanic as Mechanics;
use Mush\Equipment\Repository\MechanicsRepository;
use Mush\Game\ConfigData\ConfigDataLoader;

abstract class MechanicsDataLoader extends ConfigDataLoader
{
    protected MechanicsRepository $mechanicsRepository;
    protected ActionRepository $actionRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        MechanicsRepository $mechanicsRepository,
        ActionRepository $actionRepository
    ) {
        parent::__construct($entityManager);
        $this->mechanicsRepository = $mechanicsRepository;
        $this->actionRepository = $actionRepository;
    }

    abstract public function loadConfigsData(): void;

    protected function setMechanicsActions(Mechanics $mechanics, array $mechanicsData): void
    {
        $actions = [];
        foreach ($mechanicsData['actions'] as $actionName) {
            /** @var Action $action */
            $action = $this->actionRepository->findOneBy(['name' => $actionName]);
            if ($action === null) {
                throw new \Exception('Action not found: ' . $actionName);
            }
            $actions[] = $action;
        }
        $mechanics->setActions(new ArrayCollection($actions));
    }
}
