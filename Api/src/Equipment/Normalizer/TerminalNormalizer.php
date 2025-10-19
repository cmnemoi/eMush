<?php

declare(strict_types=1);

namespace Mush\Equipment\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\AbstractMoveDaedalusAction;
use Mush\Action\Actions\AdvanceDaedalus;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Normalizer\ActionHolderNormalizerTrait;
use Mush\Communications\Collection\RebelBaseCollection;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\NeronVersionRepositoryInterface;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Repository\TradeRepositoryInterface;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Daedalus\Enum\NeronFoodDestructionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\DifficultyEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Hunter\Enum\HunterEnum;
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

    public function __construct(
        private readonly GameEquipmentServiceInterface $gameEquipmentService,
        private readonly LinkWithSolRepositoryInterface $linkWithSolRepository,
        private readonly NeronVersionRepositoryInterface $neronVersionRepository,
        private readonly PlanetServiceInterface $planetService,
        private readonly RebelBaseRepositoryInterface $rebelBaseRepository,
        private readonly TranslationServiceInterface $translationService,
        private readonly TradeRepositoryInterface $tradeRepository,
        private readonly XylophRepositoryInterface $xylophEntryRepository
    ) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GameEquipment;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            GameEquipment::class => false,
        ];
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
                parameters: [
                    'neronVersion' => $this->neronVersionRepository->findByDaedalusIdOrThrow($daedalus->getId())->toString(),
                ],
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
            'items' => $this->getNormalizedTerminalItems($terminal, $format, $context),
            'rebelBases' => $this->getNormalizedRebelBases($terminal, $format, $context),
            'xylophEntries' => $this->getNormalizedXylophEntries($terminal, $format, $context),
            'trades' => $this->getNormalizedTrades($terminal, $format, $context),
        ];

        $astroTerminalInfos = $this->normalizeAstroTerminalInfos($terminal, $format, $context);
        $commandTerminalInfos = $this->normalizeCommandTerminalInfos($terminal);
        $biosTerminalInfos = $this->normalizeBiosTerminalInfos($terminal);
        $pilgredTerminalInfos = $this->getNormalizedPilgredTerminalInfos($terminal);
        $neronCoreInfos = $this->getNormalizedNeronCoreInfos($terminal);
        $researchTerminalInfos = $this->getNormalizedResearchTerminalInfos($terminal);
        $calculatorInfos = $this->getNormalizedCalculatorInfos($terminal);
        $commsCenterInfos = $this->getNormalizedCommsCenterInfos($terminal);

        $normalizedTerminal['infos'] = array_merge(
            $astroTerminalInfos,
            $commandTerminalInfos,
            $biosTerminalInfos,
            $pilgredTerminalInfos,
            $neronCoreInfos,
            $researchTerminalInfos,
            $calculatorInfos,
            $commsCenterInfos,
        );

        return $normalizedTerminal;
    }

    private function normalizeTerminalSectionTitles(GameEquipment $terminal): array
    {
        $titles = [];
        $terminalKey = $terminal->getName();
        $daedalusId = $terminal->getDaedalus()->getId();
        $parameters = [];
        if ($terminal->getName() === EquipmentEnum::COMMUNICATION_CENTER) {
            $parameters['neronVersion'] = $this->neronVersionRepository->findByDaedalusIdOrThrow($daedalusId)->toString();
        }

        if (\array_key_exists($terminalKey, EquipmentEnum::$terminalSectionTitlesMap)) {
            foreach (EquipmentEnum::$terminalSectionTitlesMap[$terminalKey] as $sectionKey) {
                $titles[$sectionKey] = $this->translationService->translate(
                    $terminalKey . '.' . $sectionKey,
                    $parameters,
                    'terminal',
                    $terminal->getDaedalus()->getLanguage(),
                );
            }
        }

        return $titles;
    }

    private function getNormalizedTerminalProjects(GameEquipment $terminal, ?string $format, array $context): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];
        $rebelBases = new RebelBaseCollection($this->rebelBaseRepository->findAllDecodedRebelBases($currentPlayer->getDaedalus()->getId()));

        $projects = match ($terminal->getName()) {
            EquipmentEnum::PILGRED => [$terminal->getDaedalus()->getPilgred()],
            EquipmentEnum::NERON_CORE, EquipmentEnum::AUXILIARY_TERMINAL => $terminal->getDaedalus()->getProposedNeronProjects(),
            EquipmentEnum::RESEARCH_LABORATORY => $terminal->getDaedalus()->getVisibleResearchProjectsForPlayer($currentPlayer, $rebelBases),
            default => [],
        };

        $normalizedProjects = [];

        foreach ($projects as $project) {
            $normalizedProject = $this->normalizer->normalize($project, $format, $context);
            if (\is_array($normalizedProject) && \count($normalizedProject) > 0) {
                $normalizedProjects[] = $normalizedProject;
            }
        }

        return $this->putLastAdvancedProjectFirst($normalizedProjects);
    }

    private function getNormalizedTerminalItems(GameEquipment $terminal, ?string $format, array $context): array
    {
        if ($terminal->getName() !== EquipmentEnum::RESEARCH_LABORATORY) {
            return [];
        }

        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        $playerItems = $currentPlayer->getEquipments();
        $placeItems = $terminal->getPlace()->getNonPersonalItems();

        $allItems = array_merge($playerItems->toArray(), $placeItems->toArray());
        $normalizedItems = [];
        foreach ($allItems as $item) {
            $normalizedItems[] = $this->normalizer->normalize($item, $format, $context);
        }

        return $normalizedItems;
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

    private function getNormalizedRebelBases(GameEquipment $terminal, ?string $format, array $context): array
    {
        $daedalus = $terminal->getDaedalus();
        $rebelBases = $this->rebelBaseRepository->findAllByDaedalusId($daedalus->getId());

        $normalizedRebelBases = [];
        foreach ($rebelBases as $rebelBase) {
            $normalizedRebelBases[] = $this->normalizer->normalize($rebelBase, $format, $context);
        }

        return $normalizedRebelBases;
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

        return [
            'orientation' => $this->translationService->translate(
                key: $terminalKey . '.orientation',
                parameters: ['orientation' => $daedalus->getOrientation()],
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
            'foodDestructionOptions' => $this->getTranslatedAvailableFoodDestructionOptions($terminal),
            'currentFoodDestructionOption' => $neron->getFoodDestructionOption()->value,
            'neronInhibitionToggles' => $this->getTranslatedNeronInhibitionToggles($terminal),
            'isNeronInhibited' => $neron->isInhibited(),
            'areVocodedAnnouncementsActive' => $neron->areVocodedAnnouncementsActive(),
            'vocodedAnnouncementsToggles' => $this->getTranslatedVocodedAnnouncementsToggles($terminal),
            'areDeathAnnouncementsActive' => $neron->areDeathAnnouncementsActive(),
            'deathAnnouncementsToggles' => $this->getTranslatedDeathAnnouncementsToggles($terminal),
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
            'noProposedNeronProjects' => $daedalus->getProposedNeronProjects()->isEmpty(),
            'noProposedNeronProjectsDescription' => $this->translationService->translate(
                key: $terminalKey . '.no_proposed_neron_projects_description',
                parameters: [],
                domain: 'terminal',
                language: $language
            ),
        ];
    }

    private function getNormalizedResearchTerminalInfos(GameEquipment $terminal): array
    {
        $terminalKey = $terminal->getName();
        if ($terminalKey !== EquipmentEnum::RESEARCH_LABORATORY) {
            return [];
        }

        return [
            'requirements' => $this->getFullfilledResearchRequirements($terminal, $terminalKey),
        ];
    }

    private function getNormalizedCalculatorInfos(GameEquipment $terminal): array
    {
        $terminalKey = $terminal->getName();
        if ($terminalKey !== EquipmentEnum::CALCULATOR) {
            return [];
        }

        $infos = [];
        $infos['nothingToCompute'] = $this->getNothingToComputeInfo($terminal);
        $infos['edenComputed'] = $this->getEdenComputedInfo($terminal);

        return $infos;
    }

    private function getNothingToComputeInfo(GameEquipment $terminal): ?string
    {
        $place = $terminal->getPlace();
        $terminalKey = $terminal->getName();

        return $place->doesNotHaveEquipmentByName(ItemEnum::STARMAP_FRAGMENT) ? $this->translationService->translate(
            key: $terminalKey . '.nothing_to_compute',
            parameters: [],
            domain: 'terminal',
            language: $terminal->getDaedalus()->getLanguage()
        ) : null;
    }

    private function getEdenComputedInfo(GameEquipment $terminal): ?string
    {
        $daedalus = $terminal->getDaedalus();
        $terminalKey = $terminal->getName();

        return $daedalus->hasStatus(DaedalusStatusEnum::EDEN_COMPUTED) ? $this->translationService->translate(
            key: $terminalKey . '.eden_computed',
            parameters: [],
            domain: 'terminal',
            language: $daedalus->getLanguage()
        ) : null;
    }

    private function getFullfilledResearchRequirements(GameEquipment $terminal, string $terminalKey): array
    {
        $allRequirements = new ArrayCollection(
            [
                [
                    'key' => 'chun_present',
                    'fullfilled' => $terminal->getPlace()->isChunForResearch(),
                ],
                [
                    'key' => 'mush_dead',
                    'fullfilled' => $terminal->getDaedalus()->getPlayers()->isThereAMushUnlockingProjects(),
                ],
            ]
        );
        $language = $terminal->getDaedalus()->getLanguage();

        return $allRequirements
            ->filter(static fn ($requirement) => $requirement['fullfilled'])
            ->map(function ($requirement) use ($terminalKey, $language) {
                return $this->translationService->translate(
                    key: $terminalKey . '.' . $requirement['key'],
                    parameters: [],
                    domain: 'terminal',
                    language: $language,
                );
            })->toArray();
    }

    private function getNormalizedPilgredTerminalInfos(GameEquipment $terminal): array
    {
        $daedalus = $terminal->getDaedalus();
        $terminalKey = $terminal->getName();
        if ($terminalKey !== EquipmentEnum::PILGRED) {
            return [];
        }

        return [
            'pilgredIsFinished' => $daedalus->getPilgred()->isFinished(),
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

    private function getTranslatedAvailableFoodDestructionOptions(GameEquipment $terminal): array
    {
        $availableFoodDestructionOptions = [];
        foreach (NeronFoodDestructionEnum::getValues() as $option) {
            $availableFoodDestructionOptions[] = [
                'key' => $option->value,
                'name' => $this->translationService->translate(
                    key: $terminal->getName() . '.food_destruction_option_' . $option->value,
                    parameters: [],
                    domain: 'terminal',
                    language: $terminal->getDaedalus()->getLanguage()
                ),
            ];
        }

        return $availableFoodDestructionOptions;
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

    private function getTranslatedNeronInhibitionToggles(GameEquipment $terminal): array
    {
        $neronInhibitionToggles = [];
        foreach (['active', 'inactive'] as $toggle) {
            $neronInhibitionToggles[] = [
                'key' => $toggle,
                'name' => $this->translationService->translate(
                    key: $terminal->getName() . '.neron_inhibition_toggle_' . $toggle,
                    parameters: [],
                    domain: 'terminal',
                    language: $terminal->getDaedalus()->getLanguage()
                ),
            ];
        }

        return $neronInhibitionToggles;
    }

    private function getNormalizedCommsCenterInfos(GameEquipment $terminal)
    {
        $terminalKey = $terminal->getName();
        if ($terminalKey !== EquipmentEnum::COMMUNICATION_CENTER) {
            return [];
        }

        $daedalus = $terminal->getDaedalus();

        $link = $this->linkWithSolRepository->findByDaedalusIdOrThrow($daedalus->getId());
        $neronVersion = $this->neronVersionRepository->findByDaedalusIdOrThrow($daedalus->getId());

        $infos = [
            'linkStrength' => $this->translationService->translate(
                key: $terminalKey . '.link_strength',
                parameters: ['quantity' => $link->getStrength()],
                domain: 'terminal',
                language: $daedalus->getLanguage()
            ),
            'neronUpdateStatus' => $this->translationService->translate(
                key: $terminalKey . '.neron_update_status',
                parameters: ['quantity' => $neronVersion->getMinor()],
                domain: 'terminal',
                language: $daedalus->getLanguage()
            ),
            'selectRebelBaseToDecode' => $this->translationService->translate(
                key: $terminalKey . '.select_rebel_base_to_decode',
                parameters: [],
                domain: 'terminal',
                language: $daedalus->getLanguage()
            ),
            'never' => $this->translationService->translate(
                key: $terminalKey . '.never',
                parameters: [],
                domain: 'terminal',
                language: $daedalus->getLanguage()
            ),
        ];

        if ($link->isEstablished()) {
            $infos['linkEstablished'] = $this->translationService->translate(
                key: $terminalKey . '.link_established',
                parameters: [],
                domain: 'terminal',
                language: $daedalus->getLanguage()
            );
        }

        if ($this->tradeRepository->isThereAvailableTrade($daedalus->getId())) {
            if ($daedalus->getAttackingHunters()->getAllExceptType(HunterEnum::ASTEROID)->count() > 0) {
                $infos['cannotTradeUnderAttack'] = $this->translationService->translate(
                    key: $terminalKey . '.cannot_trade_under_attack',
                    parameters: [],
                    domain: 'terminal',
                    language: $daedalus->getLanguage()
                );
            }

            $infos['seeCommunications'] = $this->translationService->translate(
                key: $terminalKey . '.see_communications',
                parameters: [],
                domain: 'terminal',
                language: $daedalus->getLanguage()
            );
            $infos['seeTrades'] = $this->translationService->translate(
                key: $terminalKey . '.see_trades',
                parameters: [],
                domain: 'terminal',
                language: $daedalus->getLanguage()
            );
        }

        return $infos;
    }

    private function getNormalizedXylophEntries(GameEquipment $terminal, ?string $format, array $context): array
    {
        $daedalus = $terminal->getDaedalus();
        $xylophEntries = $this->xylophEntryRepository->findAllByDaedalusId($daedalus->getId());

        $normalizedXylophEntries = [];
        foreach ($xylophEntries as $xylophEntry) {
            $normalizedXylophEntries[] = $this->normalizer->normalize($xylophEntry, $format, $context);
        }

        return $normalizedXylophEntries;
    }

    private function getNormalizedTrades(GameEquipment $terminal, ?string $format, array $context): array
    {
        $daedalus = $terminal->getDaedalus();
        if ($daedalus->getAttackingHunters()->getAllExceptType(HunterEnum::ASTEROID)->count() > 0) {
            return [];
        }

        $trades = $this->tradeRepository->findAllByDaedalusId($daedalus->getId());

        $normalizedTrades = [];
        foreach ($trades as $trade) {
            $normalizedTrades[] = $this->normalizer->normalize($trade, $format, $context);
        }

        return $normalizedTrades;
    }

    private function getTranslatedVocodedAnnouncementsToggles(GameEquipment $terminal): array
    {
        $vocodedAnnouncementsToggles = [];
        foreach (['active', 'inactive'] as $toggle) {
            $vocodedAnnouncementsToggles[] = [
                'key' => $toggle,
                'name' => $this->translationService->translate(
                    key: $terminal->getName() . '.vocoded_announcements_toggle_' . $toggle,
                    parameters: [],
                    domain: 'terminal',
                    language: $terminal->getDaedalus()->getLanguage()
                ),
            ];
        }

        return $vocodedAnnouncementsToggles;
    }

    private function getTranslatedDeathAnnouncementsToggles(GameEquipment $terminal): array
    {
        $deathAnnouncementsToggles = [];
        foreach (['active', 'inactive'] as $toggle) {
            $deathAnnouncementsToggles[] = [
                'key' => $toggle,
                'name' => $this->translationService->translate(
                    key: $terminal->getName() . '.death_announcements_toggle_' . $toggle,
                    parameters: [],
                    domain: 'terminal',
                    language: $terminal->getDaedalus()->getLanguage()
                ),
            ];
        }

        return $deathAnnouncementsToggles;
    }

    private function putLastAdvancedProjectFirst(array $normalizedProjects): array
    {
        foreach ($normalizedProjects as $normalizedProject) {
            if ($normalizedProject['isLastAdvancedProject']) {
                $lastAdvancedProject = $normalizedProject;

                return array_merge(
                    [$lastAdvancedProject],
                    array_filter($normalizedProjects, static function ($project) use ($lastAdvancedProject) {
                        return $project['id'] !== $lastAdvancedProject['id'];
                    })
                );
            }
        }

        return $normalizedProjects;
    }
}
