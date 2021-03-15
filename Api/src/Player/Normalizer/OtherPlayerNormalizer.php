<?php

namespace Mush\Player\Normalizer;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class OtherPlayerNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
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

        return $data instanceof Player && $data !== $currentPlayer;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;
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
            'statuses' => $statuses,
            'skills' => $player->getSkills(),
            'actions' => $this->getActions($player, $format, $context),
        ];
    }

    private function getActions(Player $player, ?string $format, array $context): array
    {
        $contextualActions = $this->getContextActions($context['currentPlayer']);

        $actions = [];

        /** @var Action $action */
        foreach ($player->getTargetActions() as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, array_merge($context, ['player' => $player]));
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        /** @var Action $action */
        foreach ($contextualActions as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, array_merge($context, ['player' => $player]));
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        return $actions;
    }

    private function getContextActions(Player $currentPlayer): Collection
    {
        $scope = [ActionScopeEnum::OTHER_PLAYER];

        return $this->gearToolService->getActionsTools($currentPlayer, $scope);
    }
}
