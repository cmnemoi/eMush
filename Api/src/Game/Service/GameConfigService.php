<?php

namespace Mush\Game\Service;

use Mush\Game\Entity\GameConfig;
use Mush\Game\Repository\GameConfigRepository;
use Symfony\Component\Serializer\SerializerInterface;

class GameConfigService implements GameConfigServiceInterface
{
    private GameConfigRepository $repository;

    /**
     * GameConfigService constructor.
     * @param GameConfigRepository $repository
     */
    public function __construct(GameConfigRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getConfig(): GameConfig
    {
        return $this->repository->findOneByName('default');
    }
}
