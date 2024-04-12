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
        foreach (EventConfigData::$dataArray as $variableEventConfigData) {
            if ($variableEventConfigData['type'] !== 'variable_event_config') {
                continue;
            }

            $variableEventConfig = $this->variableEventConfigRepository->findOneBy(['name' => $variableEventConfigData['name']]);

            if ($variableEventConfig !== null) {
                continue;
            }

            $variableEventConfig = new VariableEventConfig();
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
