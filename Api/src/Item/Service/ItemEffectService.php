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
                $effectsNumber = current($this->randomService->getSingleRandomElementFromProbaArray(
                    $ration->getEffectsNumber()
                ));

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
                $curesNumber   =$pickedEffects->filter(fn (int $idEffect) => $idEffect<=$diseaseNumberPossible)->count();
                $extraEffectNumber=$pickedEffects->filter(fn (int $idEffect) => $idEffect>2*$diseaseNumberPossible)->count();
                $diseaseNumber=$diseaseNumberPossible * 2 + $extraEffectNumberPossible-$curesNumber-$extraEffectNumber;


                if($curesNumber>0){
		                //Get the names of cures among the list possible
		                //For the cures append the name of the disease as key and the probability to cure as value (randomly picked)
		                $curesNames = $this->randomService->getRandomElementsFromProbaArray($ration->getDiseasesNames(),$curesNumber);
		                $cures=[]
		                foreach ($curesNames as $cureName) {
		                	$cures[$cureName]=$this->randomService->getSingleRandomElementFromProbaArray($ration->getDiseaseEffectChance());
		                };
                }
                
                
                if($diseasesNumber>0){
	                //Get the names of diseases among the list possible
	                //For the diseases append the name of the disease as key and the probability to get sick as value in $diseasesChances
	                //append the name of the disease as key and the minimum delay before effect in $diseasesDelayMin
	                //append the name of the disease as key and the range of delay before effect in $diseasesDelayLengh
	                $diseasesNames = $this->randomService->getRandomElementsFromProbaArray($ration->getDiseasesNames(),$diseasesNumber);
	                $diseasesChances = [];
	                $diseasesDelayMin = [];
	                $diseasesDelayLengh = [];
	                foreach ($diseasesNames as $diseaseName) {
	                	$diseasesChances[$diseaseName]=$this->randomService->getSingleRandomElementFromProbaArray($ration->getDiseaseEffectChance());
	                	$diseasesDelayMin[$diseaseName] = $this->randomService->getSingleRandomElementFromProbaArray($ration->getDiseaseDelayMin());
	                   $diseasesDelayLengh[$diseaseName] = $this->randomService->getSingleRandomElementFromProbaArray($ration->getDiseaseDelayLengh());
	                };
	             }
	             
	             //@TODO fruit have only 1 possible extra effect. If we change the, this part needs to be changed
	             if($extraEffectNumber>0){
                    $extraEffects = $ration->getExtraEffects();
                }

                $consumableEffect
                    ->setCures($cures)
                    ->setDiseasesChance($pickedDiseases)
                    ->setDiseasesDelayMin($diseasesDelayMin)
                    ->setDiseasesDelayLengh($diseasesDelayLengh)
                    ->setExtraEffects($extraEffects);
                    
            } elseif ($ration instanceof Drug && count($ration->getDrugEffectsNumber()) > 0) {
                // if the ration is a drug 1 to 4 diseases are cured with 100% chances
                $curesNumber = $this->randomService->getSingleRandomElementFromProbaArray($ration->getDrugEffectsNumber());
                $consumableEffect
                    ->setCures(array_fill_keys($this->randomService->getRandomElements($ration->getCures(), $curesNumber)),100);
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
