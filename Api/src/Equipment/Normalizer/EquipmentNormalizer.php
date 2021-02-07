<?php

namespace Mush\Equipment\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\Door;
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

class EquipmentNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslatorInterface $translator;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof GameEquipment;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $context = [];

        if ($object instanceof Door) {
            $context['door'] = $object;
            $type = 'door';
        } elseif ($object instanceof GameItem) {
            $context['item'] = $object;
            $type = 'items';
        } else {
            $context['equipment'] = $object;
            $type = 'equipments';
        }

        $statuses = [];
        foreach ($object->getStatuses() as $status) {
            $normedStatus = $this->normalizer->normalize($status, null, ['equipment' => $object]);
            if (is_array($normedStatus) && count($normedStatus) > 0) {
                $statuses[] = $normedStatus;
            }
        }

        return [
            'id' => $object->getId(),
            'key' => $object->getName(),
            'name' => $this->translator->trans($object->getName() . '.name', [], $type),
            'description' => $this->translator->trans("{$object->getName()}.description", [], $type),
            'statuses' => $statuses,
            'actions' => $this->getActions($object, $context),
        ];
    }

    private function getActions(GameEquipment $gameEquipment, array $context): array
    {
        $actions = [];

        $contextActions = $this->getContextActions($gameEquipment);

        /** @var Action $action */
        foreach ($contextActions as $action) {
            $normedAction = $this->normalizer->normalize($action, null, $context);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        $actionsObject = $gameEquipment->getEquipment()->getActions()
            ->filter(fn (Action $action) => $action->getScope() === ActionScopeEnum::CURRENT)
        ;

        /** @var Action $action */
        foreach ($actionsObject as $action) {
            $normedAction = $this->normalizer->normalize($action, null, $context);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        return $actions;
    }

    private function getContextActions(GameEquipment $gameEquipment): Collection
    {
        $reachableTools = $this->getPlayer()->getReachableTools();

        $scope = [ActionScopeEnum::ROOM];
        $scope[] = $gameEquipment->getPlace() ? ActionScopeEnum::SHELVE : ActionScopeEnum::INVENTORY;

        $contextActions = new ArrayCollection();
        /** @var GameEquipment $tool */
        foreach ($reachableTools as $tool) {
            $actions = $tool->getActions()->filter(fn (Action $action) => (
                in_array($action->getScope(), $scope) &&
                ($action->getTarget() === null || get_class($gameEquipment) === $action->getTarget())
            ));
            foreach ($actions as $action) {
                $contextActions->add($action);
            }
        }

        $player = $this->getPlayer();

        $actions = $player->getCharacterConfig()->getActions()->filter(fn (Action $action) => (
            in_array($action->getScope(), $scope) &&
            ($action->getTarget() === null || get_class($gameEquipment) === $action->getTarget())
        ));
        foreach ($actions as $action) {
            $contextActions->add($action);
        }

        return $contextActions;
    }

    private function getPlayer(): Player
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
