<?php

namespace Mush\Player\Normalizer;

use Mush\Action\Actions\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Normalizer\DaedalusNormalizer;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Status\Normalizer\StatusNormalizer;
use Mush\Action\Enum\ActionTargetEnum;
use Mush\Action\Enum\ActionEnum;
use Mush\Player\Entity\Player;
use Mush\Room\Normalizer\RoomNormalizer;
use Mush\Action\Normalizer\ActionNormalizer;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlayerNormalizer implements ContextAwareNormalizerInterface
{
    private DaedalusNormalizer $daedalusNormalizer;
    private RoomNormalizer $roomNormalizer;
    private TranslatorInterface $translator;
    private TokenStorageInterface $tokenStorage;
    private EquipmentNormalizer $equipmentNormalizer;
    private StatusNormalizer $statusNormalizer;
    private ActionNormalizer $actionNormalizer;
    private ActionServiceInterface $actionService;

    public function __construct(
        DaedalusNormalizer $daedalusNormalizer,
        RoomNormalizer $roomNormalizer,
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage,
        EquipmentNormalizer $equipmentNormalizer,
        StatusNormalizer $statusNormalizer,
        ActionNormalizer $actionNormalizer,
        ActionServiceInterface $actionService
    ) {
        $this->daedalusNormalizer = $daedalusNormalizer;
        $this->roomNormalizer = $roomNormalizer;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->equipmentNormalizer = $equipmentNormalizer;
        $this->statusNormalizer = $statusNormalizer;
        $this->actionNormalizer = $actionNormalizer;
        $this->actionService = $actionService;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Player;
    }

    /**
     * @param Player $player
     *
     * @return array
     */
    public function normalize($player, string $format = null, array $context = [])
    {
        $statuses=[];
        foreach($player->getStatuses() as $status){ 
            $normedStatus=$this->statusNormalizer->normalize($status, null, ['player' => $player]);
            if(count($normedStatus)>0){$statuses[] = $normedStatus;}
        }


        $actionParameter = new ActionParameters();
        $actionParameter->setPlayer($player);

        $actions=[];
        if ($this->getUser()->getCurrentGame() === $player) {
            foreach (ActionEnum::getPermanentSelfActions() as $actionName){
                $actionClass = $this->actionService->getAction($actionName);

                if ($actionClass instanceof Action){
                    $actionClass->loadParameters($this->getUser()->getCurrentGame(), $actionParameter);
                    $normedAction=$this->actionNormalizer->normalize($actionClass);
                    if(count($normedAction)>0){$actions[] = $normedAction;}
                }
            }
        }else{
            foreach (ActionEnum::getPermanentPlayerActions() as $actionName){
                $actionClass = $this->actionService->getAction($actionName);
                if ($actionClass instanceof Action){
                    $actionClass->loadParameters($this->getUser()->getCurrentGame(), $actionParameter);
                    $normedAction=$this->actionNormalizer->normalize($actionClass);
                    if(count($normedAction)>0){$actions[] = $normedAction;}
                }
            }
        }

        //Handle tools
        $tools=$this->getUser()->getCurrentGame()->getReachableTools()
            ->filter(fn (GameEquipment $gameEquipment) => 
                count($gameEquipment->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions())>0);
        
        foreach ($tools as $tool){
            if ($this->getUser()->getCurrentGame() === $player) {
                $playerActions = $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions()
                        ->filter(fn (string $actionName) => 
                        $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)
                        ->getActionsTarget()[$actionName]===ActionTargetEnum::SELF_PLAYER);
            }else{
                $playerActions = $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions()
                        ->filter(fn (string $actionName) => 
                        $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)
                        ->getActionsTarget()[$actionName]===ActionTargetEnum::TARGET_PLAYER);
            }
            foreach($playerActions as $actionName){
                $actionClass = $this->actionService->getAction($actionName);
                if ($actionClass instanceof Action){
                    $actionClass->loadParameters($this->getUser()->getCurrentGame(), $actionParameter);
                    $normedAction=$this->actionNormalizer->normalize($actionClass);
                    if(count($normedAction)>0){$actions[] = $normedAction;}
                }
            }
        };
        





        $playerPersonalInfo = [];
        if ($this->getUser()->getCurrentGame() === $player) {
            $items = [];
            /** @var GameItem $item */
            foreach ($player->getItems() as $item) {
                $items[] = $this->equipmentNormalizer->normalize($item);
            }

            $playerPersonalInfo = [
                'items' => $items,
                'actionPoint' => $player->getActionPoint(),
                'movementPoint' => $player->getMovementPoint(),
                'healthPoint' => $player->getHealthPoint(),
                'moralPoint' => $player->getMoralPoint(),
                'triumph' => $player->getTriumph()
            ];
        }

        return array_merge([
            'id' => $player->getId(),
            'character' => [
                'key' => $player->getPerson(),
                'value' => $this->translator->trans($player->getPerson() . '.name', [], 'characters'),
            ],
            'gameStatus' => $player->getGameStatus(),
            'statuses' => $statuses,
            'daedalus' => $this->daedalusNormalizer->normalize($player->getDaedalus()),
            'room' => $this->roomNormalizer->normalize($player->getRoom()),
            'skills' => $player->getSkills(),
            'actions' => $actions,
        ], $playerPersonalInfo);
    }

    private function getUser(): User
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
