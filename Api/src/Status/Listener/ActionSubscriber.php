<?php

namespace Mush\Status\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\MakePlayerActiveService;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MakePlayerActiveService $makePlayerActiveService,
        private StatusServiceInterface $statusService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => ['onPreAction', EventPriorityEnum::LOW],
            ActionEvent::RESULT_ACTION => ['onResultAction', EventPriorityEnum::LOW],
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPreAction(ActionEvent $event): void
    {
        $this->makePlayerActiveService->execute($event->getAuthor());
    }

    public function onResultAction(ActionEvent $event): void
    {
        if ($event->shouldTriggerRoomTrap()) {
            $this->statusService->removeStatus(
                statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
                holder: $event->getPlace(),
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }

        if ($event->shouldTriggerAttemptHandling()) {
            $this->statusService->handleAttempt(
                holder: $event->getAuthor(),
                actionName: $event->getActionName()->toString(),
                result: $event->getActionResultOrThrow(),
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        $this->immunizeAntiquePerfumePlayerAfterAShower($event);
        $this->removeLyingDownStatusIfPlayerWakesUpAfterAction($event);
        $this->removeFocusedStatusIfPlayerIsAccessingTakenBlockOfPostIt($event);
    }

    private function immunizeAntiquePerfumePlayerAfterAShower(ActionEvent $event): void
    {
        if ($event->shouldCreateParfumeAntiqueImmunizedStatus()) {
            $this->statusService->createStatusFromName(
                statusName: PlayerStatusEnum::ANTIQUE_PERFUME_IMMUNIZED,
                holder: $event->getAuthor(),
                tags: $event->getTags(),
                time: $event->getTime(),
            );
        }
    }

    private function removeLyingDownStatusIfPlayerWakesUpAfterAction(ActionEvent $event): void
    {
        if ($event->shouldRemoveTargetLyingDownStatus()) {
            $this->statusService->removeStatus(
                statusName: PlayerStatusEnum::LYING_DOWN,
                holder: $event->getPlayerActionTargetOrThrow(),
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }

    private function removeFocusedStatusIfPlayerIsAccessingTakenBlockOfPostIt(ActionEvent $event): void
    {
        if ($event->getActionName() !== ActionEnum::TAKE) {
            return;
        }

        /** @var ?GameEquipment $blockOfPostIt */
        $blockOfPostIt = $event->getActionTarget();
        if ($blockOfPostIt?->getLogName() !== ToolItemEnum::BLOCK_OF_POST_IT) {
            return;
        }

        /** @var ?Player $focusedPlayer */
        $focusedPlayer = $blockOfPostIt?->getTargetingStatusByName(PlayerStatusEnum::FOCUSED)?->getPlayerOwnerOrThrow();

        if ($focusedPlayer) {
            $this->statusService->removeStatus(
                statusName: PlayerStatusEnum::FOCUSED,
                holder: $focusedPlayer,
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }
}
