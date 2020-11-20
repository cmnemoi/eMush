<?php

namespace Mush\Item\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\ConsumableEffect;
use Mush\Item\Entity\Items\Drug;
use Mush\Item\Entity\Items\Fruit;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Entity\PlantEffect;
use Mush\Item\Repository\ConsumableEffectRepository;
use Mush\Item\Repository\PlantEffectRepository;

class ItemEffectService implements ItemEffectServiceInterface
{
    private ConsumableEffectRepository $consumableEffectRepository;
    private PlantEffectRepository $plantEffectRepository;
    private RandomServiceInterface $randomService;

    /**
     * ItemEffectService constructor.
     */
    public function __construct(
        ConsumableEffectRepository $consumableEffectRepository,
        PlantEffectRepository $plantEffectRepository,
        RandomServiceInterface $randomService
    ) {
        $this->consumableEffectRepository = $consumableEffectRepository;
        $this->plantEffectRepository = $plantEffectRepository;
        $this->randomService = $randomService;
    }

    public function getConsumableEffect(Ration $ration, Daedalus $daedalus): ConsumableEffect
    {
        $consumableEffect = $this->consumableEffectRepository
            ->findOneBy(['ration' => $ration, 'daedalus' => $daedalus])
        ;

        if (null === $consumableEffect) {
            $consumableEffect = new ConsumableEffect();

            $consumableEffect
                ->setDaedalus($daedalus)
                ->setRation($ration)
                ->setActionPoint(
                    $this->randomService->getSingleRandomElementFromProbaArray($ration->getActionPoints())
                )
                ->setMovementPoint(
                    $this->randomService->getSingleRandomElementFromProbaArray($ration->getMovementPoints())
                )
                ->setHealthPoint(
                    $this->randomService->getSingleRandomElementFromProbaArray($ration->getHealthPoints())
                )
                ->setMoralPoint(
                    $this->randomService->getSingleRandomElementFromProbaArray($ration->getMoralPoints())
                )
            ;

            if ($ration instanceof Fruit && count($ration->getFruitEffectsNumber()) > 0) {
                // if the ration is a fruit 0 to 4 effects should be dispatched among diseases, cures and extraEffects
                $effectsNumber = $this->randomService->getSingleRandomElementFromProbaArray(
                    $ration->getFruitEffectsNumber()
                );

                $diseaseNumberPossible = count($ration->getDiseasesName());
                $extraEffectNumberPossible = count($ration->getExtraEffects());
                $pickedEffects = $this->randomService->getRandomElements(
                    range(
                        1,
                        $diseaseNumberPossible * 2 + $extraEffectNumberPossible,
                        $effectsNumber
                    )
                );

                $cures = [];
                $diseasesChances = [];
                $diseasesDelayMin = [];
                $diseasesDelayLengh = [];
                $extraEffects = [];

                // @FIXME: effect is not used??? This doesn't really seems to work as expected
                foreach ($pickedEffects as $effect) {
                    if ($pickedEffects <= $diseaseNumberPossible) {
                        $cures[$this->randomService->getSingleRandomElementFromProbaArray($ration->getDiseasesName())] = $this->randomService->getSingleRandomElementFromProbaArray($ration->getDiseasesEffectChance());
                    } elseif ($pickedEffects >= $diseaseNumberPossible + $extraEffectNumberPossible) {
                        $pickedDiseases = $this->randomService
                            ->getSingleRandomElementFromProbaArray($ration->getDiseasesName());
                        $diseasesChances[$pickedDiseases] = $this->randomService
                            ->getSingleRandomElementFromProbaArray($ration->getDiseasesEffectChance());
                        $diseasesDelayMin[$pickedDiseases] = $this->randomService
                            ->getSingleRandomElementFromProbaArray($ration->getDiseasesDelayMin());
                        $diseasesDelayLengh[$pickedDiseases] = $this->randomService
                            ->getSingleRandomElementFromProbaArray($ration->getDiseasesDelayLengh());
                    } else {
                        $extraEffects = $this->randomService->getSingleRandomElementFromProbaArray($ration->getExtraEffects());
                    }
                }
                $consumableEffect
                    ->setCures($cures)
                    ->setDiseasesChance($pickedDiseases)
                    ->setDiseasesDelayMin($diseasesDelayMin)
                    ->setDiseasesDelayLengh($diseasesDelayLengh)
                    ->setExtraEffects($extraEffects);
            } elseif ($ration instanceof Drug && count($ration->getDrugEffectsNumber()) > 0) {
                // if the ration is a drug 1 to 4 diseases are cured
                $curesNumber = $this->randomService->getSingleRandomElementFromProbaArray($ration->getDrugEffectsNumber());
                $consumableEffect
                    ->setCures($this->randomService->getRandomElements($ration->getCures(), $curesNumber));
            } else {
                $consumableEffect
                    ->setCures($ration->getCures())
                    ->setDiseasesChance($ration->getDiseasesChances())
                    ->setDiseasesDelayMin($ration->getDiseasesDelayMin())
                    ->setDiseasesDelayLengh($ration->getDiseasesDelayMin() + $ration->getDiseasesDelayLengh())
                    ->setExtraEffects($ration->getExtraEffects());
            }

            $this->consumableEffectRepository->persist($consumableEffect);
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
                    $this->randomService->getSingleRandomElementFromProbaArray(
                        $plant->getMaturationTime()
                    )
                )
                ->setOxygen($this->randomService->random($plant->getMinOxygen(), $plant->getMaxOxygen()))
            ;

            $this->plantEffectRepository->persist($plantEffect);
        }

        return $plantEffect;
    }
}
