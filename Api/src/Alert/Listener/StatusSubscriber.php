<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusSubscriber implements EventSubscriberInterface
{
    private AlertServiceInterface $alertService;

    public function __construct(
        AlertServiceInterface $alertService,
    ) {
        $this->alertService = $alertService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_DELETED => 'onStatusDeleted',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();

        switch ($event->getStatusName()) {
            case EquipmentStatusEnum::BROKEN:
                if (!$holder instanceof GameEquipment) {
                    throw new UnexpectedTypeException($holder, GameEquipment::class);
                }
                if ($holder instanceof GameItem) {
                    return;
                }
                $this->alertService->handleEquipmentBreak($holder);

                return;

            case StatusEnum::FIRE:
                /** @var Place $place */
                $place = $event->getPlace();
                $this->alertService->handleFireStart($place);

                return;

            case DaedalusStatusEnum::NO_GRAVITY:
                $this->alertService->gravityAlert($holder->getDaedalus(), AlertEnum::BREAK);

                return;

            case DaedalusStatusEnum::NO_GRAVITY_REPAIRED:
                $this->alertService->gravityAlert($holder->getDaedalus(), AlertEnum::REPAIR);

                return;

            case PlayerStatusEnum::LOST:
                $this->alertService->handlePlayerLost($holder->getDaedalus());

                return;

            case PlayerStatusEnum::PARIAH:
                $this->alertService->handlePariahApplied($this->getHolderAsPlayer($event));

                return;
        }
    }

    public function onStatusDeleted(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();

        switch ($event->getStatusName()) {
            case PlayerStatusEnum::LOST:
                $this->alertService->handleLostPlayerFound($holder->getDaedalus());

                return;
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();

        switch ($event->getStatusName()) {
            case EquipmentStatusEnum::BROKEN:
                if (!$holder instanceof GameEquipment) {
                    throw new UnexpectedTypeException($holder, GameEquipment::class);
                }
                $this->alertService->handleEquipmentRepair($holder);

                return;

            case StatusEnum::FIRE:
                /** @var Place $place */
                $place = $event->getPlace();
                $this->alertService->handleFireStop($place);

                return;

            case DaedalusStatusEnum::NO_GRAVITY_REPAIRED:
                $this->alertService->gravityAlert($holder->getDaedalus(), AlertEnum::GRAVITY_REBOOT);

                return;

            case PlayerStatusEnum::PARIAH:
                $this->alertService->handlePariahRemoved($this->getHolderAsPlayer($event));

                return;
        }
    }

    private function getHolderAsPlayer(StatusEvent $event): Player
    {
        $holder = $event->getStatusHolder();

        if (!$holder instanceof Player) {
            throw new UnexpectedTypeException($holder, Player::class);
        }

        return $holder;
    }
}
