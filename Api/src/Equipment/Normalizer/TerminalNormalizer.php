<?php

declare(strict_types=1);

namespace Mush\Equipment\Normalizer;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\DifficultyEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TerminalNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof GameEquipment;
    }

    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        /** @var ?GameEquipment $terminal */
        $terminal = $object;

        $context['terminal'] = $terminal;

        if ($terminal === null) {
            return [];
        }

        if ($currentPlayer->getFocusedTerminal() !== $terminal) {
            return [];
        }

        $daedalus = $currentPlayer->getDaedalus();
        $terminalKey = $terminal->getName();

        $normalizedTerminal = [
            'id' => $terminal->getId(),
            'key' => $terminalKey,
            'name' => $this->translationService->translate(
                $terminalKey . '.name',
                [],
                'terminal',
                $daedalus->getLanguage()
            ),
            'tips' => $this->translationService->translate(
                $terminalKey . '.tips',
                [],
                'terminal',
                $daedalus->getLanguage()
            ),
            'actions' => $this->normalizeTerminalActions($terminal, $format, $context),
            'sectionTitles' => $this->normalizeTerminalSectionTitles($terminal),
        ];

        // @TODO : add other terminal infos
        $commandTerminalInfos = $this->normalizeCommandTerminalInfos($terminal);
        $normalizedTerminal['infos'] = $commandTerminalInfos;

        return $normalizedTerminal;
    }

    private function normalizeTerminalActions(GameEquipment $terminal, ?string $format, array $context = []): array
    {
        $actions = $terminal->getActions()->filter(fn (Action $action) => $action->getScope() === ActionScopeEnum::TERMINAL);

        $normalizedActions = [];
        /** @var Action $action */
        foreach ($actions as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $normalizedActions[] = $normedAction;
            }
        }

        return $normalizedActions;
    }

    private function normalizeTerminalSectionTitles(GameEquipment $terminal): array
    {
        $titles = [];
        $terminalKey = $terminal->getName();
        foreach (EquipmentEnum::$terminalSectionTitlesMap[$terminalKey] as $sectionKey) {
            $titles[$sectionKey] = $this->translationService->translate(
                $terminalKey . '.' . $sectionKey,
                [],
                'terminal',
                $terminal->getDaedalus()->getLanguage(),
            );
        }

        return $titles;
    }

    private function normalizeCommandTerminalInfos(GameEquipment $terminal): ?array
    {
        $terminalKey = $terminal->getName();
        if ($terminalKey !== EquipmentEnum::COMMAND_TERMINAL) {
            return null;
        }

        $difficulty = DifficultyEnum::NORMAL;
        $daedalus = $terminal->getDaedalus();
        if ($daedalus->isInHardMode()) {
            $difficulty = DifficultyEnum::HARD;
        } elseif ($daedalus->isInVeryHardMode()) {
            $difficulty = DifficultyEnum::VERY_HARD;
        }

        return [
            'difficulty' => $this->translationService->translate(
                $terminalKey . '.difficulty_' . $difficulty,
                [],
                'terminal',
                $daedalus->getLanguage()
            ),
        ];
    }
}
