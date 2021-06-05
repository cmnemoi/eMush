<?php

namespace Mush\Disease\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\ConsumableDisease;
use Mush\Disease\Entity\ConsumableDiseaseCharacteristic;
use Mush\Disease\Entity\ConsumableDiseaseConfig;
use Mush\Disease\Repository\ConsumableDiseaseConfigRepository;
use Mush\Disease\Repository\ConsumableDiseaseRepository;
use Mush\Game\Service\RandomServiceInterface;

class ConsumableDiseaseService implements ConsumableDiseaseServiceInterface
{
    private ConsumableDiseaseRepository $consumableDiseaseRepository;
    private ConsumableDiseaseConfigRepository $consumableDiseaseConfigRepository;
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;

    public function __construct(
        ConsumableDiseaseRepository $consumableDiseaseRepository,
        ConsumableDiseaseConfigRepository $consumableDiseaseConfigRepository,
        EntityManagerInterface $entityManager,
        RandomServiceInterface $randomService
    ) {
        $this->consumableDiseaseRepository = $consumableDiseaseRepository;
        $this->consumableDiseaseConfigRepository = $consumableDiseaseConfigRepository;
        $this->entityManager = $entityManager;
        $this->randomService = $randomService;
    }

    public function findConsumableDiseases(string $name, Daedalus $daedalus): ?ConsumableDisease
    {
        $consumableDisease = $this->consumableDiseaseRepository->findOneBy(
            ['name' => $name, 'daedalus' => $daedalus]
        );

        if ($consumableDisease === null) {
            $consumableDisease = $this->createConsumableDiseases($name, $daedalus);
        }

        return $consumableDisease;
    }

    public function createConsumableDiseases(string $name, Daedalus $daedalus): ?ConsumableDisease
    {
        /** @var ConsumableDiseaseConfig $consumableDiseaseConfig */
        $consumableDiseaseConfig = $this->consumableDiseaseConfigRepository->findOneBy(['name' => $name, 'gameConfig' => $daedalus->getGameConfig()]);
        if ($consumableDiseaseConfig === null) {
            return null;
        }

        $consumableDisease = new ConsumableDisease();
        $consumableDisease
            ->setDaedalus($daedalus)
            ->setName($name)
        ;
        $this->entityManager->persist($consumableDisease);

        $effectsNumber = 0;
        // if the ration is a fruit 0 to 4 effects should be dispatched among diseases, cures and extraEffects
        if (count($consumableDiseaseConfig->getFruitEffectsNumber()) > 0) {
            $effectsNumber = intval($this->randomService->getSingleRandomElementFromProbaArray(
                $consumableDiseaseConfig->getFruitEffectsNumber()
            ));
        }

        $diseaseNumberPossible = count($consumableDiseaseConfig->getDiseasesName());

        if ($effectsNumber > 0) {
            // We chose 0 to 4 unique id for the effects
            $pickedEffects = $this->randomService->getRandomElements(
                range(
                    1,
                    $diseaseNumberPossible,
                    $effectsNumber
                )
            );

            //To be changed to include cures later on
            $diseasesNumber = count($pickedEffects);
            if ($diseasesNumber > 0) {
                $diseasesNames = $this->randomService->getRandomElementsFromProbaArray($consumableDiseaseConfig->getDiseasesName(), $diseasesNumber);
                foreach ($diseasesNames as $diseaseName) {
                    $diseaseCharacteristic = $this->createDiseaseCharacteristic($diseaseName, $consumableDiseaseConfig);
                    $diseaseCharacteristic->setConsumableDisease($consumableDisease);
                    $this->entityManager->persist($diseaseCharacteristic);
                }
            }
        }

        /** @var ConsumableDiseaseCharacteristic $disease */
        foreach ($consumableDiseaseConfig->getDiseases() as $disease) {
            $consumableDiseaseCharacteristic = new ConsumableDiseaseCharacteristic();
            $consumableDiseaseCharacteristic
                ->setConsumableDisease($consumableDisease)
                ->setDisease($disease->getDisease())
                ->setRate($disease->getRate())
                ->setDelayMin($disease->getDelayMin())
                ->setDelayLength($disease->getDelayLength())
            ;

            $this->entityManager->persist($consumableDiseaseCharacteristic);
        }

        $this->entityManager->flush();

        return $consumableDisease;
    }

    private function createDiseaseCharacteristic(string $diseaseName, ConsumableDiseaseConfig $config): ConsumableDiseaseCharacteristic
    {
        $consumableDiseaseCharacteristic = new ConsumableDiseaseCharacteristic();
        $consumableDiseaseCharacteristic
            ->setDisease($diseaseName)
            ->setRate((int) $this->randomService->getSingleRandomElementFromProbaArray($config->getDiseasesChances()))
        ;
        $delay = (int) $this->randomService->getSingleRandomElementFromProbaArray($config->getDiseasesDelayMin());

        if ($delay > 0) {
            $consumableDiseaseCharacteristic
                ->setDelayMin($delay)
                ->setDelayLength((int) $this->randomService->getSingleRandomElementFromProbaArray($config->getDiseasesDelayLength()));
        }

        return $consumableDiseaseCharacteristic;
    }
}
