<?php

namespace Mush\Action\Listener;

use Mush\Action\Actions\GetUp;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Service\ActionSideEffectsServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private ActionSideEffectsServiceInterface $actionSideEffectsService;
    private GetUp $getUpAction;
    private GearToolServiceInterface $gearToolService;
    private LoggerInterface $logger;

    public function __construct(
        ActionSideEffectsServiceInterface $actionSideEffectsService,
        GetUp $getUp,
        GearToolServiceInterface $gearToolService,
        LoggerInterface $logger
    ) {
        $this->actionSideEffectsService = $actionSideEffectsService;
        $this->getUpAction = $getUp;
        $this->gearToolService = $gearToolService;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => ['onPreAction', 1],
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPreAction(ActionEvent $event): void
    {
        $action = $event->getAction();
        $player = $event->getPlayer();

        if ($action->getActionName() !== $this->getUpAction->getActionName() &&
            $lyingDownStatus = $player->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $getUpAction = $player->getPlayerInfo()->getCharacterConfig()->getActionByName(ActionEnum::GET_UP);

            if ($getUpAction === null) {
                $errorMessage = "ActionSubscriber::onPreAction: character do not have get up action";
                $this->logger->error($errorMessage,
                    [   
                        'daedalus' => $player->getDaedalus()->getId(),
                        'player' => $player->getId(),
                    ]
                );
                throw new \LogicException($errorMessage);
            }

            $this->getUpAction->loadParameters($getUpAction, $player);
            $this->getUpAction->execute();
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        $action = $event->getAction();
        $player = $event->getPlayer();
        $actionParameter = $event->getActionParameter();

        $this->actionSideEffectsService->handleActionSideEffect($action, $player, new \DateTime());

        $this->gearToolService->applyChargeCost($player, $action->getActionName(), $action->getTypes());

        if ($actionParameter instanceof Player &&
            in_array($action->getActionName(), ActionEnum::getForceGetUpActions()) &&
            $lyingDownStatus = $actionParameter->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $actionParameter->removeStatus($lyingDownStatus);
        }
    }
}
