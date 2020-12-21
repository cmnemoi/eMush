<?php

namespace Mush\Equipment\Normalizer;

use Mush\Action\Enum\ActionTargetEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
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
    private ActionServiceInterface $actionService;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        TranslatorInterface $translator,
        ActionServiceInterface $actionService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->translator = $translator;
        $this->actionService = $actionService;
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
        $actions = [];

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

        //@TODO this is awfully messy
        //Handle tools
        $tools = $this->getPlayer()->getReachableTools()
            ->filter(
                function (GameEquipment $gameEquipment) {
                    /** @var Tool $tool */
                    $tool = $gameEquipment->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL);
                    !$tool->getGrantActions()->isEmpty();
                }
            )
        ;

        foreach ($tools as $tool) {
            if ($object instanceof Door) {
                $itemActions = $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions()
                    ->filter(fn (string $actionName) => $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)
                            ->getActionsTarget()[$actionName] === ActionTargetEnum::DOOR);
            } elseif ($object instanceof GameItem) {
                $itemActions = $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions()
                    ->filter(fn (string $actionName) => $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)
                            ->getActionsTarget()[$actionName] === ActionTargetEnum::ITEM ||
                        $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)
                            ->getActionsTarget()[$actionName] === ActionTargetEnum::EQUIPMENT);
            } else {
                $itemActions = $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions()
                    ->filter(fn (string $actionName) => $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)
                            ->getActionsTarget()[$actionName] === ActionTargetEnum::EQUIPMENT);
            }

            foreach ($itemActions as $actionName) {
                $actionClass = $this->actionService->getAction($actionName);
                if ($actionClass) {
                    $normedAction = $this->normalizer->normalize($actionClass, null, $context);
                    if (is_array($normedAction) && count($normedAction) > 0) {
                        $actions[] = $normedAction;
                    }
                }
            }
        }

        foreach ($object->getActions() as $actionName) {
            $actionClass = $this->actionService->getAction($actionName);
            if ($actionClass) {
                $normedAction = $this->normalizer->normalize($actionClass, null, $context);
                if (is_array($normedAction) && count($normedAction) > 0) {
                    $actions[] = $normedAction;
                }
            }
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
            'actions' => $actions,
        ];
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
