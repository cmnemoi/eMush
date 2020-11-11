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
     * @param ConsumableEffectRepository $consumableEffectRepository
     * @param PlantEffectRepository $plantEffectRepository
     * @param RandomServiceInterface $randomService
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

        if ($consumableEffect === null) {
            $consumableEffect = new ConsumableEffect();
            $consumableEffect
                ->setDaedalus($daedalus)
                ->setRation($ration)
                ->setActionPoint(current($this->randomService->getRandomElements($ration->getActionPoints())))
                ->setMovementPoint(current($this->randomService->getRandomElements($ration->getMovementPoints())))
                ->setHealthPoint(current($this->randomService->getRandomElements($ration->getHealthPoints())))
                ->setMoralPoint(current($this->randomService->getRandomElements($ration->getMoralPoints())))
                ->setCures($this->randomService->getRandomElements(
                    $ration->getCures(),
                    current($this->randomService->getRandomElements($ration->getCuresNumber()))
                    )
                )
                ->setDiseases($this->randomService->getRandomElements(
                    $ration->getDiseases(),
                    current($this->randomService->getRandomElements($ration->getDiseasesNumber()))
                    )
                )
            ;

            $this->consumableEffectRepository->persist($consumableEffect);
        }

        return $consumableEffect;
    }

    public function getPlantEffect(Plant $plant, Daedalus $daedalus): PlantEffect
    {
        $plantEffect = $this->plantEffectRepository
            ->findOneBy(['plant' => $plant, 'daedalus' => $daedalus])
        ;

        if ($plantEffect === null) {
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
