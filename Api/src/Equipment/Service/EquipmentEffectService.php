<?php

namespace Mush\Equipment\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Repository\ConsumableEffectRepository;
use Mush\Equipment\Repository\PlantEffectRepository;
use Mush\Game\Service\RandomServiceInterface;

class EquipmentEffectService implements EquipmentEffectServiceInterface
{
    private ConsumableEffectRepository $consumableEffectRepository;
    private PlantEffectRepository $plantEffectRepository;
    private RandomServiceInterface $randomService;

    public function __construct(
        ConsumableEffectRepository $consumableEffectRepository,
        PlantEffectRepository $plantEffectRepository,
        RandomServiceInterface $randomService
    ) {
        $this->consumableEffectRepository = $consumableEffectRepository;
        $this->plantEffectRepository = $plantEffectRepository;
        $this->randomService = $randomService;
    }

    public function removeAllEffects(Daedalus $daedalus): void
    {
        $consumableEffects = $this->consumableEffectRepository->findBy(['daedalus' => $daedalus]);
        foreach ($consumableEffects as $effect) {
            $this->consumableEffectRepository->remove($effect);
        }

        $plantEffects = $this->plantEffectRepository->findBy(['daedalus' => $daedalus]);
        foreach ($plantEffects as $effect) {
            $this->plantEffectRepository->remove($effect);
        }
    }

    public function getConsumableEffect(Ration $ration, Daedalus $daedalus): ConsumableEffect
    {
        $consumableEffect = $this->consumableEffectRepository
            ->findOneBy(['ration' => $ration, 'daedalus' => $daedalus])
        ;
        $consumableEffect = $consumableEffect instanceof ConsumableEffect ? $consumableEffect : null;

        if ($consumableEffect === null) {
            $consumableEffect = $this->createConsumableEffect($daedalus, $ration);
            $this->consumableEffectRepository->persist($consumableEffect);
        }

        return $consumableEffect;
    }

    public function getPlantEffect(Plant $plant, Daedalus $daedalus): PlantEffect
    {
        $plantEffect = $this->plantEffectRepository
            ->findOneBy(['plant' => $plant, 'daedalus' => $daedalus])
        ;

        $plantEffect = $plantEffect instanceof PlantEffect ? $plantEffect : null;

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

    private function createConsumableEffect(Daedalus $daedalus, Ration $ration): ConsumableEffect
    {
        $consumableEffect = new ConsumableEffect();

        $consumableEffect
            ->setDaedalus($daedalus)
            ->setRation($ration)
            ->setSatiety($ration->getSatiety())
        ;

        if ($ration instanceof Drug) {
            $consumableEffect = $this->createDrugEffects($consumableEffect, $ration);
        } else {
            $consumableEffect
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
                );
        }

        return $consumableEffect;
    }

    private function createDrugEffects(ConsumableEffect $consumableEffect, Drug $drug): ConsumableEffect
    {
        // if the ration is a drug either action point gain or movement point gain
        if ($this->randomService->isSuccessful(50)) {
            $consumableEffect
                ->setActionPoint(
                    intval($this->randomService->getSingleRandomElementFromProbaArray($drug->getActionPoints()))
                );
        } else {
            $consumableEffect->setMovementPoint(
                intval($this->randomService->getSingleRandomElementFromProbaArray($drug->getMovementPoints()))
            );
        }

        // if the ration is a drug either health point gain or moral point gain
        if ($this->randomService->isSuccessful(50)) {
            $consumableEffect->setHealthPoint(
                intval($this->randomService->getSingleRandomElementFromProbaArray($drug->getHealthPoints()))
            );
        } else {
            $consumableEffect->setMoralPoint(
                intval($this->randomService->getSingleRandomElementFromProbaArray($drug->getMoralPoints()))
            );
        }

        return $consumableEffect;
    }
}
