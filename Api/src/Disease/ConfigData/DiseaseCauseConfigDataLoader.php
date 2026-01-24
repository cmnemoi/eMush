<?php

namespace Mush\Disease\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Repository\DiseaseCauseConfigRepository;
use Mush\Game\ConfigData\ConfigDataLoader;

class DiseaseCauseConfigDataLoader extends ConfigDataLoader
{
    private DiseaseCauseConfigRepository $diseaseCauseConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DiseaseCauseConfigRepository $diseaseCauseConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->diseaseCauseConfigRepository = $diseaseCauseConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (DiseaseCauseConfigData::getAll() as $diseaseCauseConfigDto) {
            $diseaseCauseConfig = $this->diseaseCauseConfigRepository->findOneBy(['name' => $diseaseCauseConfigDto->name . '_default']);

            if ($diseaseCauseConfig instanceof DiseaseCauseConfig) {
                $diseaseCauseConfig->updateFromDto($diseaseCauseConfigDto);
            } else {
                $diseaseCauseConfig = DiseaseCauseConfig::fromDto($diseaseCauseConfigDto);
            }

            $this->entityManager->persist($diseaseCauseConfig);
        }
        $this->entityManager->flush();
    }
}
