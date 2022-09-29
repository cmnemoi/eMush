<?php

namespace Mush\Player\Normalizer;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class CurrentPlayerNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private PlayerServiceInterface $playerService;
    private PlayerVariableServiceInterface $playerVariableService;
    private TranslationServiceInterface $translationService;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        PlayerServiceInterface $playerService,
        PlayerVariableServiceInterface $playerVariableService,
        TranslationServiceInterface $translationService,
        GearToolServiceInterface $gearToolService
    ) {
        $this->playerService = $playerService;
        $this->playerVariableService = $playerVariableService;
        $this->translationService = $translationService;
        $this->gearToolService = $gearToolService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        $currentPlayer = $context['currentPlayer'] ?? null;

        return $data instanceof Player && $data === $currentPlayer;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;

        $items = [];
        /** @var Item $item */
        foreach ($player->getEquipments() as $item) {
            $items[] = $this->normalizer->normalize($item, $format, $context);
        }

        $character = $player->getCharacterConfig()->getName();

        $playerData = [
            'id' => $player->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translationService->translate($character . '.name', [], 'characters'),
            ],
            'gameStatus' => $player->getGameStatus(),
            'triumph' => $player->getTriumph(),
            'daedalus' => $this->normalizer->normalize($object->getDaedalus(), $format, $context),
        ];

        if ($player->getGameStatus() === GameStatusEnum::CURRENT) {
            $statuses = [];
            foreach ($player->getStatuses() as $status) {
                $normedStatus = $this->normalizer->normalize($status, $format, array_merge($context, ['player' => $player]));
                if (is_array($normedStatus) && count($normedStatus) > 0) {
                    $statuses[] = $normedStatus;
                }
            }

            $diseases = [];
            foreach ($player->getMedicalConditions()->getActiveDiseases() as $disease) {
                $normedDisease = $this->normalizer->normalize($disease, $format, array_merge($context, ['player' => $player]));
                if (is_array($normedDisease) && count($normedDisease) > 0) {
                    $diseases[] = $normedDisease;
                }
            }

            $playerData = array_merge($playerData, [
                'room' => $this->normalizer->normalize($object->getPlace(), $format, $context),
                'skills' => $player->getSkills(),
                'actions' => $this->getActions($object, $format, $context),
                'items' => $items,
                'statuses' => $statuses,
                'diseases' => $diseases,
                'actionPoint' => [
                    'quantity' => $player->getActionPoint(),
                    'max' => $this->playerVariableService->getMaxPlayerVariable($player, PlayerVariableEnum::ACTION_POINT),
                    'name' => $this->translationService->translate('actionPoint.name', [], 'player'),
                    'description' => $this->translationService->translate('actionPoint.description', [
                        'quantityaction' => $player->getActionPoint(),
                        'quantitymovement' => $player->getMovementPoint(),
                    ], 'player'), ],
                'movementPoint' => [
                    'quantity' => $player->getMovementPoint(),
                    'max' => $this->playerVariableService->getMaxPlayerVariable($player, PlayerVariableEnum::MOVEMENT_POINT),
                    ],
                'healthPoint' => [
                    'quantity' => $player->getHealthPoint(),
                    'max' => $this->playerVariableService->getMaxPlayerVariable($player, PlayerVariableEnum::HEALTH_POINT),
                    'name' => $this->translationService->translate('healthPoint.name', ['quantity' => $player->getHealthPoint()], 'player'),
                    'description' => $this->translationService->translate('healthPoint.description', [], 'player'), ],
                'moralPoint' => [
                    'quantity' => $player->getMoralPoint(),
                    'max' => $this->playerVariableService->getMaxPlayerVariable($player, PlayerVariableEnum::MORAL_POINT),
                    'name' => $this->translationService->translate('moralPoint.name', ['quantity' => $player->getMoralPoint()], 'player'),
                    'description' => $this->translationService->translate('moralPoint.description', [], 'player'), ],
            ]);
        }

        return $playerData;
    }

    private function getActions(Player $player, ?string $format, array $context): array
    {
        $contextualActions = $this->getContextActions($player);

        $actions = [];

        /** @var Action $action */
        foreach ($player->getSelfActions() as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        /** @var Action $action */
        foreach ($contextualActions as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        return $actions;
    }

    private function getContextActions(Player $player): Collection
    {
        $scope = [ActionScopeEnum::SELF];

        return $this->gearToolService->getActionsTools($player, $scope);
    }
}
