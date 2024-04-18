<?php

namespace Mush\Game\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\TitleConfig;
use Mush\Game\Repository\TitleConfigRepository;

class TitleConfigDataLoader extends ConfigDataLoader
{
    private TitleConfigRepository $titleConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        TitleConfigRepository $titleConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->titleConfigRepository = $titleConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (TitleConfigData::$dataArray as $titleConfigData) {
            $titleConfig = $this->titleConfigRepository->findOneBy(['name' => $titleConfigData['name']]);

            if ($titleConfig === null) {
                $titleConfig = new TitleConfig();
            }
            $titleConfig
                ->setName($titleConfigData['name'])
                ->setPriority($titleConfigData['priority']);

            $this->entityManager->persist($titleConfig);
        }
        $this->entityManager->flush();
    }
}
