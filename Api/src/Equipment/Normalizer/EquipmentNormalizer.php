<?php

namespace Mush\Equipment\Normalizer;

use Mush\Action\Actions\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionTargetEnum;
use Mush\Action\Normalizer\ActionNormalizer;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
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

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof GameEquipment;
    }

    /**
     * @param GameEquipment $equipment
     *
     * @return array
     */
    public function normalize($equipment, string $format = null, array $context = [])
    {
        $actions = [];

        $context=[];
        if ($equipment instanceof Door) {
            $context['door']=$equipment;
        } elseif ($equipment instanceof GameItem) {
            $context['item']=$equipment;
        } else {
            $context['equipment']=$equipment;
        }

        //@TODO this is awfully messy
        //Handle tools
        $tools = $this->getUser()->getCurrentGame()->getReachableTools()
            ->filter(fn (GameEquipment $gameEquipment) => count($gameEquipment->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions()) > 0);

        foreach ($tools as $tool) {
            if ($equipment instanceof Door) {
                $itemActions = $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions()
                                    ->filter(fn (string $actionName) => $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)
                                    ->getActionsTarget()[$actionName] === ActionTargetEnum::DOOR);
            }elseif ($equipment instanceof GameItem) {
                $itemActions = $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions()
                                    ->filter(fn (string $actionName) => $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)
                                    ->getActionsTarget()[$actionName] === ActionTargetEnum::ITEM ||
                                    $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)
                                    ->getActionsTarget()[$actionName] === ActionTargetEnum::EQUIPMENT);
            }else{
                $itemActions = $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions()
                                    ->filter(fn (string $actionName) => $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)
                                    ->getActionsTarget()[$actionName] === ActionTargetEnum::EQUIPMENT);
            }

            foreach ($itemActions as $actionName) {
                $actionClass = $this->actionService->getAction($actionName);              
                if($actionClass){
                    $normedAction = $this->actionNormalizer->normalize($actionClass, null, $context);
                    if (count($normedAction) > 0) {$actions[] = $normedAction;}
                }
            }
        }

        foreach ($equipment->getActions() as $actionName) {
            $actionClass = $this->actionService->getAction($actionName);              
            if($actionClass){
                $normedAction = $this->actionNormalizer->normalize($actionClass, null, $context);
                if (count($normedAction) > 0) {$actions[] = $normedAction;}
            }
        }

        $statuses = [];
        foreach ($equipment->getStatuses() as $status) {
            $normedStatus = $this->statusNormalizer->normalize($status, null, ['equipment' => $equipment]);
            if (count($normedStatus) > 0) {
                $statuses[] = $normedStatus;
            }
        }

        return [
            'id' => $equipment->getId(),
            'key' => $equipment->getName(),
            'name' => $this->translator->trans($equipment->getName() . '.name', [], 'equipments'),
            'description' => $this->translator->trans("{$equipment->getName()}.description", [], 'equipments'),
            'statuses' => $statuses,
            'actions' => $actions,
        ];
    }

    private function getUser(): User
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
