<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\AlertEnum;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentClassEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DaedalusNormalizer implements ContextAwareNormalizerInterface
{
    private CycleServiceInterface $cycleService;
    private GameConfig $gameConfig;
    private TranslatorInterface $translator;

    public function __construct(
        CycleServiceInterface $cycleService,
        GameConfigServiceInterface $gameConfigService,
        TranslatorInterface $translator
    ) {
        $this->cycleService = $cycleService;
        $this->gameConfig = $gameConfigService->getConfig();
        $this->translator = $translator;
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
                'alerts' => $this->getAlerts($daedalus),
                'minimap' => $this->getMinimap($daedalus),
            ];
    }

    public function getMinimap($daedalus): array
    {
        $minimap = [];
        foreach ($daedalus->getRoom() as $room) {
            $minimap[] = [
                'key' => $room->getName(),
                'name' => $this->translator->trans($room->getName() . '.name', [], 'rooms'),
                'players' => $room->getPlayers()->count(),
                'fire' => $room->getStatusByName(StatusEnum::FIRE) !== null,
            ];

            //@TODO add project fire detector, anomaly detector doors detectors and actopi protocol
        }

        return $minimap;
    }

    public function getAlerts(Daedalus $daedalus): array
    {
        $oxygenAlert = 8;
        $hullAlert = 33;

        $alerts = [];

        $numberAlert = array_filter($this->countAlert($daedalus), function (int $value) {return $value > 0; });

        foreach ($numberAlert as $key => $number) {
            $alerts[] = $this->translateAlert($key, $number);
        }

        if ($daedalus->getOxygen() < $oxygenAlert) {
            $alerts[] = $this->translateAlert(AlertEnum::LOW_OXYGEN);
        }
        if ($daedalus->getHull() <= $hullAlert) {
            $alerts[] = $this->translateAlert(AlertEnum::LOW_HULL, $daedalus->getHull());
        }

        if (count($alerts)===0){
            $alerts[] = $this->translateAlert(AlertEnum::NO_ALERT);
        }

        return $alerts;
    }

    public function countAlert(Daedalus $daedalus): array
    {
        $fire = 0;
        $brokenDoors = 0;
        $brokenEquipments = 0;

        foreach ($daedalus->getRooms() as $room) {
            if ($room->getStatusByName(StatusEnum::FIRE)) {
                $fire = $fire + 1;
            }
            $brokenDoors = $brokenDoors + $room->getEquipment()
                ->filter(fn (GameEquipment $equipment) => $equipment instanceof Door && $equipment->isBroken())->count();
            $brokenEquipments = $brokenEquipments + $room->getEquipment()
                ->filter(fn (GameEquipment $equipment) => $equipment->getClassName() === EquipmentClassEnum::GAME_EQUIPMENT &&
                        $equipment->isBroken()
                    )->count();
        }

        return [AlertEnum::NUMBER_FIRE => $fire, AlertEnum::BROKEN_DOORS => $brokenDoors, AlertEnum::BROKEN_EQUIPMENTS => $brokenEquipments];
    }

    public function translateAlert(string $key, ?int $quantity = null): array
    {
        if ($quantity !== null) {
            if ($quantity > 1) {
                $plural = '.plural';
            } else {
                $plural = '.single';
            }
            $alert = [
                'key' => $key,
                'name' => $this->translator->trans($key . '.name' . $plural, ['quantity' => $quantity], 'alerts'),
                'description' => $this->translator->trans($key . '.description', [], 'alerts'),
            ];
        } else {
            $alert = [
                'key' => $key,
                'name' => $this->translator->trans($key . '.name', [], 'alerts'),
                'description' => $this->translator->trans($key . '.description', [], 'alerts'),
            ];
        }

        return $alert;
    }
}
