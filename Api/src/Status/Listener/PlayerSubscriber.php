<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Game\Enum\TitleEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Event\PlayerChangedPlaceEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Service\UserServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private StatusServiceInterface $statusService,
        private UserServiceInterface $userService,
    ) {}

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::CONVERSION_PLAYER => [
                ['onConversionPlayer'],
            ],
            PlayerEvent::NEW_PLAYER => ['onNewPlayer', 100],
            PlayerEvent::DEATH_PLAYER => 'onPlayerDeath',
            PlayerChangedPlaceEvent::class => 'onPlayerChangedPlace',
            PlayerEvent::TITLE_ATTRIBUTED => 'onTitleAttributed',
        ];
    }

    public function onConversionPlayer(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();

        $mushStatusConfig = $this->statusService->getStatusConfigByNameAndDaedalus(PlayerStatusEnum::MUSH, $player->getDaedalus());
        $this->statusService->createStatusFromConfig($mushStatusConfig, $player, $playerEvent->getTags(), $playerEvent->getTime());
    }

    public function onNewPlayer(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $characterConfig = $playerEvent->getCharacterConfig();
        $reasons = $playerEvent->getTags();
        $time = $playerEvent->getTime();

        if ($characterConfig === null) {
            throw new \LogicException('playerConfig should be provided');
        }
        $initStatuses = $characterConfig->getInitStatuses();

        foreach ($initStatuses as $statusConfig) {
            $this->statusService->createStatusFromConfig(
                $statusConfig,
                $player,
                $reasons,
                $time
            );
        }

        $this->createBeginnerStatusForPlayer($playerEvent);
    }

    public function onPlayerDeath(PlayerEvent $playerEvent): void
    {
        $this->statusService->removeAllStatuses($playerEvent->getPlayer(), $playerEvent->getTags(), $playerEvent->getTime());

        $this->removePariahStatus($playerEvent);
    }

    public function onPlayerChangedPlace(PlayerChangedPlaceEvent $event): void
    {
        $oldRoom = $event->getOldPlace();
        if ($oldRoom->isNotARoom()) {
            return;
        }

        if ($oldRoom->hasStatus(PlaceStatusEnum::CEASEFIRE->toString())) {
            $this->deleteCeasefireStatus($event);
        }

        $this->removeGuardianStatus($event);
        $this->removeLyingDownStatus($event);
        $this->removeFocusedStatus($event);

        if ($event->getPlace()->isNotARoom()) {
            return;
        }

        $player = $event->getPlayer();
        if ($player->hasStatus(PlayerStatusEnum::PREVIOUS_ROOM)) {
            $this->updatePreviousRoomStatus($event);
        } else {
            $this->createPreviousRoomStatus($event);
        }
    }

    public function onTitleAttributed(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $title = $playerEvent->getTitle();

        $this->statusService->createStatusFromName(
            statusName: TitleEnum::getHasGainedTitleStatusName($title),
            holder: $player,
            tags: $playerEvent->getTags(),
            time: $playerEvent->getTime(),
        );
    }

    private function deleteCeasefireStatus(PlayerChangedPlaceEvent $event): void
    {
        $oldPlace = $event->getOldPlace();
        $player = $event->getPlayer();

        $ceasefireStatus = $oldPlace->getStatusByNameOrThrow(PlaceStatusEnum::CEASEFIRE->toString());
        if ($ceasefireStatus->getTargetOrThrow()->notEquals($player)) {
            return;
        }

        $this->statusService->removeStatus(
            statusName: $ceasefireStatus->getName(),
            holder: $ceasefireStatus->getOwner(),
            tags: $event->getTags(),
            time: $event->getTime(),
            visibility: VisibilityEnum::PUBLIC,
        );
    }

    private function createPreviousRoomStatus(PlayerChangedPlaceEvent $event): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::PREVIOUS_ROOM,
            holder: $event->getPlayer(),
            tags: $event->getTags(),
            time: $event->getTime(),
            target: $event->getOldPlace(),
        );
    }

    private function updatePreviousRoomStatus(PlayerChangedPlaceEvent $event): void
    {
        $this->statusService->updateStatusTarget(
            status: $event->getPlayer()->getStatusByNameOrThrow(PlayerStatusEnum::PREVIOUS_ROOM),
            target: $event->getOldPlace(),
        );
    }

    private function removeGuardianStatus(PlayerChangedPlaceEvent $event): void
    {
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::GUARDIAN,
            holder: $event->getPlayer(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );
    }

    private function removeLyingDownStatus(PlayerChangedPlaceEvent $event): void
    {
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $event->getPlayer(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );
    }

    private function removeFocusedStatus(PlayerChangedPlaceEvent $event): void
    {
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $event->getPlayer(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );
    }

    private function removePariahStatus(PlayerEvent $playerEvent): void
    {
        $deadPlayer = $playerEvent->getPlayer();
        $daedalus = $deadPlayer->getDaedalus();

        if ($daedalus->hasAPariah() === false) {
            return;
        }

        $currentPariah = $daedalus->getCurrentPariah();
        if ($currentPariah->getStatusByNameOrThrow(PlayerStatusEnum::PARIAH)->getTargetOrThrow()->equals($deadPlayer)) {
            $this->statusService->removeStatus(
                statusName: PlayerStatusEnum::PARIAH,
                holder: $currentPariah,
                tags: $playerEvent->getTags(),
                time: $playerEvent->getTime(),
            );
        }
    }

    private function createBeginnerStatusForPlayer(PlayerEvent $event): void
    {
        $user = $event->getPlayer()->getUser();

        if ($this->userService->isABeginner($user)) {
            $this->statusService->createStatusFromName(
                statusName: PlayerStatusEnum::BEGINNER,
                holder: $event->getPlayer(),
                tags: $event->getTags(),
                time: $event->getTime(),
            );
        }
    }
}
