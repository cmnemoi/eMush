<?php

namespace Mush\Game\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Repository\GameConfigRepository;

class GameConfigService implements GameConfigServiceInterface
{
    private GameConfigRepository $repository;

    /**
     * GameConfigService constructor.
     */
    public function __construct(GameConfigRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getConfig(): GameConfig
    {
        return $this->repository->findOneByName('default');
    }

    public function getDifficultyConfig(): DifficultyConfig
    {
        return $this->getConfig()->getDifficultyConfig();
    }

    public function getTriumphConfig(): Collection
    {
        return $this->getConfig()->getTriumphConfig();
    }
}
