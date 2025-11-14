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
    // ": {equipment}" placeholder
    private const string ACTION_PARAMETER_PLACEHOLDER_REGEX = '/\s*:\s*\{[^}]+\}/';

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
        return array_map(
            fn (string $translatedName) => $this->formatName($translatedName, $player),
            array_map(fn (ActionEnum $actionName) => $this->translateActionName($actionName, $player), $player->getActionHistory(limit: $numberOfActions))
        );
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
            ActionEnum::TORTURE->value => LogEnum::TORTURER_ACTIONS,
            default => throw new \InvalidArgumentException("{$action->getActionName()} action does not support action reveal!"),
        };
    }

    private function player(AbstractAction $action): Player
    {
        return match ($action->getActionName()) {
            ActionEnum::CHITCHAT->value => $action->playerTarget(),
            ActionEnum::PREMONITION->value => $action->getPlayer(),
            ActionEnum::TORTURE->value => $action->getPlayer(),
            default => throw new \InvalidArgumentException("{$action->getActionName()} action does not support action reveal!"),
        };
    }

    private function targetPlayer(AbstractAction $action): Player
    {
        return match ($action->getActionName()) {
            ActionEnum::CHITCHAT->value => $action->getPlayer(),
            ActionEnum::PREMONITION->value => $action->playerTarget(),
            ActionEnum::TORTURE->value => $action->playerTarget(),
            default => throw new \InvalidArgumentException("{$action->getActionName()} action does not support action reveal!"),
        };
    }

    private function formatName(string $translatedActionName, Player $player): string
    {
        // replace parameters placeholder like "Graft : {equipment}" by "Graft"
        /** @var ?string $cleanedActionName */
        $cleanedActionName = preg_replace(self::ACTION_PARAMETER_PLACEHOLDER_REGEX, '', $translatedActionName);
        if ($cleanedActionName === null) {
            throw new \RuntimeException("Something went wrong when cleaning translation for action {$translatedActionName}");
        }

        return \sprintf('**%s**', $cleanedActionName);
    }

    private function translateActionName(ActionEnum $action, Player $player): string
    {
        return $this->translationService->translate(
            key: \sprintf('%s.name', $action->toString()),
            parameters: [],
            domain: 'actions',
            language: $player->getLanguage(),
        );
    }
}
