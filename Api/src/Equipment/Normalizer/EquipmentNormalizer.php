<?php

namespace Mush\Equipment\Normalizer;

use Mush\Action\Enum\ActionTargetEnum;
use Mush\Action\Normalizer\ActionNormalizer;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Status\Normalizer\StatusNormalizer;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EquipmentNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;
    private ActionServiceInterface $actionService;
    private TokenStorageInterface $tokenStorage;
    private StatusNormalizer $statusNormalizer;
    private ActionNormalizer $actionNormalizer;

    public function __construct(
        TranslatorInterface $translator,
        ActionServiceInterface $actionService,
        TokenStorageInterface $tokenStorage,
        StatusNormalizer $statusNormalizer,
        ActionNormalizer $actionNormalizer
    ) {
        $this->translator = $translator;
        $this->actionService = $actionService;
        $this->tokenStorage = $tokenStorage;
        $this->statusNormalizer = $statusNormalizer;
        $this->actionNormalizer = $actionNormalizer;
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
        } elseif ($object instanceof GameItem) {
            $context['item'] = $object;
        } else {
            $context['equipment'] = $object;
        }

        //@TODO this is awfully messy
        //Handle tools
        $tools = $this->getUser()->getCurrentGame()->getReachableTools()
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
                    $normedAction = $this->actionNormalizer->normalize($actionClass, null, $context);
                    if (count($normedAction) > 0) {
                        $actions[] = $normedAction;
                    }
                }
            }
        }

        foreach ($object->getActions() as $actionName) {
            $actionClass = $this->actionService->getAction($actionName);
            if ($actionClass) {
                $normedAction = $this->actionNormalizer->normalize($actionClass, null, $context);
                if (count($normedAction) > 0) {
                    $actions[] = $normedAction;
                }
            }
        }

        $statuses = [];
        foreach ($object->getStatuses() as $status) {
            $normedStatus = $this->statusNormalizer->normalize($status, null, ['equipment' => $object]);
            if (count($normedStatus) > 0) {
                $statuses[] = $normedStatus;
            }
        }

        return [
            'id' => $object->getId(),
            'key' => $object->getName(),
            'name' => $this->translator->trans($object->getName() . '.name', [], 'equipments'),
            'description' => $this->translator->trans("{$object->getName()}.description", [], 'equipments'),
            'statuses' => $statuses,
            'actions' => $actions,
        ];
    }

    private function getUser(): User
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        return $user;
    }
}
