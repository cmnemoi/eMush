<?php

namespace Mush\Equipment\Normalizer;

use Mush\Action\Actions\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EquipmentNormalizer implements ContextAwareNormalizerInterface
{
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
        $actionParameter = new ActionParameters();
        if ($equipment instanceof Door) {
            $actionParameter
                ->setDoor($equipment)
            ;
        } elseif($equipment instanceof GameItem) {
            $actionParameter
                ->setItem($equipment)
            ;
        } else {
            $actionParameter
                ->setEquipment($equipment)
            ;
        }


        //Handle tools
        if (!$equipment instanceof Door){

            $place= $equipment->getRoom() ?? $equipment->getPlayer()->getRoom();
            $tools=$place->GetEquipments()
                ->filter(fn (GameEquipment $gameEquipment) =>  $gameEquipment->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL));
            foreach ($tools as $tool){
                foreach($tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions() as $actionName){
                    $actionClass = $this->actionService->getAction($actionName);
                    $actionClass->loadParameters($this->getUser()->getCurrentGame(), $actionParameter);
                    if ($actionClass instanceof Action) {
                        if ($actionClass->canExecute()) {
                            $actions[] = [
                                'key' => $actionName,
                                'name' => $this->translator->trans("{$actionName}.name", [], 'actions'),
                                'description' => $this->translator->trans("{$actionName}.description", [], 'actions'),
                                'actionPointCost' => $actionClass->getActionCost()->getActionPointCost(),
                                'movementPointCost' => $actionClass->getActionCost()->getMovementPointCost(),
                                'moralPointCost' => $actionClass->getActionCost()->getMoralPointCost(),
                            ];
                        }
                    }
                }
            };
        }
        


        foreach ($equipment->getActions() as $actionName) {
            $actionClass = $this->actionService->getAction($actionName);
            if ($actionClass instanceof Action) {
                $actionClass->loadParameters($this->getUser()->getCurrentGame(), $actionParameter);
                if ($actionClass->canExecute()) {
                    $actions[] = [
                        'key' => $actionName,
                        'name' => $this->translator->trans("{$actionName}.name", [], 'actions'),
                        'description' => $this->translator->trans("{$actionName}.description", [], 'actions'),
                        'actionPointCost' => $actionClass->getActionCost()->getActionPointCost(),
                        'movementPointCost' => $actionClass->getActionCost()->getMovementPointCost(),
                        'moralPointCost' => $actionClass->getActionCost()->getMoralPointCost(),
                    ];
                }
            }
        }


        return [
            'id' => $equipment->getId(),
            'key' => $equipment->getName(),
            'name' => $this->translator->trans($equipment->getName() . '.name', [], 'equipments'),
            'description' => $this->translator->trans("{$equipment->getName()}.description", [], 'equipments'),
            'statuses' => $equipment->getStatuses(),
            'actions' => $actions,
        ];
    }

    private function getUser(): User
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
