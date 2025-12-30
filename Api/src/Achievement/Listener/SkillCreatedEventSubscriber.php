<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Skill\Event\SkillCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SkillCreatedEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdatePlayerStatisticService $updatePlayerStatisticService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            SkillCreatedEvent::class => ['onSkillCreated', EventPriorityEnum::LOWEST],
        ];
    }

    public function onSkillCreated(SkillCreatedEvent $event): void
    {
        if ($event->doesNotHaveTag(ActionEnum::READ_BOOK->toString())) {
            return;
        }

        $this->updatePlayerStatisticService->execute(
            player: $event->skillPlayer(),
            statisticName: StatisticEnum::MAGE_BOOK_LEARNED,
        );
    }
}
