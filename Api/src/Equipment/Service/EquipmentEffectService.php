<?php

namespace Mush\Equipment\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Repository\ConsumableEffectRepository;
use Mush\Equipment\Repository\PlantEffectRepository;
use Mush\Game\Service\RandomServiceInterface;

class EquipmentEffectService implements EquipmentEffectServiceInterface
{
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;
    private ConsumableEffectRepository $consumableEffectRepository;
    private PlantEffectRepository $plantEffectRepository;
    private RandomServiceInterface $randomService;

    public function __construct(
        ConsumableDiseaseServiceInterface $consumableDiseaseService,
        ConsumableEffectRepository $consumableEffectRepository,
        PlantEffectRepository $plantEffectRepository,
        RandomServiceInterface $randomService
    ) {
        $this->consumableDiseaseService = $consumableDiseaseService;
        $this->consumableEffectRepository = $consumableEffectRepository;
        $this->plantEffectRepository = $plantEffectRepository;
        $this->randomService = $randomService;
    }

    public function getConsumableEffect(string $name, Ration $ration, Daedalus $daedalus): ConsumableEffect
    {
        $consumableEffect = $this->consumableEffectRepository
            ->findOneBy(['ration' => $ration, 'daedalus' => $daedalus])
        ;

        if ($consumableEffect === null) {
            $consumableEffect = $this->createConsumableEffect($name, $daedalus, $ration);
        }

        return $consumableEffect;
    }

    public function getPlantEffect(Plant $plant, Daedalus $daedalus): PlantEffect
    {
        $plantEffect = $this->plantEffectRepository
            ->findOneBy(['plant' => $plant, 'daedalus' => $daedalus])
        ;

        if (null === $plantEffect) {
            $plantEffect = new PlantEffect();
            $plantEffect
                ->setDaedalus($daedalus)
                ->setPlant($plant)
                ->setMaturationTime(
                    intval($this->randomService->getSingleRandomElementFromProbaArray(
                        $plant->getMaturationTime()
                    ))
                )
                ->setOxygen(intval($this->randomService->getSingleRandomElementFromProbaArray($plant->getOxygen())))
            ;

            $this->plantEffectRepository->persist($plantEffect);
        }

        return $plantEffect;
    }

    private function createConsumableEffect(string $name, Daedalus $daedalus, Ration $ration): ConsumableEffect
    {
        $consumableEffect = new ConsumableEffect();

        $consumableEffect
            ->setDaedalus($daedalus)
            ->setRation($ration)
            ->setActionPoint(
                intval($this->randomService->getSingleRandomElementFromProbaArray($ration->getActionPoints()))
            )
            ->setMovementPoint(
                intval($this->randomService->getSingleRandomElementFromProbaArray($ration->getMovementPoints()))
            )
            ->setHealthPoint(
                intval($this->randomService->getSingleRandomElementFromProbaArray($ration->getHealthPoints()))
            )
            ->setMoralPoint(
                intval($this->randomService->getSingleRandomElementFromProbaArray($ration->getMoralPoints()))
            )
            ->setSatiety($ration->getSatiety())
        ;

        $this->consumableEffectRepository->persist($consumableEffect);

        $this->consumableDiseaseService->createConsumableDiseases($name, $consumableEffect);

        return $consumableEffect;
    }
}
