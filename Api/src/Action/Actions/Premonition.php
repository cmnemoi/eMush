<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Premonition extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::PREMONITION;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private RoomLogServiceInterface $roomLogService,
        private TranslationServiceInterface $translationService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(
            new GameVariableLevel([
                'variableName' => PlayerVariableEnum::MORAL_POINT,
                'checkMode' => GameVariableLevel::EQUALS,
                'target' => GameVariableLevel::PLAYER,
                'value' => 1,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::PREMONITION_INSUFFICIENT_MORALE,
            ])
        );
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
        $this->createRevealedActionLog();
    }

    private function createRevealedActionLog(): void
    {
        $actions = $this->getTranslatedPlayerActions(player: $this->targetPlayer(), limit: $this->numberOfActionsRevealed());

        $this->roomLogService->createLog(
            logKey: LogEnum::PREMONITION_ACTION,
            place: $this->player->getPlace(),
            visibility: VisibilityEnum::PRIVATE,
            type: 'event_log',
            player: $this->player,
            parameters: $this->getLogParametersFromActions($this->targetPlayer(), $actions),
        );
    }

    private function getTranslatedPlayerActions(Player $player, int $limit): array
    {
        $actions = [];
        foreach ($player->getActionHistory(limit: $limit) as $action) {
            $translatedAction = $this->translationService->translate(
                key: sprintf('%s.name', $action),
                parameters: [],
                domain: 'actions',
                language: $player->getLanguage(),
            );
            $actions[] = sprintf('**%s**', $translatedAction);
        }

        return $actions;
    }

    private function getLogParametersFromActions(Player $player, array $actions): array
    {
        return [
            sprintf('target_%s', $player->getLogKey()) => $player->getLogName(),
            'actions' => implode(', ', \array_slice($actions, offset: 0, length: -1)),
            'lastAction' => end($actions),
            'quantity' => \count($actions),
        ];
    }

    private function numberOfActionsRevealed(): int
    {
        return $this->getOutputQuantity();
    }

    private function targetPlayer(): Player
    {
        return $this->target;
    }
}
