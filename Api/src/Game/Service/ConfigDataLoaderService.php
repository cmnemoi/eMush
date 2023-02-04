<?php

namespace Mush\Game\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Repository\GameConfigRepository;
use Mush\Game\Repository\TriumphConfigRepository;

class ConfigDataLoaderService
{
    private Collection $dataLoaders;
    private EntityManagerInterface $entityManager;
    private GameConfigRepository $gameConfigRepository;
    private TriumphConfigRepository $triumphConfigRepository;

    public function __construct(EntityManagerInterface $entityManager,
                                GameConfigRepository $gameConfigRepository,
                                TriumphConfigRepository $triumphConfigRepository
    ) {
        $this->addDataLoader(
            new TriumphConfigDataLoader(
                $entityManager,
                $gameConfigRepository,
                $triumphConfigRepository
            )
        );
    }

    public function loadData(): void
    {
        /** @var ConfigDataLoader $dataLoader */
        foreach ($this->dataLoaders as $dataLoader) {
            $dataLoader->loadConfigData();
        }
    }

    private function addDataLoader(ConfigDataLoader $dataLoader): void
    {
        $this->dataLoaders->add($dataLoader);
    }
}
