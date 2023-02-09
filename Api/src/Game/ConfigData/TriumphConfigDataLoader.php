<?php

namespace Mush\Game\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\TriumphConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Repository\GameConfigRepository;
use Mush\Game\Repository\TriumphConfigRepository;

class TriumphConfigDataLoader extends ConfigDataLoader
{
    private EntityManagerInterface $entityManager;
    private GameConfigRepository $gameConfigRepository;
    private TriumphConfigRepository $triumphConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameConfigRepository $gameConfigRepository,
        TriumphConfigRepository $triumphConfigRepository)
    {
        $this->entityManager = $entityManager;
        $this->gameConfigRepository = $gameConfigRepository;
        $this->triumphConfigRepository = $triumphConfigRepository;
    }

    public function loadConfigsData(): void
    {
        /** @var GameConfig $defaultGameConfig */
        $defaultGameConfig = $this->gameConfigRepository->findOneBy(['name' => 'default']);
        if ($defaultGameConfig == null) {
            throw new \Exception('Default game config not found');
        }

        foreach (TriumphConfigData::$dataArray as $triumphConfigData) {
            $triumphConfig = $this->triumphConfigRepository->findOneBy(['name' => $triumphConfigData['name']]);

            if ($triumphConfig === null) {
                $triumphConfig = new TriumphConfig();
                $triumphConfig
                    ->setName($triumphConfigData['name'])
                    ->setTriumph($triumphConfigData['triumph'])
                    ->setIsAllCrew($triumphConfigData['is_all_crew'])
                    ->setTeam($triumphConfigData['team'])
                ;

                $this->entityManager->persist($triumphConfig);
                if (!$defaultGameConfig->getTriumphConfig()->contains($triumphConfig)) {
                    $defaultGameConfig->addTriumphConfig($triumphConfig);
                }
            }
        }
        $this->entityManager->flush();
    }

}
