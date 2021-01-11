<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class DaedalusNormalizer implements ContextAwareNormalizerInterface
{
    private CycleServiceInterface $cycleService;
    private GameConfig $gameConfig;

    public function __construct(
        CycleServiceInterface $cycleService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->cycleService = $cycleService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Daedalus;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Daedalus $daedalus */
        $daedalus = $object;

        return [
                'id' => $object->getId(),
                'cycle' => $object->getCycle(),
                'day' => $object->getDay(),
                'oxygen' => $object->getOxygen(),
                'fuel' => $object->getFuel(),
                'hull' => $object->getHull(),
                'shield' => $object->getShield(),
                'nextCycle' => $this->cycleService->getDateStartNextCycle($object)->format(\DateTime::ATOM),
                'cryogenizedPlayers' => $this->gameConfig->getCharactersConfig()->count() - $daedalus->getPlayers()->count(),
                'humanPlayerAlive' => $daedalus->getPlayers()->getHumanPlayer()->getPlayerAlive()->count(),
                'humanPlayerDead' => $daedalus->getPlayers()->getHumanPlayer()->getPlayerDead()->count(),
                'mushPlayerAlive' => $daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count(),
                'mushPlayerDead' => $daedalus->getPlayers()->getMushPlayer()->getPlayerDead()->count(),
            ];
    }

    public function minimap($daedalus): array
    {
        $minimap =[];
        foreach($daedalus->getRoom() as $room){
            $minimap[$room->getName()] = ['players' => $room->getPlayers()->count()];

            //@TODO add project fire detector, anomaly detector doors detectors and actopi protocol
        }

        return $minimap;
    }

    public function getAlerts($daedalus): array
    {
        $alerts = [];

        $fire=0;
        $brokenDoors=0;
        $brokenEquipments=0;

        foreach($daedalus->getRoom() as $room){
            if ($room->getStatusByName(StatusEnum::FIRE)){
                $fire=$fire+1;
            }
            $brokenDoors = $brokenDoors+$room->getEquipment()
                ->filter(fn (GameEquipment $equipment) => $equipment instanceof Door && $equipment->isBroken())->count();
            $brokenEquipments = $brokenEquipments+$room->getEquipment()
                ->filter(fn (GameEquipment $equipment) => $equipment->isPureEquipment() && $equipment->isBroken())->count();
        }

        if ($fire !==0){
            $alert =[
                "name" => $this->translator->trans('fire' . '.name', ['quantity' => $fire], 'alerts'),
                "description" => $this->translator->trans('fire' . '.description', [], 'alerts'),
            ];
            $alerts[] = $alert;
        }
        if ($brokenDoors !==0){
            $alert =[
                "name" => $this->translator->trans('brokenDoors' . '.name', ['quantity' => $brokenDoors], 'alerts'),
                "description" => $this->translator->trans('brokenDoors' . '.description', [], 'alerts'),
            ];
            $alerts[] = $alert;

        }
        if ($brokenEquipments !==0){
            $alert =[
                "name" => $this->translator->trans('brokenEquipments' . '.name', ['quantity' => $brokenEquipments], 'alerts'),
                "description" => $this->translator->trans('brokenEquipments' . '.description', [], 'alerts'),
            ];
            $alerts[] = $alert;
        }

        if ($daedalus->getOxygen() < 8){
            $alerts['oxygen'] = true;$alert =[
                "name" => $this->translator->trans('oxygen' . '.name', [], 'alerts'),
                "description" => $this->translator->trans('oxygen' . '.description', [], 'alerts'),
            ];
            $alerts[] = $alert;
        if ($daedalus->getHull() <= 33){
            $alert =[
                "name" => $this->translator->trans('lowHull' . '.name', ['quantity' => $daedalus->getHull()], 'alerts'),
                "description" => $this->translator->trans('lowHull' . '.description', [], 'alerts'),
            ];
            $alerts[] = $alert;
        }

        
        return $alerts;

    }
}
