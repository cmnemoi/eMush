<?php

namespace Mush\Game\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Repository\GameConfigRepository;

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

    public function getConfigByName(string $name): GameConfig
    {
        return $this->repository->findOneByName($name);
    }
}
