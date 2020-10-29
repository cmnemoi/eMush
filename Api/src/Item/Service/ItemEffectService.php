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
                ->setHealthPoint(
                    $this->randomService->random(
                        $ration->getMinHealthPoint(),
                        $ration->getMaxHealthPoint()
                    )
                )
                ->setMoralPoint(
                    $this->randomService->random(
                        $ration->getMinMoralPoint(),
                        $ration->getMaxMoralPoint()
                    )
                )
                ->setActionPoint(
                    $this->randomService->random(
                        $ration->getMinActionPoint(),
                        $ration->getMaxActionPoint()
                    )
                )
                ->setMovementPoint(
                    $this->randomService->random(
                        $ration->getMinMovementPoint(),
                        $ration->getMaxMovementPoint()
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
