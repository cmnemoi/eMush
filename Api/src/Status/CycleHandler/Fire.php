<?php

namespace Mush\Status\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\RoomEventEnum;
use Mush\Room\Event\RoomEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\StatusEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Fire extends AbstractCycleHandler
{
    protected string $name = StatusEnum::FIRE;

    private RandomServiceInterface $randomService;
    private EventDispatcherInterface $eventDispatcher;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        RandomServiceInterface $randomService,
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        DaedalusServiceInterface $daedalusService
    ) {
        $this->randomService = $randomService;
        $this->eventDispatcher = $eventDispatcher;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->daedalusService = $daedalusService;
    }


    public function handleNewCycle($object, Daedalus $daedalus, \DateTime $dateTime): void
    {
        if (!$object instanceof ChargeStatus && $object->getName() !== StatusEnum::FIRE) {
            return;
        }

        if (!($room = $object->getRoom())) {
            throw new \LogicException('Fire status does not have a room');
        }

        //If fire is active
        if ($object->getCharge() > 0) {
            $this->propagateFire($room, $dateTime);
            $this->fireDamage($room, $dateTime);
        }
    }


    private function propagateFire(Room $room, \DateTime $date): Room
    {
        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();

        /** @var Door $door */
        foreach ($room->getDoors() as $door) {
            $adjacentRoom = $door->getOtherRoom($room);

            if ($this->randomService->isSuccessfull($difficultyConfig->getPropagatingFireRate())) {
                $roomEvent = new RoomEvent($adjacentRoom, $date);
                $roomEvent->setReason(RoomEventEnum::PROPAGATING_FIRE);
                $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);
            }
        }

        return $room;
    }

    private function fireDamage(Room $room, \DateTime $date): Room
    {
        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();

        foreach ($room->getPlayers() as $player) {
            $damage = $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getFirePlayerDamage());
            $actionModifier = new Modifier();
            $actionModifier
                ->setDelta(-$damage)
                ->setTarget(ModifierTargetEnum::HEALTH_POINT)
            ;

            $playerEvent = new PlayerEvent($player, $date);
            $playerEvent->setReason(EndCauseEnum::BURNT);
            $playerEvent->setModifier($actionModifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }

        //@TODO: also add items
        foreach ($room->getEquipments() as $equipment) {
            $this->gameEquipmentService->handleBreakFire($equipment, $date);
        }

        if ($this->randomService->isSuccessfull($difficultyConfig->getHullFireDamageRate())) {
            $damage = intval($this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getFireHullDamage()));

            $room->getDaedalus()->addHull(-$damage);
            $this->daedalusService->persist($room->getDaedalus());
        }

        return $room;
    }


    public function handleNewDay($object, Daedalus $daedalus, \DateTime $dateTime): void
    {
        return;
    }


}