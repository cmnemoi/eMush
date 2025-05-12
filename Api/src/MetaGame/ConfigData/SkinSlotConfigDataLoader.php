<?php

declare(strict_types=1);

namespace Mush\MetaGame\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\MetaGame\Entity\Skin\SkinSlotConfig;

final class SkinSlotConfigDataLoader extends ConfigDataLoader
{
    private EntityRepository $skinSlotConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($entityManager);

        $this->skinSlotConfigRepository = $entityManager->getRepository(SkinSlotConfig::class);
    }

    public function loadConfigsData(): void
    {
        foreach (SkinSlotConfigData::$dataArray as $skinSlotData) {
            $skinSlotConfig = $this->skinSlotConfigRepository->findOneBy(['name' => $skinSlotData['name']]);

            if ($skinSlotConfig === null) {
                $skinSlotConfig = new SkinSlotConfig();
            } elseif (!$skinSlotConfig instanceof SkinSlotConfig) {
                $this->entityManager->remove($skinSlotConfig);
                $skinSlotConfig = new SkinSlotConfig();
            }

            $skinSlotConfig
                ->setName($skinSlotData['name'])
                ->setSkinableName($skinSlotData['skinableName'])
                ->setSkinableClass($skinSlotData['skinableClass'])
                ->setPriority($skinSlotData['priority']);

            $this->entityManager->persist($skinSlotConfig);
        }

        $this->entityManager->flush();
    }
}
