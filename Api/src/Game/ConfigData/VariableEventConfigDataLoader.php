<?php

namespace Mush\Game\Service\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Repository\EventConfigRepository;

class VariableEventConfigDataLoader extends ConfigDataLoader
{
    private EntityManagerInterface $entityManager;
    private EventConfigRepository $variableEventConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventConfigRepository $variableEventConfigRepository)
    {
        $this->entityManager = $entityManager;
        $this->variableEventConfigRepository = $variableEventConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (VariableEventConfigData::$dataArray as $variableEventConfigData) {
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
            ;
            $variableEventConfig->buildName();

            $this->entityManager->persist($variableEventConfig);
        }

        $this->entityManager->flush();
    }
}
