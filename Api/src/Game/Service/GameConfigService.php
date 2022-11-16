<?php

namespace Mush\Game\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\Collection\TriumphConfigCollection;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Repository\GameConfigRepository;
use Mush\Game\Enum\GameConfigEnum;

class GameConfigService implements GameConfigServiceInterface
{
    private EntityManagerInterface $entityManager;

    private GameConfigRepository $repository;

    public function __construct(EntityManagerInterface $entityManager, GameConfigRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function persist(GameConfig $gameConfig): GameConfig
    {
        $this->entityManager->persist($gameConfig);
        $this->entityManager->flush();

        return $gameConfig;
    }

    public function getConfig(): GameConfig
    {
        return $this->repository->findOneByName(GameConfigEnum::FRENCH_DEFAULT);
    }

    public function getDifficultyConfig(): DifficultyConfig
    {
        return $this->getConfig()->getDifficultyConfig();
    }

    public function getTriumphConfig(): TriumphConfigCollection
    {
        return $this->getConfig()->getTriumphConfig();
    }
}
