<?php

namespace Mush\Disease\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\ConsumableDiseaseConfig;
use Mush\Disease\Entity\ConsumableDisease;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Disease\Repository\ConsumableDiseaseRepository;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Service\RandomServiceInterface;

class ConsumableDiseaseService implements ConsumableDiseaseServiceInterface
{
    private ConsumableDiseaseRepository $consumableDiseaseRepository;
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;

    public function __construct(
        ConsumableDiseaseRepository $consumableDiseaseRepository,
        EntityManagerInterface $entityManager,
        RandomServiceInterface $randomService
    ) {
        $this->consumableDiseaseRepository = $consumableDiseaseRepository;
        $this->entityManager = $entityManager;
        $this->randomService = $randomService;
    }

    public function removeAllConsumableDisease(Daedalus $daedalus): void
    {
        $consumableEffects = $this->consumableDiseaseRepository->findBy(['daedalus' => $daedalus]);
        foreach ($consumableEffects as $effect) {
            $this->entityManager->remove($effect);
            $this->entityManager->flush();
        }
    }

    public function findConsumableDiseases(string $name, Daedalus $daedalus): ?ConsumableDisease
    {
        $consumableDisease = $this->consumableDiseaseRepository->findOneBy(
            ['name' => $name, 'daedalus' => $daedalus]
        );

        $consumableDisease = $consumableDisease instanceof ConsumableDisease ? $consumableDisease : null;

        if ($consumableDisease === null) {
            $consumableDisease = $this->createConsumableDiseases($name, $daedalus);
        }

        return $consumableDisease;
    }

    private function findConsumableDiseaseConfigByNameAndDaedalus(string $name, Daedalus $daedalus): ?ConsumableDiseaseConfig
    {
        $consumableDiseaseConfigs = $daedalus->getGameConfig()
            ->getConsumableDiseaseConfig()->filter(fn (ConsumableDiseaseConfig $consumableDiseaseConfig) => $consumableDiseaseConfig->getCauseName() === $name);

        if ($consumableDiseaseConfigs->count() === 0) {
            return null;
        }

        return $consumableDiseaseConfigs->first();
    }

    public function createConsumableDiseases(string $name, Daedalus $daedalus): ?ConsumableDisease
    {
        $consumableDiseaseConfig = $this->findConsumableDiseaseConfigByNameAndDaedalus($name, $daedalus);

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
        if (count($consumableDiseaseConfig->getEffectNumber()) > 0) {
            $effectsNumber = intval($this->randomService->getSingleRandomElementFromProbaCollection(
                $consumableDiseaseConfig->getEffectNumber()
            ));
        }

        $diseasesNumberPossible = count($consumableDiseaseConfig->getDiseasesName());
        $curesNumberPossible = count($consumableDiseaseConfig->getCuresName());

        if ($effectsNumber > 0) {
            // We chose 0 to 4 unique id for the effects
            $pickedEffects = $this->randomService->getRandomElements(
                range(
                    1,
                    $diseasesNumberPossible + $curesNumberPossible),
                $effectsNumber
            );

            // Get the number of cures, disease and special effect from the id
            $curesNumber = count(array_filter($pickedEffects, fn ($idEffect) => $idEffect <= $curesNumberPossible));

            $diseasesNumber = $effectsNumber - $curesNumber;

            if ($curesNumber > 0 && $curesNumberPossible > 0) {
                $this->createMedicinalEffectFromConfigForConsumableDisease($consumableDisease, $consumableDiseaseConfig, MedicalConditionTypeEnum::CURE, $curesNumber);
            }

            if ($diseasesNumber > 0 && $diseasesNumberPossible > 0) {
                $this->createMedicinalEffectFromConfigForConsumableDisease($consumableDisease, $consumableDiseaseConfig, MedicalConditionTypeEnum::DISEASE, $diseasesNumber);
            }
        }

        /** @var ConsumableDiseaseAttribute $disease */
        foreach ($consumableDiseaseConfig->getAttributes() as $disease) {
            $ConsumableDiseaseAttribute = new ConsumableDiseaseAttribute();
            $ConsumableDiseaseAttribute
                ->setConsumableDisease($consumableDisease)
                ->setDisease($disease->getDisease())
                ->setRate($disease->getRate())
                ->setDelayMin($disease->getDelayMin())
                ->setDelayLength($disease->getDelayLength())
            ;

            $this->entityManager->persist($ConsumableDiseaseAttribute);
        }

        $this->entityManager->flush();

        return $consumableDisease;
    }

    private function createMedicinalEffectFromConfigForConsumableDisease(
        ConsumableDisease $consumableDisease,
        ConsumableDiseaseConfig $consumableDiseaseConfig,
        string $type,
        int $number
    ): ConsumableDisease {
        $names = $type === MedicalConditionTypeEnum::DISEASE ? $consumableDiseaseConfig->getDiseasesName() : $consumableDiseaseConfig->getCuresName();
        $diseasesNames = $this->randomService->getRandomElementsFromProbaCollection(new ProbaCollection($names), $number);

        foreach ($diseasesNames as $diseaseName) {
            $diseaseCharacteristic = $this->createMedicalCharacteristic($diseaseName, $consumableDiseaseConfig, $type);
            $diseaseCharacteristic->setConsumableDisease($consumableDisease);
            $this->entityManager->persist($diseaseCharacteristic);
        }

        return $consumableDisease;
    }

    private function createMedicalCharacteristic(string $diseaseName, ConsumableDiseaseConfig $config, string $type): ConsumableDiseaseAttribute
    {
        $ConsumableDiseaseAttribute = new ConsumableDiseaseAttribute();

        $rates = $type === MedicalConditionTypeEnum::DISEASE ? $config->getDiseasesChances() : $config->getCuresChances();

        $ConsumableDiseaseAttribute
            ->setDisease($diseaseName)
            ->setType($type)
            ->setRate((int) $this->randomService->getSingleRandomElementFromProbaCollection($rates))
        ;

        if ($type === MedicalConditionTypeEnum::DISEASE) {
            $delay = (int) $this->randomService->getSingleRandomElementFromProbaCollection($config->getDiseasesDelayMin());

            if ($delay > 0) {
                $ConsumableDiseaseAttribute
                    ->setDelayMin($delay)
                    ->setDelayLength((int) $this->randomService->getSingleRandomElementFromProbaCollection($config->getDiseasesDelayLength()));
            }
        }

        return $ConsumableDiseaseAttribute;
    }
}
