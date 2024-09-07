<?php

namespace Mush\Player\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Normalizer\ActionHolderNormalizerTrait;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Normalizer\SpaceBattlePatrolShipNormalizer;
use Mush\Equipment\Normalizer\SpaceBattleTurretNormalizer;
use Mush\Equipment\Normalizer\TerminalNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Service\ClosedExplorationServiceInterface;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Hunter\Service\HunterNormalizerHelperInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Entity\SkillConfigCollection;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CurrentPlayerNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use ActionHolderNormalizerTrait;
    use NormalizerAwareTrait;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private PlayerVariableServiceInterface $playerVariableService;
    private SpaceBattlePatrolShipNormalizer $spaceBattlePatrolShipNormalizer;
    private SpaceBattleTurretNormalizer $spaceBattleTurretNormalizer;
    private TerminalNormalizer $terminalNormalizer;
    private TranslationServiceInterface $translationService;
    private GearToolServiceInterface $gearToolService;
    private HunterNormalizerHelperInterface $hunterNormalizerHelper;
    private ClosedExplorationServiceInterface $closedExplorationService;
    private ExplorationServiceInterface $explorationService;

    public function __construct(
        GameEquipmentServiceInterface $equipmentService,
        PlayerServiceInterface $playerService,
        PlayerVariableServiceInterface $playerVariableService,
        SpaceBattlePatrolShipNormalizer $spaceBattlePatrolShipNormalizer,
        SpaceBattleTurretNormalizer $spaceBattleTurretNormalizer,
        TerminalNormalizer $terminalNormalizer,
        TranslationServiceInterface $translationService,
        GearToolServiceInterface $gearToolService,
        HunterNormalizerHelperInterface $hunterNormalizerHelper,
        ClosedExplorationServiceInterface $closedExplorationService,
        ExplorationServiceInterface $explorationService
    ) {
        $this->gameEquipmentService = $equipmentService;
        $this->playerService = $playerService;
        $this->playerVariableService = $playerVariableService;
        $this->spaceBattlePatrolShipNormalizer = $spaceBattlePatrolShipNormalizer;
        $this->spaceBattleTurretNormalizer = $spaceBattleTurretNormalizer;
        $this->terminalNormalizer = $terminalNormalizer;
        $this->translationService = $translationService;
        $this->gearToolService = $gearToolService;
        $this->hunterNormalizerHelper = $hunterNormalizerHelper;
        $this->closedExplorationService = $closedExplorationService;
        $this->explorationService = $explorationService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        $currentPlayer = $context['currentPlayer'] ?? null;

        return $data instanceof Player
            && $data === $currentPlayer
            && $data->isAlive();
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;
        $daedalus = $player->getDaedalus();

        $language = $daedalus->getLanguage();

        /** @var array<string, mixed> $items */
        $items = [];

        /** @var GameItem $item */
        foreach ($player->getEquipments() as $item) {
            $items[] = $this->normalizer->normalize($item, $format, $context);
        }

        // Sort items in a stack fashion in player's inventory : last in, first out
        usort($items, static fn (array $a, array $b) => $a['updatedAt'] <=> $b['updatedAt']);
        // remove updatedAt from the items because it's not needed in the response
        $items = array_map(static fn (array $item) => array_diff_key($item, ['updatedAt' => null]), $items);

        $character = $player->getName();

        $playerData = [
            'id' => $player->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translationService->translate($character . '.name', [], 'characters', $language),
                'description' => $this->translationService->translate($character . '.description', [], 'characters', $language),
                'selectableHumanSkills' => $this->normalizeSelectableSkills($player->getSelectableHumanSkills(), $format, $context),
                'selectableMushSkills' => $this->normalizeSelectableSkills($player->getSelectableMushSkills(), $format, $context),
                'humanSkillSlots' => $player->hasStatus(PlayerStatusEnum::HAS_READ_MAGE_BOOK) ? min($player->getHumanSkillSlots(), $player->getHumanLevel() + 1) : min($player->getHumanSkillSlots(), $player->getHumanLevel()),
                'mushSkillSlots' => min($player->getDaedalus()->getDaedalusConfig()->getMushSkillSlots(), $player->getMushLevel()),
                'humanLevel' => $player->getHumanLevel(),
                'mushLevel' => $player->getMushLevel(),
            ],
            'gameStatus' => $player->getPlayerInfo()->getGameStatus(),
            'triumph' => [
                'name' => $this->translationService->translate('triumph.name', [], 'player', $language),
                'description' => $this->translationService->translate('triumph.description', [], 'player', $language),
                'quantity' => $player->getTriumph(),
            ],
            'daedalus' => $this->normalizer->normalize($daedalus, $format, $context),
            'spaceBattle' => $this->normalizeSpaceBattle($player, $format, $context),
            'terminal' => $this->terminalNormalizer->normalize($player->getFocusedTerminal(), $format, $context),
            'exploration' => $this->normalizer->normalize($this->getExplorationForPlayer($player), $format, $context),
        ];

        $statuses = $this->normalizeMushPlayerSpores($player, $this->getNormalizedPlayerStatuses($player, $format, $context));

        $diseases = [];
        foreach ($player->getMedicalConditions()->getActiveDiseases() as $disease) {
            $normedDisease = $this->normalizer->normalize($disease, $format, array_merge($context, ['player' => $player]));
            if (\is_array($normedDisease) && \count($normedDisease) > 0) {
                $diseases[] = $normedDisease;
            }
        }

        $titles = [];
        foreach ($player->getTitles() as $title) {
            $normedTitle = [
                'id' => $title,
                'name' => $this->translationService->translate($title . '.name', [], 'player', $language),
                'description' => $this->translationService->translate($title . '.desc', [], 'player', $language),
            ];
            $titles[] = $normedTitle;
        }

        $playerData = array_merge($playerData, [
            'room' => $this->normalizer->normalize($player->getPlace(), $format, $context),
            'skills' => $player->getSkills()->map(fn (Skill $skill) => $this->normalizer->normalize($skill, $format, $context))->toArray(),
            'titles' => $titles,
            'actions' => $this->getNormalizedActions($player, ActionHolderEnum::PLAYER, $player, $format, $context),
            'items' => $items,
            'statuses' => $statuses,
            'diseases' => $diseases,
            'actionPoint' => $this->normalizePlayerGameVariable($player, PlayerVariableEnum::ACTION_POINT, $language),
            'movementPoint' => $this->normalizePlayerGameVariable($player, PlayerVariableEnum::MOVEMENT_POINT, $language),
            'healthPoint' => $this->normalizePlayerGameVariable($player, PlayerVariableEnum::HEALTH_POINT, $language),
            'moralPoint' => $this->normalizePlayerGameVariable($player, PlayerVariableEnum::MORAL_POINT, $language),
            'skillPoints' => $this->getNormalizedSkillPoints($player, $language),
        ]);
        if ($player->hasNotification()) {
            $playerData['notification'] = $this->normalizer->normalize($player->getNotificationOrThrow(), $format, $context);
        }

        return $playerData;
    }

    private function normalizeSelectableSkills(SkillConfigCollection $skillConfigs, ?string $format, array $context): array
    {
        $selectableSkills = [];
        foreach ($skillConfigs as $skillConfig) {
            $selectableSkills[] = $this->normalizer->normalize($skillConfig, $format, $context);
        }

        return $selectableSkills;
    }

    private function normalizeMushPlayerSpores(Player $player, array $normalizedStatuses): array
    {
        if ($player->isMush()) {
            $normalizedSpores = [
                'key' => PlayerStatusEnum::SPORES,
                'name' => $this->translationService->translate(PlayerStatusEnum::SPORES . '.name', [], 'status', $player->getDaedalus()->getLanguage()),
                'description' => $this->translationService->translate(PlayerStatusEnum::SPORES . '.description', [], 'status', $player->getDaedalus()->getLanguage()),
                'charge' => $player->getSpores(),
            ];
            $normalizedStatuses[] = $normalizedSpores;
        }

        return $normalizedStatuses;
    }

    private function normalizePlayerGameVariable(Player $player, string $variable, string $language): array
    {
        $gameVariable = $player->getVariableByName($variable);

        $name = $this->translationService->translate(
            $variable . '.name',
            [
                'quantityHealth' => $player->getHealthPoint(),
                'quantityMoral' => $player->getMoralPoint(),
            ],
            'player',
            $language
        );

        $description = $this->translationService->translate(
            $variable . '.description',
            [
                'quantityAction' => $player->getActionPoint(),
                'quantityMovement' => $player->getMovementPoint(),
            ],
            'player',
            $language
        );

        return [
            'quantity' => $gameVariable->getValue(),
            'max' => $gameVariable->getMaxValue(),
            'name' => $name,
            'description' => $description,
        ];
    }

    private function normalizeSpaceBattle(Player $player, ?string $format = null, array $context = []): ?array
    {
        if (!$player->canSeeSpaceBattle()) {
            return null;
        }

        $daedalus = $player->getDaedalus();
        $patrolShips = $this->getPatrolShipsInBattle($daedalus);
        $turrets = $this->gameEquipmentService->findEquipmentByNameAndDaedalus(EquipmentEnum::TURRET_COMMAND, $daedalus);

        $huntersToNormalize = $this->hunterNormalizerHelper->getHuntersToNormalize($daedalus);
        $normalizedHunters = [];
        foreach ($huntersToNormalize as $hunter) {
            $normalizedHunters[] = $this->normalizer->normalize($hunter, $format, $context);
        }

        return [
            'hunters' => $normalizedHunters,
            'patrolShips' => $patrolShips->map(fn (GameEquipment $patrolShip) => $this->spaceBattlePatrolShipNormalizer->normalize($patrolShip, $format, $context))->toArray(),
            'turrets' => $turrets->map(fn (GameEquipment $turret) => $this->spaceBattleTurretNormalizer->normalize($turret, $format, $context))->toArray(),
        ];
    }

    private function getPatrolShipsInBattle(Daedalus $daedalus): ArrayCollection
    {
        $patrolShips = RoomEnum::getPatrolShips()
            ->map(fn (string $patrolShip) => $this->gameEquipmentService->findEquipmentByNameAndDaedalus($patrolShip, $daedalus)->first())
            ->filter(static fn ($patrolShip) => $patrolShip instanceof GameEquipment);
        $patrolShipsInBattle = $patrolShips->filter(static fn (GameEquipment $patrolShip) => $patrolShip->isInSpaceBattle());

        return new ArrayCollection(array_values($patrolShipsInBattle->toArray()));
    }

    private function getNormalizedPlayerStatuses(Player $player, ?string $format = null, array $context = []): array
    {
        $statuses = [];
        foreach ($player->getStatuses() as $status) {
            $normedStatus = $this->normalizer->normalize($status, $format, array_merge($context, ['player' => $player]));
            if (\is_array($normedStatus) && \count($normedStatus) > 0) {
                $statuses[] = $normedStatus;
            }
        }

        return $statuses;
    }

    private function getNormalizedSkillPoints(Player $player, string $language): array
    {
        $normalizedSkillPoints = [];

        /** @var Skill $skill */
        foreach ($player->getSkillsWithPoints() as $skill) {
            $skillPoints = $skill->getSkillPointsName();
            $normalizedSkillPoints[] = [
                'key' => $skillPoints,
                'quantityPoint' => [
                    'name' => $this->translationService->translate($skillPoints . '.name', [], 'player', $language),
                    'description' => $this->translationService->translate($skillPoints . '.description', [], 'player', $language),
                    'quantity' => $skill->getSkillPoints(),
                ],
            ];
        }

        return $normalizedSkillPoints;
    }

    private function getExplorationForPlayer(Player $player): ?Exploration
    {
        // If player is lost but the exploration is finished, we need to normalize a dummy exploration with
        // basic information from their last closed exploration.
        if (!$player->isExploring() && $player->hasStatus(PlayerStatusEnum::LOST)) {
            return $this->explorationService->getDummyExplorationForLostPlayer(
                $this->closedExplorationService->getMostRecentForPlayer($player)
            );
        }

        return $player->getDaedalus()->getExploration();
    }
}
