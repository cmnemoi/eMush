<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Event\CommanderMissionAcceptedEvent;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ActionEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdatePlayerStatisticService $updatePlayerStatisticService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => [
                ['onPreAction', EventPriorityEnum::HIGHEST],
            ],
            ActionEvent::POST_ACTION => [
                ['onPostSuccessfulAction', EventPriorityEnum::LOWEST],
                ['onPostFailedAction', EventPriorityEnum::LOWEST],
            ],
            CommanderMissionAcceptedEvent::class => ['onCommanderMissionAccepted', EventPriorityEnum::LOWEST],
        ];
    }

    public function onPreAction(ActionEvent $event): void
    {
        // Should be about comfort action
        if ($event->getActionName() !== ActionEnum::COMFORT) {
            return;
        }

        // The target should be at most 1 morale point
        if ($event->getPlayerActionTargetOrThrow()->getMoralPoint() > 1) {
            return;
        }

        $this->updatePlayerStatisticService->execute(
            player: $event->getAuthorOrThrow(),
            statisticName: StatisticEnum::KIND_PERSON,
        );
    }

    public function onPostSuccessfulAction(ActionEvent $event): void
    {
        if ($event->getActionResultOrThrow()->isAFail()) {
            return;
        }

        $author = $event->getAuthor();
        $statisticName = match ($event->getActionName()) {
            ActionEnum::BORING_SPEECH, ActionEnum::MOTIVATIONAL_SPEECH => $event->getAuthorOrThrow()->getAlivePlayersInRoomExceptSelf()->count() >= 8 ? StatisticEnum::POLITICIAN : StatisticEnum::NULL,
            ActionEnum::COFFEE => StatisticEnum::COFFEE_TAKEN,
            ActionEnum::COM_MANAGER_ANNOUNCEMENT => StatisticEnum::DAILY_ORDER,
            ActionEnum::CONSUME => $this->getConsumeStatisticToIncrementFromEvent($event),
            ActionEnum::CONSUME_DRUG => StatisticEnum::DRUGS_TAKEN,
            ActionEnum::INSTALL_CAMERA => StatisticEnum::CAMERA_INSTALLED,
            ActionEnum::SEARCH => StatisticEnum::SUCCEEDED_INSPECTION,
            ActionEnum::SELF_SURGERY, ActionEnum::SURGERY => StatisticEnum::SURGEON,
            default => StatisticEnum::NULL,
        };

        $this->updatePlayerStatisticService->execute(
            player: $author,
            statisticName: $statisticName,
        );
    }

    public function onPostFailedAction(ActionEvent $event): void
    {
        if ($event->getActionResultOrThrow()->isASuccess()) {
            return;
        }

        $author = $event->getAuthor();
        $statisticName = match ($event->getActionName()) {
            ActionEnum::SELF_SURGERY, ActionEnum::SURGERY => StatisticEnum::BUTCHER,
            default => StatisticEnum::NULL,
        };

        $this->updatePlayerStatisticService->execute(
            player: $author,
            statisticName: $statisticName,
        );
    }

    public function onCommanderMissionAccepted(CommanderMissionAcceptedEvent $event): void
    {
        $this->updatePlayerStatisticService->execute(
            player: $event->getCommander(),
            statisticName: StatisticEnum::GIVE_MISSION,
        );
    }

    private function getConsumeStatisticToIncrementFromEvent(ActionEvent $event): StatisticEnum
    {
        return match ($event->getEquipmentActionTargetOrThrow()->getName()) {
            GameRationEnum::COFFEE => StatisticEnum::COFFEE_MAN,
            GameRationEnum::COOKED_RATION => StatisticEnum::COOKED_TAKEN,
            GameRationEnum::STANDARD_RATION => StatisticEnum::RATION_TAKEN,
            default => StatisticEnum::NULL,
        };
    }
}
