<?php

namespace Mush\Game\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Repository\ActionRepository;
use Mush\Action\Service\ConfigData\ActionDataLoader;
use Mush\Game\Repository\DifficultyConfigRepository;
use Mush\Game\Repository\GameConfigRepository;
use Mush\Game\Repository\TriumphConfigRepository;
use Mush\Game\Service\ConfigData\ConfigDataLoader;
use Mush\Game\Service\ConfigData\DifficultyConfigDataLoader;
use Mush\Game\Service\ConfigData\GameConfigDataLoader;
use Mush\Game\Service\ConfigData\TriumphConfigDataLoader;

class ConfigDataLoaderService
{
    private EntityManagerInterface $entityManager;
    private ActionRepository $actionRepository;
    private DifficultyConfigRepository $difficultyConfigRepository;
    private GameConfigRepository $gameConfigRepository;
    private TriumphConfigRepository $triumphConfigRepository;

    private ArrayCollection $dataLoaders;

    public function __construct(EntityManagerInterface $entityManager,
                                ActionRepository $actionRepository,
                                DifficultyConfigRepository $difficultyConfigRepository,
                                GameConfigRepository $gameConfigRepository,
                                TriumphConfigRepository $triumphConfigRepository
    ) {
        /** @var ConfigDataLoader $actionDataLoader */
        $actionDataLoader = new ActionDataLoader($entityManager, $actionRepository);
        /** @var ConfigDataLoader $difficultyConfigDataLoader */
        $difficultyConfigDataLoader = new DifficultyConfigDataLoader($entityManager, $difficultyConfigRepository);
        /** @var ConfigDataLoader $gameConfigDataLoader */
        $gameConfigDataLoader = new GameConfigDataLoader($entityManager, $gameConfigRepository);
        /** @var ConfigDataLoader $triumphConfigDataLoader */
        $triumphConfigDataLoader = new TriumphConfigDataLoader($entityManager, $gameConfigRepository, $triumphConfigRepository);

        /** @var ArrayCollection<int, ConfigDataLoader> $dataLoaders */
        $dataLoaders = new ArrayCollection(
            [
                $actionDataLoader,
                $difficultyConfigDataLoader,
                $gameConfigDataLoader,
                $triumphConfigDataLoader,
            ]
        );
        $this->setDataLoaders($dataLoaders);
    }

    public function loadAllConfigsData(): void
    {
        /** @var ConfigDataLoader $dataLoader */
        foreach ($this->dataLoaders as $dataLoader) {
            $dataLoader->loadConfigsData();
        }
    }

    /** @psalm-param ArrayCollection<int, ConfigDataLoader> $dataLoaders **/
    private function setDataLoaders(ArrayCollection $dataLoaders): void
    {
        $this->dataLoaders = $dataLoaders;
    }
}
