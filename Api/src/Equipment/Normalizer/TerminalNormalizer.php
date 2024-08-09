<?php

declare(strict_types=1);

namespace Mush\Equipment\Normalizer;

use Mush\Action\Actions\AbstractMoveDaedalusAction;
use Mush\Action\Actions\AdvanceDaedalus;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Normalizer\ActionHolderNormalizerTrait;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\DifficultyEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\DaedalusStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TerminalNormalizer implements NormalizerInterface, NormalizerAwareInterface
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

        if ($terminal instanceof GameItem) {
            $context['terminalItem'] = $terminal;
        } else {
            $context['terminal'] = $terminal;
        }

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
            'actions' => $this->getNormalizedActions($terminal, ActionHolderEnum::TERMINAL, $currentPlayer, $format, $context),
            'sectionTitles' => $this->normalizeTerminalSectionTitles($terminal),
            'buttons' => $this->getNormalizedTerminalButtons($terminal),
            'projects' => $this->getNormalizedTerminalProjects($terminal, $format, $context),
        ];

        $astroTerminalInfos = $this->normalizeAstroTerminalInfos($terminal, $format, $context);
        $commandTerminalInfos = $this->normalizeCommandTerminalInfos($terminal);
        $biosTerminalInfos = $this->normalizeBiosTerminalInfos($terminal);
        $pilgredTerminalInfos = $this->getNormalizedPilgredTerminalInfos($terminal);
        $neronCoreInfos = $this->getNormalizedNeronCoreInfos($terminal);

        $normalizedTerminal['infos'] = array_merge($astroTerminalInfos, $commandTerminalInfos, $biosTerminalInfos, $pilgredTerminalInfos, $neronCoreInfos);

        return $normalizedTerminal;
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

    private function getNormalizedTerminalProjects(GameEquipment $terminal, ?string $format, array $context): array
    {
        $projects = match ($terminal->getName()) {
            EquipmentEnum::PILGRED => [$terminal->getDaedalus()->getPilgred()],
            EquipmentEnum::NERON_CORE, EquipmentEnum::AUXILIARY_TERMINAL => $terminal->getDaedalus()->getProposedNeronProjects(),
            default => [],
        };

        $normalizedProjects = [];

        foreach ($projects as $project) {
            $normalizedProject = $this->normalizer->normalize($project, $format, $context);
            if (\is_array($normalizedProject) && \count($normalizedProject) > 0) {
                $normalizedProjects[] = $normalizedProject;
            }
        }

        return $normalizedProjects;
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
        if ($advanceDaedalusStatusKey === AbstractMoveDaedalusAction::ARACK_PREVENTS_TRAVEL) {
            $advanceDaedalusStatusKey = AbstractMoveDaedalusAction::OK;
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

    private function normalizeAstroTerminalInfos(GameEquipment $terminal, ?string $format, array $context): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

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

    private function normalizeBiosTerminalInfos(GameEquipment $terminal): array
    {
        $terminalKey = $terminal->getName();
        if ($terminalKey !== EquipmentEnum::BIOS_TERMINAL) {
            return [];
        }

        $daedalus = $terminal->getDaedalus();
        $neron = $daedalus->getNeron();

        $infos = [
            'availableCpuPriorities' => $this->getTranslatedAvailableCpuPriorities($terminal),
            'currentCpuPriority' => $neron->getCpuPriority(),
            'crewLocks' => $this->getTranslatedAvailableCrewLocks($terminal),
            'currentCrewLock' => $neron->getCrewLock()->value,
        ];
        if ($daedalus->hasFinishedProject(ProjectName::PLASMA_SHIELD)) {
            $infos['plasmaShieldToggles'] = $this->getTranslatedPlasmaShieldToggles($terminal);
            $infos['isPlasmaShieldActive'] = $neron->isPlasmaShieldActive();
        }
        if ($daedalus->hasFinishedProject(ProjectName::MAGNETIC_NET)) {
            $infos['magneticNetToggles'] = $this->getTranslatedMagneticNetToggles($terminal);
            $infos['isMagneticNetActive'] = $neron->isMagneticNetActive();
        }

        return $infos;
    }

    private function getNormalizedNeronCoreInfos(GameEquipment $terminal): array
    {
        $daedalus = $terminal->getDaedalus();
        $language = $daedalus->getLanguage();
        $terminalKey = $terminal->getName();
        if ($terminalKey !== EquipmentEnum::NERON_CORE && $terminalKey !== EquipmentEnum::AUXILIARY_TERMINAL) {
            return [];
        }

        return [
            'noProposedNeronProjects' => $daedalus->hasNoProposedNeronProjects(),
            'noProposedNeronProjectsDescription' => $this->translationService->translate(
                key: $terminalKey . '.no_proposed_neron_projects_description',
                parameters: [],
                domain: 'terminal',
                language: $language
            ),
        ];
    }

    private function getNormalizedPilgredTerminalInfos(GameEquipment $terminal): array
    {
        $daedalus = $terminal->getDaedalus();
        $terminalKey = $terminal->getName();
        if ($terminalKey !== EquipmentEnum::PILGRED) {
            return [];
        }

        return [
            'pilgredIsFinished' => $daedalus->isPilgredFinished(),
            'pilgredFinishedDescription' => $this->translationService->translate(
                key: $terminalKey . '.pilgred_finished_description',
                parameters: [],
                domain: 'terminal',
                language: $daedalus->getLanguage()
            ),
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

    private function getTranslatedAvailableCrewLocks(GameEquipment $terminal): array
    {
        $availableCrewLocks = [];
        foreach (NeronCrewLockEnum::getValues() as $crewLock) {
            $availableCrewLocks[] = [
                'key' => $crewLock->value,
                'name' => $this->translationService->translate(
                    key: $terminal->getName() . '.crew_lock_' . $crewLock->value,
                    parameters: [],
                    domain: 'terminal',
                    language: $terminal->getDaedalus()->getLanguage()
                ),
            ];
        }

        return $availableCrewLocks;
    }

    private function getTranslatedPlasmaShieldToggles(GameEquipment $terminal): array
    {
        $plasmaShieldToggles = [];
        foreach (['activate', 'deactivate'] as $toggle) {
            $plasmaShieldToggles[] = [
                'key' => $toggle,
                'name' => $this->translationService->translate(
                    key: $terminal->getName() . '.plasma_shield_toggle_' . $toggle,
                    parameters: [],
                    domain: 'terminal',
                    language: $terminal->getDaedalus()->getLanguage()
                ),
            ];
        }

        return $plasmaShieldToggles;
    }

    private function getTranslatedMagneticNetToggles(GameEquipment $terminal): array
    {
        $magneticNetToggles = [];
        foreach (['active', 'inactive'] as $toggle) {
            $magneticNetToggles[] = [
                'key' => $toggle,
                'name' => $this->translationService->translate(
                    key: $terminal->getName() . '.magnetic_net_toggle_' . $toggle,
                    parameters: [],
                    domain: 'terminal',
                    language: $terminal->getDaedalus()->getLanguage()
                ),
            ];
        }

        return $magneticNetToggles;
    }
}
