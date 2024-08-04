<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Chitchat extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::CHITCHAT;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private RoomLogServiceInterface $roomLogService,
        private StatusServiceInterface $statusService,
        private TranslationServiceInterface $translationService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::GAGGED,
            'contain' => false,
            'target' => HasStatus::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::GAGGED_PREVENT_SPOKEN_ACTION,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::GAGGED,
            'contain' => false,
            'target' => HasStatus::PARAMETER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::GAGGED_PREVENT_SPOKEN_ACTION,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::HAS_CHITCHATTED,
            'contain' => false,
            'target' => HasStatus::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DAILY_LIMIT,
        ]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->addMoralePointsToPlayer();
        $this->createConfidenceLog();
        $this->createHasChichattedStatus();
    }

    private function addMoralePointsToPlayer(): void
    {
        $playerVariableEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::MORAL_POINT,
            $this->getOutputQuantity(),
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function createHasChichattedStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_CHITCHATTED,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function createConfidenceLog(): void
    {
        /** @var Player $confident */
        $confident = $this->target;

        $actions = $this->getTranslatedPlayerActions(limit: $confident->getMoralPoint());

        $this->roomLogService->createLog(
            logKey: LogEnum::CONFIDENT_ACTIONS,
            place: $confident->getPlace(),
            visibility: VisibilityEnum::PRIVATE,
            type: 'event_log',
            player: $confident,
            parameters: $this->getLogParametersFromActions($actions),
        );
    }

    private function getTranslatedPlayerActions(int $limit): array
    {
        $actions = [];
        foreach ($this->player->getActionHistory(limit: $limit) as $action) {
            $translatedAction = $this->translationService->translate(
                key: sprintf('%s.name', $action),
                parameters: [],
                domain: 'actions',
                language: $this->player->getLanguage(),
            );
            $actions[] = sprintf('**%s**', $translatedAction);
        }

        return $actions;
    }

    private function getLogParametersFromActions(array $actions): array
    {
        return [
            $this->player->getLogKey() => $this->player->getLogName(),
            'actions' => implode(', ', \array_slice($actions, offset: 0, length: -1)),
            'lastAction' => end($actions),
            'quantity' => \count($actions),
        ];
    }
}
