<?php

namespace Mush\Player\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class CurrentPlayerNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TokenStorageInterface $tokenStorage;
    private TranslatorInterface $translator;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Player && $data === $this->getUserPlayer();
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;

        $items = [];
        /** @var GameItem $item */
        foreach ($player->getItems() as $item) {
            $items[] = $this->normalizer->normalize($item);
        }

        $statuses = [];
        foreach ($player->getStatuses() as $status) {
            $normedStatus = $this->normalizer->normalize($status, null, ['player' => $player]);
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
            'daedalus' => $this->normalizer->normalize($object->getDaedalus()),
            'room' => $this->normalizer->normalize($object->getPlace()),
            'skills' => $player->getSkills(),
            'actions' => $this->getActions($object),
            'items' => $items,
            'statuses' => $statuses,
            'actionPoint' => $player->getActionPoint(),
            'movementPoint' => $player->getMovementPoint(),
            'healthPoint' => $player->getHealthPoint(),
            'moralPoint' => $player->getMoralPoint(),
            'triumph' => $player->getTriumph(),
        ];
    }

    private function getActions(Player $player): array
    {
        $contextualActions = $this->getContextActions($player);

        $actions = [];

        /** @var Action $action */
        foreach ($player->getCharacterConfig()->getActions() as $action) {
            if ($action->getScope() === ActionScopeEnum::SELF) {
                $normedAction = $this->normalizer->normalize($action);
                if (is_array($normedAction) && count($normedAction) > 0) {
                    $actions[] = $normedAction;
                }
            }
        }

        /** @var Action $action */
        foreach ($contextualActions as $action) {
            $normedAction = $this->normalizer->normalize($action);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        return $actions;
    }

    private function getContextActions(Player $player): Collection
    {
        $reachableTools = $player->getReachableTools();

        $scope = [ActionScopeEnum::SELF];

        $contextActions = new ArrayCollection();
        /** @var GameEquipment $tool */
        foreach ($reachableTools as $tool) {
            $actions = $tool->getActions()->filter(fn (Action $action) => (
            in_array($action->getScope(), $scope))
            );
            foreach ($actions as $action) {
                $contextActions->add($action);
            }
        }

        return $contextActions;
    }

    private function getUserPlayer(): Player
    {
        if (!$token = $this->tokenStorage->getToken()) {
            throw new AccessDeniedException('User should be logged to access that');
        }

        /** @var User $user */
        $user = $token->getUser();

        if (!$player = $user->getCurrentGame()) {
            throw new AccessDeniedException('User should be in game to access that');
        }

        return $player;
    }
}
