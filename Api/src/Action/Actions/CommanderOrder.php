<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\CanContactACrewmate;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\NeedTitle;
use Mush\Communication\UseCase\GetContactablePlayersUseCase;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Exception\GameException;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Player\Service\AddCommanderMissionToPlayerService;
use Mush\Player\Service\UpdatePlayerNotificationService;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CommanderOrder extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::COMMANDER_ORDER;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private readonly AddCommanderMissionToPlayerService $addCommanderMissionToPlayer,
        private readonly GetContactablePlayersUseCase $getContactablePlayers,
        private readonly UpdatePlayerNotificationService $updatePlayerNotification,
        private readonly StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new NeedTitle([
                'title' => TitleEnum::COMMANDER,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::HAS_ISSUED_MISSION,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::ISSUE_MISSION_ALREADY_ISSUED,
            ]),
            new CanContactACrewmate([
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::ISSUE_MISSION_NO_TARGET,
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
        $this->checkSubordinateIsContactable();

        $this->createMission();
        $this->createMissionSentNotification();
        $this->createMissionReceivedNotification();
        $this->createHasIssuedMissionStatus();
    }

    private function checkSubordinateIsContactable(): void
    {
        if ($this->getContactablePlayers->execute($this->player)->contains($this->subordinate())) {
            return;
        }

        throw new GameException('You can only give a mission to a player you can contact!');
    }

    private function createMission(): void
    {
        $this->addCommanderMissionToPlayer->execute(
            commander: $this->player,
            subordinate: $this->subordinate(),
            mission: $this->mission()
        );
    }

    private function createMissionSentNotification(): void
    {
        $this->updatePlayerNotification->execute(
            player: $this->player,
            message: PlayerNotificationEnum::MISSION_SENT->toString(),
        );
    }

    private function createMissionReceivedNotification(): void
    {
        $this->updatePlayerNotification->execute(
            player: $this->subordinate(),
            message: PlayerNotificationEnum::MISSION_RECEIVED->toString(),
        );
    }

    private function createHasIssuedMissionStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_ISSUED_MISSION,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function subordinate(): Player
    {
        $params = $this->getParameters();
        $subordinate = ($params && \array_key_exists('subordinate', $params)) ? $params['subordinate'] : '';

        if (!$subordinate) {
            throw new GameException('You need to specify to whom you want to give a mission!');
        }

        return $this->player->getDaedalus()->getAlivePlayerByNameOrThrow($subordinate);
    }

    private function mission(): string
    {
        $params = $this->getParameters();
        $mission = ($params && \array_key_exists('mission', $params)) ? $params['mission'] : '';

        if (!$mission) {
            throw new GameException('You need to specify a mission content!');
        }

        return $mission;
    }
}
