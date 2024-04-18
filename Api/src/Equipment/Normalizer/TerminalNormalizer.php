<?php

declare(strict_types=1);

namespace Mush\Equipment\Normalizer;

use Mush\Action\Actions\AdvanceDaedalus;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Action\Normalizer\ActionHolderNormalizerTrait;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\DifficultyEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\DaedalusStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TerminalNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use ActionHolderNormalizerTrait;
    use NormalizerAwareTrait;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlanetServiceInterface $planetService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        PlanetServiceInterface $planetService,
        TranslationServiceInterface $translationService
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->planetService = $planetService;
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GameEquipment;
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
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
                key: $terminalKey . '.name',
                parameters: [],
                domain: 'terminal',
                language: $daedalus->getLanguage()
            ),
            'tips' => $this->translationService->translate(
                key: $terminalKey . '.tips',
                parameters: [
                    'max_planets' => $currentPlayer->getPlayerInfo()->getCharacterConfig()->getMaxDiscoverablePlanets(),
                ],
                domain: 'terminal',
                language: $daedalus->getLanguage()
            ),
            'actions' => $this->normalizeTerminalActions($terminal, $format, $context),
            'sectionTitles' => $this->normalizeTerminalSectionTitles($terminal),
            'buttons' => $this->getNormalizedTerminalButtons($terminal),
        ];

        $astroTerminalInfos = $this->normalizeAstroTerminalInfos($format, $context);
        $commandTerminalInfos = $this->normalizeCommandTerminalInfos($terminal);
        $biosTerminalInfos = $this->normalizeBiosTerminalInfos($context);

        $normalizedTerminal['infos'] = array_merge($astroTerminalInfos, $commandTerminalInfos, $biosTerminalInfos);

        return $normalizedTerminal;
    }

    private function normalizeTerminalActions(GameEquipment $terminal, ?string $format, array $context = []): array
    {
        $actions = $terminal->getActions()
            ->filter(static fn (Action $action) => $action->getScope() === ActionScopeEnum::TERMINAL)
            ->filter(static fn (Action $action) => $action->getTarget() === null);

        $normalizedActions = [];

        /** @var Action $action */
        foreach ($actions as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (\is_array($normedAction) && \count($normedAction) > 0) {
                $normalizedActions[] = $normedAction;
            }
        }

        $normalizedActions = $this->getNormalizedActionsSortedBy('name', $normalizedActions);
        $normalizedActions = $this->getNormalizedActionsSortedBy('actionPointCost', $normalizedActions);

        return $normalizedActions;
    }

    private function normalizeTerminalSectionTitles(GameEquipment $terminal): array
    {
        $titles = [];
        $terminalKey = $terminal->getName();
        if (\array_key_exists($terminalKey, EquipmentEnum::$terminalSectionTitlesMap)) {
            foreach (EquipmentEnum::$terminalSectionTitlesMap[$terminalKey] as $sectionKey) {
                $titles[$sectionKey] = $this->translationService->translate(
                    $terminalKey . '.' . $sectionKey,
                    [],
                    'terminal',
                    $terminal->getDaedalus()->getLanguage(),
                );
            }
        }

        return $titles;
    }

    private function getNormalizedTerminalButtons(GameEquipment $terminal): array
    {
        $buttons = [];
        $terminalKey = $terminal->getName();
        if (\array_key_exists($terminalKey, EquipmentEnum::$terminalButtonsMap)) {
            foreach (EquipmentEnum::$terminalButtonsMap[$terminalKey] as $buttonKey) {
                $buttons[$buttonKey]['name'] = $this->translationService->translate(
                    $terminalKey . '.' . $buttonKey . '_button_name',
                    [],
                    'terminal',
                    $terminal->getDaedalus()->getLanguage(),
                );
                $buttons[$buttonKey]['description'] = $this->translationService->translate(
                    $terminalKey . '.' . $buttonKey . '_button_description',
                    [],
                    'terminal',
                    $terminal->getDaedalus()->getLanguage(),
                );
            }
        }

        return $buttons;
    }

    private function normalizeCommandTerminalInfos(GameEquipment $terminal): array
    {
        $terminalKey = $terminal->getName();
        if ($terminalKey !== EquipmentEnum::COMMAND_TERMINAL) {
            return [];
        }

        $difficulty = DifficultyEnum::NORMAL;
        $daedalus = $terminal->getDaedalus();
        if ($daedalus->isInHardMode()) {
            $difficulty = DifficultyEnum::HARD;
        } elseif ($daedalus->isInVeryHardMode()) {
            $difficulty = DifficultyEnum::VERY_HARD;
        }

        $advanceDaedalusStatusKey = AdvanceDaedalus::getActionStatus($daedalus, $this->gameEquipmentService);
        // we don't want to tell player that arack prevents travel
        if ($advanceDaedalusStatusKey === AdvanceDaedalus::ARACK_PREVENTS_TRAVEL) {
            $advanceDaedalusStatusKey = AdvanceDaedalus::OK;
        }

        $advanceDaedalusStatus = AdvanceDaedalus::$statusMap[$advanceDaedalusStatusKey];
        $advanceDaedalusStatus['text'] = $this->translationService->translate(
            key: $terminalKey . '.advance_daedalus_status_' . $advanceDaedalusStatusKey,
            parameters: [],
            domain: 'terminal',
            language: $daedalus->getLanguage()
        );

        $translatedDaedalusOrientation = $this->translationService->translate(
            key: $daedalus->getOrientation(),
            parameters: [],
            domain: 'misc',
            language: $daedalus->getDaedalus()->getLanguage()
        );

        return [
            'orientation' => $this->translationService->translate(
                key: $terminalKey . '.orientation',
                parameters: ['orientation' => $translatedDaedalusOrientation],
                domain: 'terminal',
                language: $daedalus->getLanguage()
            ),
            'difficulty' => $this->translationService->translate(
                key: $terminalKey . '.difficulty_' . $difficulty,
                parameters: [],
                domain: 'terminal',
                language: $daedalus->getLanguage()
            ),
            'advanceDaedalusStatus' => $advanceDaedalusStatus,
        ];
    }

    private function normalizeAstroTerminalInfos(?string $format, array $context): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        /** @var GameEquipment $terminal */
        $terminal = $context['terminal'];

        /** @var Daedalus $daedalus */
        $daedalus = $terminal->getDaedalus();

        $terminalKey = $terminal->getName();
        if ($terminalKey !== EquipmentEnum::ASTRO_TERMINAL) {
            return [];
        }

        $planets = $daedalus->hasStatus(DaedalusStatusEnum::IN_ORBIT)
            ? $this->planetService->findAllByDaedalus($daedalus)
            : $currentPlayer->getPlanets();

        return [
            'planets' => $this->normalizer->normalize($planets, $format, $context),
            'maxDiscoverablePlanets' => $currentPlayer->getPlayerInfo()->getCharacterConfig()->getMaxDiscoverablePlanets(),
            'inOrbit' => $daedalus->hasStatus(DaedalusStatusEnum::IN_ORBIT) ? $this->translationService->translate(
                key: $terminalKey . '.in_orbit',
                parameters: [],
                domain: 'terminal',
                language: $daedalus->getLanguage()
            ) : null,
        ];
    }

    private function normalizeBiosTerminalInfos(array $context): array
    {
        /** @var GameEquipment $terminal */
        $terminal = $context['terminal'];

        $terminalKey = $terminal->getName();
        if ($terminalKey !== EquipmentEnum::BIOS_TERMINAL) {
            return [];
        }

        return [
            'availableCpuPriorities' => $this->getTranslatedAvailableCpuPriorities($terminal),
            'currentCpuPriority' => $terminal->getDaedalus()->getDaedalusInfo()->getNeron()->getCpuPriority(),
        ];
    }

    private function getTranslatedAvailableCpuPriorities(GameEquipment $terminal): array
    {
        $availableCpuPriorities = [];
        foreach (NeronCpuPriorityEnum::getAll() as $cpuPriority) {
            $availableCpuPriorities[] = [
                'key' => $cpuPriority,
                'name' => $this->translationService->translate(
                    key: $terminal->getName() . '.cpu_priority_' . $cpuPriority,
                    parameters: [],
                    domain: 'terminal',
                    language: $terminal->getDaedalus()->getLanguage()
                ),
            ];
        }

        return $availableCpuPriorities;
    }
}
