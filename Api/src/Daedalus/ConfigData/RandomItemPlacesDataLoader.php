<?php

namespace Mush\Daedalus\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Daedalus\Repository\RandomItemPlacesRepository;
use Mush\Game\ConfigData\ConfigDataLoader;

class RandomItemPlacesDataLoader extends ConfigDataLoader
{
    private EntityManagerInterface $entityManager;
    private RandomItemPlacesRepository $randomItemPlacesRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        RandomItemPlacesRepository $randomItemPlacesRepository)
    {
        $this->entityManager = $entityManager;
        $this->randomItemPlacesRepository = $randomItemPlacesRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (RandomItemPlacesData::$dataArray as $randomItemPlacesData) {
            $randomItemPlaces = $this->randomItemPlacesRepository->findOneBy(['name' => $randomItemPlacesData['name']]);

            if ($randomItemPlaces !== null) {
                continue;
            }

            $randomItemPlaces = new RandomItemPlaces();
            $randomItemPlaces
                ->setName($randomItemPlacesData['name'])
                ->setItems($randomItemPlacesData['items'])
                ->setPlaces($randomItemPlacesData['places'])
            ;

            $this->entityManager->persist($randomItemPlaces);
        }
        $this->entityManager->flush();
    }
}
