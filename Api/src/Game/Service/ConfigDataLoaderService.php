<?php

namespace Mush\Game\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Repository\GameConfigRepository;
use Mush\Game\Repository\TriumphConfigRepository;
use Mush\Game\Service\ConfigData\ConfigDataLoader;
use Mush\Game\Service\ConfigData\GameConfigDataLoader;
use Mush\Game\Service\ConfigData\TriumphConfigDataLoader;

class ConfigDataLoaderService
{
    private ArrayCollection $dataLoaders;
    private EntityManagerInterface $entityManager;
    private GameConfigRepository $gameConfigRepository;
    private TriumphConfigRepository $triumphConfigRepository;

    public function __construct(EntityManagerInterface $entityManager,
                                GameConfigRepository $gameConfigRepository,
                                TriumphConfigRepository $triumphConfigRepository
    ) {
        $triumphConfigDataLoader = new TriumphConfigDataLoader($entityManager, $gameConfigRepository, $triumphConfigRepository);
        $gameConfigDataLoader = new GameConfigDataLoader($entityManager, $gameConfigRepository);
        $this->setDataLoaders(new ArrayCollection(
            [
                $triumphConfigDataLoader,
                $gameConfigDataLoader,
            ]
        ));
    }

    public function loadData(): void
    {
        /** @var ConfigDataLoader $dataLoader */
        foreach ($this->dataLoaders as $dataLoader) {
            $dataLoader->loadConfigData();
        }
    }

    private function setDataLoaders(ArrayCollection $dataLoaders): void
    {
        $this->dataLoaders = $dataLoaders;
    }
}
