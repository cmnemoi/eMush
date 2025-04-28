<?php

declare(strict_types=1);

namespace Mush\MetaGame\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\MetaGame\Entity\Skin\Skin;
use Mush\MetaGame\Entity\Skin\SkinSlotConfig;

final class SkinDataLoader extends ConfigDataLoader
{
    private EntityRepository $skinRepository;
    private EntityRepository $skinSlotConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($entityManager);
        $this->skinRepository = $entityManager->getRepository(Skin::class);
        $this->skinSlotConfigRepository = $entityManager->getRepository(SkinSlotConfig::class);
    }

    public function loadConfigsData(): void
    {
        foreach (SkinData::$dataArray as $skinData) {
            $skin = $this->skinRepository->findOneBy(['name' => $skinData['name']]);

            if ($skin === null) {
                $skin = new Skin();
            } elseif (!$skin instanceof Skin) {
                $this->entityManager->remove($skin);
                $skin = new Skin();
            }

            $skin->setName($skinData['name']);

            $skinSlotConfig = $this->skinSlotConfigRepository->findOneBy(['name' => $skinData['skinSlotConfig']]);

            if (!$skinSlotConfig instanceof SkinSlotConfig) {
                throw new \Exception('SkinSlotConfig config not found: ' . $skinData['skinSlotConfig']);
            }

            $skin->setSkinSlotConfig($skinSlotConfig);

            $this->entityManager->persist($skin);
        }

        $this->entityManager->flush();
    }
}
