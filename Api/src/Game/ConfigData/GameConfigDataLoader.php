<?php

namespace Mush\Game\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Repository\GameConfigRepository;

class GameConfigDataLoader extends ConfigDataLoader
{
    private EntityManagerInterface $entityManager;
    private GameConfigRepository $gameConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameConfigRepository $gameConfigRepository)
    {
        $this->entityManager = $entityManager;
        $this->gameConfigRepository = $gameConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (GameConfigData::$dataArray as $gameConfigData) {
            $gameConfig = $this->gameConfigRepository->findOneBy(['name' => $gameConfigData['name']]);

            if ($gameConfig === null) {
                $gameConfig = new GameConfig();
                $gameConfig->setName($gameConfigData['name']);

                $this->entityManager->persist($gameConfig);
            }
        }
        $this->entityManager->flush();
    }
}
