<?php

namespace Mush\Player\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
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
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Hunter\Service\HunterNormalizerHelperInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\Status\Entity\ChargeStatus;
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

    public function __construct(
        GameEquipmentServiceInterface $equipmentService,
        PlayerServiceInterface $playerService,
        PlayerVariableServiceInterface $playerVariableService,
        SpaceBattlePatrolShipNormalizer $spaceBattlePatrolShipNormalizer,
        SpaceBattleTurretNormalizer $spaceBattleTurretNormalizer,
        TerminalNormalizer $terminalNormalizer,
        TranslationServiceInterface $translationService,
        GearToolServiceInterface $gearToolService,
        HunterNormalizerHelperInterface $hunterNormalizerHelper
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
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        $currentPlayer = $context['currentPlayer'] ?? null;

        return $data instanceof Player
            && $data === $currentPlayer
            && $data->isAlive()
        ;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;
        $daedalus = $player->getDaedalus();

        $language = $daedalus->getLanguage();

        $items = [];
        /** @var GameItem $item */
        foreach ($player->getEquipments() as $item) {
            $items[] = $this->normalizer->normalize($item, $format, $context);
        }

        $character = $player->getName();

        $playerData = [
            'id' => $player->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translationService->translate($character . '.name', [], 'characters', $language),
                'description' => $this->translationService->translate($character . '.description', [], 'characters', $language),
                'skills' => $player->getPlayerInfo()->getCharacterConfig()->getSkills(),
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
            'exploration' => $this->normalizer->normalize($player->getExploration(), $format, $context),
        ];

        $statuses = $this->normalizeMushPlayerSpores($player, $this->getNormalizedPlayerStatuses($player, $format, $context));

        $diseases = [];
        foreach ($player->getMedicalConditions()->getActiveDiseases() as $disease) {
            $normedDisease = $this->normalizer->normalize($disease, $format, array_merge($context, ['player' => $player]));
            if (is_array($normedDisease) && count($normedDisease) > 0) {
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
            'skills' => $this->getNormalizedPlayerSkills($player, $format, $context),
            'titles' => $titles,
            'actions' => $this->getActions($object, $format, $context),
            'items' => $items,
            'statuses' => $statuses,
            'diseases' => $diseases,
            'actionPoint' => $this->normalizePlayerGameVariable($player, PlayerVariableEnum::ACTION_POINT, $language),
            'movementPoint' => $this->normalizePlayerGameVariable($player, PlayerVariableEnum::MOVEMENT_POINT, $language),
            'healthPoint' => $this->normalizePlayerGameVariable($player, PlayerVariableEnum::HEALTH_POINT, $language),
            'moralPoint' => $this->normalizePlayerGameVariable($player, PlayerVariableEnum::MORAL_POINT, $language),
            'shootPoint' => $this->getNormalizedShootPoints($player, $language),
        ]);

        return $playerData;
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
            $variable . '.name', [
            'quantityHealth' => $player->getHealthPoint(),
            'quantityMoral' => $player->getMoralPoint(),
        ], 'player',
            $language
        );

        $description = $this->translationService->translate(
            $variable . '.description', [
            'quantityAction' => $player->getActionPoint(),
            'quantityMovement' => $player->getMovementPoint(),
        ], 'player',
            $language
        );

        return [
                'quantity' => $gameVariable->getValue(),
                'max' => $gameVariable->getMaxValue(),
                'name' => $name,
                'description' => $description,
        ];
    }

    private function getActions(Player $player, ?string $format, array $context): array
    {
        $contextualActions = $this->getContextActions($player);
        $selfActions = $player->getSelfActions();

        $actionsToNormalize = array_merge($contextualActions->toArray(), $selfActions->toArray());

        $actions = [];

        /** @var Action $action */
        foreach ($actionsToNormalize as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        $actions = $this->getNormalizedActionsSortedBy('name', $actions);
        $actions = $this->getNormalizedActionsSortedBy('actionPointCost', $actions);

        return $actions;
    }

    private function getContextActions(Player $player): Collection
    {
        $scope = [ActionScopeEnum::SELF];

        return $this->gearToolService->getActionsTools($player, $scope);
    }

    private function normalizeSpaceBattle(Player $player, string $format = null, array $context = []): ?array
    {
        if (!$player->canSeeSpaceBattle()) {
            return null;
        }

        $daedalus = $player->getDaedalus();
        $patrolShips = $this->getPatrolShipsInBattle($daedalus);
        $turrets = $this->gameEquipmentService->findByNameAndDaedalus(EquipmentEnum::TURRET_COMMAND, $daedalus);

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
            ->map(fn (string $patrolShip) => $this->gameEquipmentService->findByNameAndDaedalus($patrolShip, $daedalus)->first())
            ->filter(fn ($patrolShip) => $patrolShip instanceof GameEquipment)
        ;
        $patrolShipsInBattle = $patrolShips->filter(fn (GameEquipment $patrolShip) => $patrolShip->isInSpaceBattle());

        return new ArrayCollection(array_values($patrolShipsInBattle->toArray()));
    }

    private function getNormalizedPlayerStatuses(Player $player, string $format = null, array $context = []): array
    {
        $statuses = [];
        foreach ($player->getStatuses() as $status) {
            $normedStatus = $this->normalizer->normalize($status, $format, array_merge($context, ['player' => $player]));
            if (is_array($normedStatus) && count($normedStatus) > 0) {
                $statuses[] = $normedStatus;
            }
        }

        return $statuses;
    }

    private function getNormalizedPlayerSkills(Player $player, string $format = null, array $context = []): array
    {
        $skills = [];
        foreach ($player->getSkills() as $skill) {
            $normedSkill = $this->normalizer->normalize($skill, $format, array_merge($context, ['player' => $player]));
            if (is_array($normedSkill) && count($normedSkill) > 0) {
                $skills[] = $normedSkill;
            }
        }

        return $skills;
    }

    private function getNormalizedShootPoints(Player $player, string $language): ?array
    {
        /** @var ?ChargeStatus $shooterSkill */
        $shooterSkill = $player->getSkillByName(PlayerStatusEnum::POC_SHOOTER_SKILL);
        if ($shooterSkill === null) {
            return null;
        }

        return [
            'name' => $this->translationService->translate('shootPoint.name', [], 'player', $language),
            'description' => $this->translationService->translate('shootPoint.description', [], 'player', $language),
            'quantity' => $shooterSkill->getCharge(),
        ];
    }
}
