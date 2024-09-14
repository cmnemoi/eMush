<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Delog extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::DELOG;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private RoomLogServiceInterface $roomLogService,
        private StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new HasStatus([
                'status' => PlayerStatusEnum::HAS_USED_DELOG,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::DAILY_LIMIT,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->hidePlaceLogs();
        $this->createDefacedRoomLog();
        $this->createRoomDefacedStatus();
        $this->createHasUsedDelogStatus();
    }

    private function hidePlaceLogs(): void
    {
        $placeLogs = $this->roomLogService->findAllByDaedalusAndPlace($this->player->getDaedalus(), $this->player->getPlace());

        $placeLogs->map(static fn (RoomLog $log) => $log->hide());
        $placeLogs->map(fn (RoomLog $log) => $this->roomLogService->persist($log));
    }

    private function createDefacedRoomLog(): void
    {
        $this->roomLogService->createLog(
            logKey: LogEnum::DELOGGED,
            place: $this->player->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
        );
    }

    private function createRoomDefacedStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::DELOGGED->toString(),
            holder: $this->player->getPlace(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function createHasUsedDelogStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_USED_DELOG,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }
}
