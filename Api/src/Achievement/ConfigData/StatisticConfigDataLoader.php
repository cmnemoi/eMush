<?php

declare(strict_types=1);

namespace Mush\Achievement\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Achievement\Entity\StatisticConfig;
use Mush\Achievement\Repository\StatisticConfigRepository;
use Mush\Game\ConfigData\ConfigDataLoader;

final class StatisticConfigDataLoader extends ConfigDataLoader
{
    private StatisticConfigRepository $statisticConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        StatisticConfigRepository $statisticConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->entityManager = $entityManager;
        $this->statisticConfigRepository = $statisticConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (StatisticConfigData::getAll() as $statisticConfigDto) {
            $statisticConfig = $this->statisticConfigRepository->findOneByNameOrNull($statisticConfigDto->name);

            if ($statisticConfig === null) {
                $statisticConfig = StatisticConfig::fromDto($statisticConfigDto);
            } else {
                $statisticConfig->updateFromDto($statisticConfigDto);
            }

            $this->entityManager->persist($statisticConfig);
        }
        $this->entityManager->flush();
    }
}
