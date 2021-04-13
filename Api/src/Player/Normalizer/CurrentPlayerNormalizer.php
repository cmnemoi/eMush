<?php

namespace Mush\Player\Normalizer;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class CurrentPlayerNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslatorInterface $translator;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        TranslatorInterface $translator,
        GearToolServiceInterface $gearToolService
    ) {
        $this->translator = $translator;
        $this->gearToolService = $gearToolService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        $currentPlayer = $context['currentPlayer'] ?? null;

        return $data instanceof Player && $data === $currentPlayer && $data->getGameStatus() === GameStatusEnum::CURRENT;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;

        $items = [];
        /** @var GameItem $item */
        foreach ($player->getItems() as $item) {
            $items[] = $this->normalizer->normalize($item, $format, $context);
        }

        $statuses = [];
        foreach ($player->getStatuses() as $status) {
            $normedStatus = $this->normalizer->normalize($status, $format, array_merge($context, ['player' => $player]));
            if (is_array($normedStatus) && count($normedStatus) > 0) {
                $statuses[] = $normedStatus;
            }
        }

        $character = $player->getCharacterConfig()->getName();

        return [
            'id' => $player->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translator->trans($character . '.name', [], 'characters'),
            ],
            'daedalus' => $this->normalizer->normalize($object->getDaedalus(), $format, $context),
            'room' => $this->normalizer->normalize($object->getPlace(), $format, $context),
            'skills' => $player->getSkills(),
            'actions' => $this->getActions($object, $format, $context),
            'items' => $items,
            'statuses' => $statuses,
            'actionPoint' => $player->getActionPoint(),
            'movementPoint' => $player->getMovementPoint(),
            'healthPoint' => $player->getHealthPoint(),
            'moralPoint' => $player->getMoralPoint(),
            'triumph' => $player->getTriumph(),
        ];
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
