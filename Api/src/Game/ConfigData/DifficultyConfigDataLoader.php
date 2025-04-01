<?php

namespace Mush\Game\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Repository\DifficultyConfigRepository;

class DifficultyConfigDataLoader extends ConfigDataLoader
{
    private DifficultyConfigRepository $difficultyConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DifficultyConfigRepository $difficultyConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->difficultyConfigRepository = $difficultyConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (DifficultyConfigData::getAll() as $difficultyConfigDto) {
            /** @var DifficultyConfig $difficultyConfig */
            $difficultyConfig = $this->difficultyConfigRepository->findOneBy(['name' => $difficultyConfigDto->name]);

            if ($difficultyConfig === null) {
                $difficultyConfig = DifficultyConfig::fromDto($difficultyConfigDto);
            } else {
                $difficultyConfig->updateFromDto($difficultyConfigDto);
            }

            $this->entityManager->persist($difficultyConfig);
        }
        $this->entityManager->flush();
    }
}
