<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Action\Event\ActionVariableEvent;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\UpdatePlayerNotificationService;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ActionVariableSubscriber implements EventSubscriberInterface
{
    public const ACTION_CLUMSINESS_DAMAGE = -2;

    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private EventServiceInterface $eventService,
        private UpdatePlayerNotificationService $updatePlayerNotification,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionVariableEvent::APPLY_COST => 'onApplyCost',
            ActionVariableEvent::ROLL_ACTION_PERCENTAGE => 'onRollPercentage',
        ];
    }

    public function onApplyCost(ActionVariableEvent $event): void
    {
        $playerVariableEvent = new PlayerVariableEvent(
            $event->getAuthor(),
            $event->getVariableName(),
            -$event->getRoundedQuantity(),
            $event->getTags(),
            $event->getTime()
        );
        $playerVariableEvent->setVisibility(VisibilityEnum::HIDDEN);

        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    public function onRollPercentage(ActionVariableEvent $event): void
    {
        if (!$event->shouldHurtPlayer($this->d100Roll)) {
            return;
        }

        if ($this->isHurtFromCat($event)) {
            $this->infectHumanIfCatIsContaminated($event);
        }

        $this->hurtPlayer($event, $this->getClumsinessEndCause($event));
    }

    private function hurtPlayer(ActionVariableEvent $event, string $endCause): void
    {
        $event->addTag($endCause);

        $damageWereApplied = $this->tryToApplyDamage($event);

        if ($damageWereApplied) {
            $notification = match ($endCause) {
                EndCauseEnum::CLUMSINESS => PlayerNotificationEnum::CLUMSINESS,
                EndCauseEnum::CLUMSINESS_CAT => PlayerNotificationEnum::CLUMSINESS_CAT,
                default => throw new \InvalidArgumentException("Unknown end cause: {$endCause}"),
            };

            $this->updatePlayerNotification->execute(player: $event->getAuthor(), message: $notification);
        }
    }

    private function infectHumanIfCatIsContaminated(ActionVariableEvent $event): void
    {
        $author = $event->getAuthor();
        if ($author->isMush()) {
            return;
        }

        $cat = $event->getEquipmentActionTargetOrThrow();

        $catInfectedStatus = $cat->getStatusByName(EquipmentStatusEnum::CAT_INFECTED);
        if (!$catInfectedStatus) {
            return;
        }

        $infectAuthor = $catInfectedStatus->getPlayerTargetOrThrow();
        $playerVariableEvent = new PlayerVariableEvent(
            $author,
            PlayerVariableEnum::SPORE,
            1,
            $event->getTagsWithout(EndCauseEnum::CLUMSINESS_CAT),
            $event->getTime()
        );
        $playerVariableEvent->setAuthor($infectAuthor);
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function isHurtFromCat(ActionVariableEvent $event): bool
    {
        $target = $event->getActionTarget();

        return $target instanceof GameItem && $target->isSchrodinger();
    }

    private function getClumsinessEndCause(ActionVariableEvent $event): string
    {
        return $this->isHurtFromCat($event) ? EndCauseEnum::CLUMSINESS_CAT : EndCauseEnum::CLUMSINESS;
    }

    private function tryToApplyDamage(ActionVariableEvent $event): bool
    {
        $playerVariableEvent = new PlayerVariableEvent(
            $event->getAuthor(),
            PlayerVariableEnum::HEALTH_POINT,
            self::ACTION_CLUMSINESS_DAMAGE,
            $event->getTags(),
            $event->getTime()
        );
        $events = $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);

        return !$events->werePrevented();
    }
}
