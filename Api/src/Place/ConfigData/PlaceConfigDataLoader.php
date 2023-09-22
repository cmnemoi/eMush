<?php

namespace Mush\Place\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Repository\PlaceConfigRepository;

class PlaceConfigDataLoader extends ConfigDataLoader
{
    private PlaceConfigRepository $placeConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PlaceConfigRepository $placeConfigRepository)
    {
        parent::__construct($entityManager);
        $this->placeConfigRepository = $placeConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (PlaceConfigData::$dataArray as $placeConfigData) {
            $placeConfig = $this->placeConfigRepository->findOneBy(['name' => $placeConfigData['name']]);

            if ($placeConfig === null) {
                $placeConfig = new PlaceConfig();
            }

            $placeConfig
                ->setName($placeConfigData['name'])
                ->setPlaceName($placeConfigData['placeName'])
                ->setType($placeConfigData['type'])
                ->setDoors($placeConfigData['doors'])
                ->setItems($placeConfigData['items'])
                ->setEquipments($placeConfigData['equipments'])
            ;

            $this->entityManager->persist($placeConfig);
        }
        $this->entityManager->flush();
    }
}
