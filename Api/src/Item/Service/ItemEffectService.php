<?php

namespace Mush\Item\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\ConsumableEffect;
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
            
            

            // @TODO Add better statistics (non equiprobable number of effect for exemple)
            $consumableEffect
                ->setDaedalus($daedalus)
                ->setRation($ration)
                ->setActionPoint(current($this->randomService->getRandomElements($ration->getActionPoints())))
                ->setMovementPoint(current($this->randomService->getRandomElements($ration->getMovementPoints())))
                ->setHealthPoint(current($this->randomService->getRandomElements($ration->getHealthPoints())))
                ->setMoralPoint(current($this->randomService->getRandomElements($ration->getMoralPoints())))
                

                if($ration instanceof fruit && $ration->getEffectsNumber()->count()>0){    
                    // if the ration is a fruit 0 to 4 effects should be dispatched among diseases, cures and extraEffects       
                   $effectsNumber=current($this->randomService->getRandomElements($ration->getEffectsNumber()));
                          
                   $diseaseNumberPossible=count($ration->getDiseasesName());
                   $extraEffectNumberPossible=count($ration->getExtraEffects());
                   $picked_effects=$this->randomService->getRandomElements(
	                   range(1, count($diseaseNumberPossible*2+$extraEffectNumberPossible,
	                   $effectsNumber
	                   );
	                   
	                   
	                $cures=[]
	                $DiseasesChances=[]
	                $DiseasesDelayMin=[]
	                $DiseasesDelayLengh=[]
                   // @TODO implement the different proba for different diseases
                   foreach($picked_effects as $effectId){
                   	if($picked_effects<=$diseaseNumberPossible){
                   		$cures=
                   		
                   	} elseif($picked_effects>=$diseaseNumberPossible+$extraEffectNumberPossible){
                   		
                   	}else{
	                	
		                };
		          
                   $consumableEffect
                   	->setCures()
                   	->setDiseasesChances()
                   	->setDiseasesDelayMin()
                   	->setDiseasesDelayLengh()
                   	->setExtraEffects();
                	
                }elseif($ration instanceof drug  && $ration->getEffectsNumber()->count()>0) {
                    // if the ration is a drug 1 to 4 diseases are cured
                    $curesNumber=current($this->randomService->getRandomElements($ration->getEffectsNumber()));
                    $consumableEffect
		                ->setCures($this->randomService->getRandomElements($ration->getCures(),$curesNumber));
                }else{                	
                	$consumableEffect
		                ->setCures($ration->getCures())
		                ->setDiseasesChances($ration->getDiseasesChances())
		                ->setDiseasesDelayMin($ration->getDiseasesDelayMin())
		                ->setDiseasesDelayLengh($ration->getDiseasesDelayMin()+$ration->getDiseasesDelayLengh())
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
        
        // @TODO The number of maturation cycle possible is not discrete + non equiprobable
        if (null === $plantEffect) {
            $plantEffect = new PlantEffect();
            $plantEffect
                ->setDaedalus($daedalus)
                ->setPlant($plant)
                ->setMaturationTime(
                    $this->randomService->random(
                        $plant->getMinMaturationTime(),
                        $plant->getMaxMaturationTime()
                    )
                )
                ->setOxygen($this->randomService->random($plant->getMinOxygen(), $plant->getMaxOxygen()))
            ;

            $this->plantEffectRepository->persist($plantEffect);
        }

        return $plantEffect;
    }
}
