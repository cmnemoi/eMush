<?php

namespace Mush\Game\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\TriumphConfig;
use Mush\Game\Repository\TriumphConfigRepository;

class TriumphConfigDataLoader extends ConfigDataLoader
{
    private TriumphConfigRepository $triumphConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        TriumphConfigRepository $triumphConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->triumphConfigRepository = $triumphConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (TriumphConfigData::$dataArray as $triumphConfigData) {
            $triumphConfig = $this->triumphConfigRepository->findOneBy(['name' => $triumphConfigData['name']]);

            if ($triumphConfig === null) {
                $triumphConfig = new TriumphConfig();
            }
            $triumphConfig
                ->setName($triumphConfigData['name'])
                ->setTriumph($triumphConfigData['triumph'])
                ->setIsAllCrew($triumphConfigData['is_all_crew'])
                ->setTeam($triumphConfigData['team']);

            $this->entityManager->persist($triumphConfig);
        }
        $this->entityManager->flush();
    }
}
