<?php

namespace Mush\Equipment\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Entity\Mechanics\Fruit;
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

    /**
     * EquipmentEffectService constructor.
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
                ->setSatiety($ration->getSatiety())
            ;

            if ($ration instanceof Fruit && count($ration->getFruitEffectsNumber()) > 0) {
                // if the ration is a fruit 0 to 4 effects should be dispatched among diseases, cures and extraEffects
                $effectsNumber = $this->randomService->getSingleRandomElementFromProbaArray(
                    $ration->getFruitEffectsNumber()
                );

                $diseaseNumberPossible = count($ration->getDiseasesName());
                $extraEffectNumberPossible = count($ration->getExtraEffects());

                // We chose 0 to 4 unique id for the effects
                $pickedEffects = $this->randomService->getRandomElements(
                    range(
                        1,
                        $diseaseNumberPossible * 2 + $extraEffectNumberPossible,
                        $effectsNumber
                    )
                );

                //Get the number of cures, disease and special effect from the id
                $curesNumber = count(array_filter($pickedEffects, function ($idEffect) use ($diseaseNumberPossible) {
                    return $idEffect <= $diseaseNumberPossible;
                }));
                $extraEffectNumber = count(array_filter($pickedEffects, function ($idEffect) use ($diseaseNumberPossible) {
                    return $idEffect > 2 * $diseaseNumberPossible;
                }));
                $diseasesNumber = $diseaseNumberPossible * 2 + $extraEffectNumberPossible - $curesNumber - $extraEffectNumber;

                if ($curesNumber > 0) {
                    //Get the names of cures among the list possible
                    //For the cures append the name of the disease as key and the probability to cure as value (randomly picked)
                    $curesNames = $this->randomService->getRandomElementsFromProbaArray($ration->getDiseasesName(), $curesNumber);
                    $cures = [];
                    foreach ($curesNames as $cureName) {
                        $cures[$cureName] = $this->randomService->getSingleRandomElementFromProbaArray($ration->getDiseasesEffectChance());
                    }
                }

                if ($diseasesNumber > 0) {
                    //Get the names of diseases among the list possible
                    //For the diseases append the name of the disease as key and the probability to get sick as value in $diseasesChances
                    //append the name of the disease as key and the minimum delay before effect in $diseasesDelayMin
                    //append the name of the disease as key and the range of delay before effect in $diseasesDelayLengh
                    $diseasesNames = $this->randomService->getRandomElementsFromProbaArray($ration->getDiseasesName(), $diseasesNumber);
                    $diseasesChances = [];
                    $diseasesDelayMin = [];
                    $diseasesDelayLength = [];
                    foreach ($diseasesNames as $diseaseName) {
                        $diseasesChances[$diseaseName] = $this->randomService->getSingleRandomElementFromProbaArray($ration->getDiseasesEffectChance());
                        $diseasesDelayMin[$diseaseName] = $this->randomService->getSingleRandomElementFromProbaArray($ration->getDiseasesDelayMin());
                        $diseasesDelayLength[$diseaseName] = $this->randomService->getSingleRandomElementFromProbaArray($ration->getDiseasesDelayLength());
                    }
                }

                //@TODO fruit have only 1 possible extra effect. If we change the, this part needs to be changed
                if ($extraEffectNumber > 0) {
                    $extraEffects = $ration->getExtraEffects();
                }

                $consumableEffect
                    ->setCures($cures)
                    ->setDiseasesChance($diseasesChances)
                    ->setDiseasesDelayMin($diseasesDelayMin)
                    ->setDiseasesDelayLength($diseasesDelayLength)
                    ->setExtraEffects($extraEffects);
            } elseif ($ration instanceof Drug && count($ration->getDrugEffectsNumber()) > 0) {
                // if the ration is a drug 1 to 4 diseases are cured with 100% chances
                $curesNumber = $this->randomService->getSingleRandomElementFromProbaArray($ration->getDrugEffectsNumber());
                $consumableEffect
                    ->setCures(array_fill_keys($this->randomService->getRandomElements($ration->getCures(), $curesNumber), 100));
            } else {
                $consumableEffect
                    ->setCures($ration->getCures())
                    ->setDiseasesChance($ration->getDiseasesChances())
                    ->setDiseasesDelayMin($ration->getDiseasesDelayMin())
                    ->setDiseasesDelayLength($ration->getDiseasesDelayMin() + $ration->getDiseasesDelayLength())
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
