<?php

namespace Mush\Status\Listener;

use Mush\Action\Event\ActionEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Player\Enum\PlayerVariableEnum;
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
            ActionEvent::RESULT_ACTION => 'onResultAction',
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPreAction(ActionEvent $event): void
    {
        $this->makePlayerActiveService->execute($event->getAuthor());

        if ($event->shouldTriggerRoomTrap()) {
            $this->statusService->removeStatus(
                statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
                holder: $event->getPlace(),
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }

    public function onResultAction(ActionEvent $event): void
    {
        $player = $event->getAuthor();

        if (($actionResult = $event->getActionResult()) === null) {
            throw new \LogicException('actionResult should be provided');
        }

        $actionPaCost = $event->getActionConfig()->getGameVariables()->getValueByName(PlayerVariableEnum::ACTION_POINT);

        if ($actionPaCost > 0) {
            $this->statusService->handleAttempt(
                $player,
                $event->getActionConfig()->getActionName()->value,
                $actionResult,
                $event->getTags(),
                $event->getTime()
            );
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        $this->handleAntiquePerfumeBonus($event);
        $this->removeLyingDownStatusFromTargetPlayer($event);
    }

    private function handleAntiquePerfumeBonus(ActionEvent $event): void
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

    private function removeLyingDownStatusFromTargetPlayer(ActionEvent $event): void
    {
        if ($event->shouldRemoveTargetLyingDownStatus()) {
            $this->statusService->removeStatus(
                statusName: PlayerStatusEnum::LYING_DOWN,
                holder: $event->getActionTarget(),
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }
}
