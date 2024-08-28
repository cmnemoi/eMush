<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\PlayerHasPendingMissions;
use Mush\Game\Exception\GameException;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Player\Repository\CommanderMissionRepositoryInterface;
use Mush\Player\Service\UpdatePlayerNotificationService;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RejectMission extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::REJECT_MISSION;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private readonly CommanderMissionRepositoryInterface $commanderMissionRepository,
        private readonly UpdatePlayerNotificationService $updatePlayerNotification,
        private readonly StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new PlayerHasPendingMissions([
                'groups' => [ClassConstraint::VISIBILITY],
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
        $mission = $this->mission();

        $this->checkMissionIsPending($mission);
        $this->checkMissionIsAddressedToPlayer($mission);

        $this->markMissionAsRejected($mission);
        $this->deleteCommanderHasIssuedMissionStatus($mission->getCommander());
        $this->sendRejectedMissionNotificationToCommander($mission);
    }

    private function checkMissionIsPending(CommanderMission $mission): void
    {
        if ($mission->isNotPending()) {
            throw new GameException('You cannot accept a mission already accepted / rejected!');
        }
    }

    private function checkMissionIsAddressedToPlayer(CommanderMission $mission): void
    {
        if ($mission->getSubordinate()->notEquals($this->player)) {
            throw new GameException('You cannot accept a mission not addressed to you!');
        }
    }

    private function markMissionAsRejected(CommanderMission $mission): void
    {
        $mission->reject();
        $this->commanderMissionRepository->save($mission);
    }

    private function deleteCommanderHasIssuedMissionStatus(Player $commander): void
    {
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::HAS_ISSUED_MISSION,
            holder: $commander,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function sendRejectedMissionNotificationToCommander(CommanderMission $mission): void
    {
        $this->updatePlayerNotification->execute(
            player: $mission->getCommander(),
            message: PlayerNotificationEnum::MISSION_REJECTED->toString(),
            parameters: ['mission' => mb_substr($mission->getMission(), 0, 50) . '...'],
        );
    }

    private function mission(): CommanderMission
    {
        $params = $this->getParameters();
        $missionId = ($params && \array_key_exists('missionId', $params)) ? $params['missionId'] : null;

        if (!$missionId) {
            throw new GameException('You need to specify which mission you want to accept!');
        }

        return $this->commanderMissionRepository->findByIdOrThrow($missionId);
    }
}
