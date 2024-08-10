<?php

declare(strict_types=1);

namespace Mush\RoomLog\Service;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;

final class ActionHistoryRevealLogService
{
    public function __construct(
        private RoomLogServiceInterface $roomLogService,
        private TranslationServiceInterface $translationService,
    ) {}

    public function generate(int $numberOfActions, AbstractAction $action): void
    {
        $translatedActions = $this->getTranslatedPlayerActions($this->targetPlayer($action), $numberOfActions);

        $this->roomLogService->createLog(
            logKey: $this->logKey($action),
            place: $this->player($action)->getPlace(),
            visibility: VisibilityEnum::PRIVATE,
            type: 'event_log',
            player: $this->player($action),
            parameters: $this->getLogParametersFromTranslatedActions($this->targetPlayer($action), $translatedActions),
        );
    }

    private function getTranslatedPlayerActions(Player $player, int $numberOfActions): array
    {
        $translatedActions = [];
        foreach ($player->getActionHistory(limit: $numberOfActions) as $action) {
            $translatedAction = $this->translationService->translate(
                key: sprintf('%s.name', $action),
                parameters: [],
                domain: 'actions',
                language: $player->getLanguage(),
            );
            $translatedActions[] = sprintf('**%s**', $translatedAction);
        }

        return $translatedActions;
    }

    private function getLogParametersFromTranslatedActions(Player $player, array $translatedActions): array
    {
        return [
            $player->getLogKey() => $player->getLogName(),
            'actions' => implode(', ', \array_slice($translatedActions, offset: 0, length: -1)),
            'lastAction' => end($translatedActions),
            'quantity' => \count($translatedActions),
        ];
    }

    private function logKey(AbstractAction $action): string
    {
        return match ($action->getActionName()) {
            ActionEnum::CHITCHAT->value => LogEnum::CONFIDENT_ACTIONS,
            ActionEnum::PREMONITION->value => LogEnum::PREMONITION_ACTION,
            default => throw new \InvalidArgumentException("{$action->getActionName()} action does not support action reveal !."),
        };
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     */
    private function targetPlayer(AbstractAction $action): Player
    {
        return match ($action->getActionName()) {
            ActionEnum::CHITCHAT->value => $action->getPlayer(),
            ActionEnum::PREMONITION->value => $action->getTarget(),
            default => throw new \InvalidArgumentException("{$action->getActionName()} action does not support action reveal !."),
        };
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     */
    private function player(AbstractAction $action): Player
    {
        return match ($action->getActionName()) {
            ActionEnum::CHITCHAT->value => $action->getTarget(),
            ActionEnum::PREMONITION->value => $action->getPlayer(),
            default => throw new \InvalidArgumentException("{$action->getActionName()} action does not support action reveal !."),
        };
    }
}
