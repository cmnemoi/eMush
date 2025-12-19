<?php

declare(strict_types=1);

namespace Mush\Skill\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Skill\Service\AddRandomSkillToPlayerService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class PlayerActionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AddRandomSkillToPlayerService $addRandomSkillToPlayerService,
        private RoomLogServiceInterface $roomLogService,
        private StatusServiceInterface $statusService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::POST_ACTION => ['onOpenGift', EventPriorityEnum::LOW],
        ];
    }

    public function onOpenGift(ActionEvent $event): void
    {
        $target = $event->getActionTarget();
        $author = $event->getAuthor();
        if ($event->getActionName() === ActionEnum::OPEN_CONTAINER && $target instanceof GameItem) {
            if ($target->getName() === ItemEnum::ANNIVERSARY_GIFT && $author->isMush() && $author->hasStatus(PlayerStatusEnum::HAS_EXTRA_MUSH_SLOT_ANNIVERSARY) === false) {
                $this->statusService->createStatusFromName(
                    statusName: PlayerStatusEnum::HAS_EXTRA_MUSH_SLOT_ANNIVERSARY,
                    holder: $author,
                    tags: [],
                    time: new \DateTime(),
                );
                $skill = $this->addRandomSkillToPlayerService->addRandomMushSkill($author);

                $logParameters = [
                    $author->getLogKey() => $author->getAnonymousKeyOrLogName(),
                    'skill' => $skill->toString(),
                ];

                $this->roomLogService->createLog(
                    logKey: ActionLogEnum::ANNIVERSARY_GIFT_MUSH_SKILL,
                    place: $author->getPlace(),
                    visibility: VisibilityEnum::PRIVATE,
                    type: 'actions_log',
                    player: $author,
                    parameters: $logParameters,
                    dateTime: new \DateTime(),
                );
            }
        }
    }
}
