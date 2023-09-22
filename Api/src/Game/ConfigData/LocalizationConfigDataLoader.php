<?php

namespace Mush\Game\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Repository\LocalizationConfigRepository;

class LocalizationConfigDataLoader extends ConfigDataLoader
{
    private LocalizationConfigRepository $localizationConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        LocalizationConfigRepository $localizationConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->localizationConfigRepository = $localizationConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (LocalizationConfigData::$dataArray as $localizationConfigData) {
            $localizationConfig = $this->localizationConfigRepository->findOneBy(['name' => $localizationConfigData['name']]);

            if ($localizationConfig === null) {
                $localizationConfig = new LocalizationConfig();
            }
            $localizationConfig
                ->setName($localizationConfigData['name'])
                ->setTimeZone($localizationConfigData['timeZone'])
                ->setLanguage($localizationConfigData['language'])
            ;

            $this->entityManager->persist($localizationConfig);
        }
        $this->entityManager->flush();
    }
}
