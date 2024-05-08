<?php

namespace Mush\Game\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Repository\EventConfigRepository;

class VariableEventConfigDataLoader extends ConfigDataLoader
{
    private EventConfigRepository $variableEventConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventConfigRepository $variableEventConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->variableEventConfigRepository = $variableEventConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (EventConfigData::$variableEventConfigData as $variableEventConfigData) {
            /** @var null|VariableEventConfig $variableEventConfig */
            $variableEventConfig = $this->variableEventConfigRepository->findOneBy(['name' => $variableEventConfigData['name']]);
            if ($variableEventConfig === null) {
                $variableEventConfig = new VariableEventConfig();
            }

            $variableEventConfig
                ->setQuantity($variableEventConfigData['quantity'])
                ->setTargetVariable($variableEventConfigData['targetVariable'])
                ->setVariableHolderClass($variableEventConfigData['variableHolderClass'])
                ->setEventName($variableEventConfigData['eventName'])
                ->setName($variableEventConfigData['name']);

            $this->entityManager->persist($variableEventConfig);
        }

        $this->entityManager->flush();
    }
}
