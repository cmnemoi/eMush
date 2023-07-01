<?php

declare(strict_types=1);

namespace Mush\Action\Listener;

use Mush\Action\ActionResult\Fail;
use Mush\Action\Actions\GetUp;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Service\ActionSideEffectsServiceInterface;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\EquipmentMechanic as Mechanic;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\PatrolShip;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionSubscriber implements EventSubscriberInterface
{
    private ActionSideEffectsServiceInterface $actionSideEffectsService;
    private EventServiceInterface $eventService;
    private GetUp $getUpAction;
    private GearToolServiceInterface $gearToolService;
    private RandomServiceInterface $randomService;

    public function __construct(
        ActionSideEffectsServiceInterface $actionSideEffectsService,
        EventServiceInterface $eventService,
        GetUp $getUp,
        GearToolServiceInterface $gearToolService,
        RandomServiceInterface $randomService
    ) {
        $this->actionSideEffectsService = $actionSideEffectsService;
        $this->eventService = $eventService;
        $this->getUpAction = $getUp;
        $this->gearToolService = $gearToolService;
        $this->randomService = $randomService;
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
        $player = $event->getAuthor();

        if ($action->getActionName() !== $this->getUpAction->getActionName() &&
            $lyingDownStatus = $player->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $getUpAction = $player->getPlayerInfo()->getCharacterConfig()->getActionByName(ActionEnum::GET_UP);

            if ($getUpAction === null) {
                throw new \LogicException('character do not have get up action');
            }

            $this->getUpAction->loadParameters($getUpAction, $player);
            $this->getUpAction->execute();
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        $action = $event->getAction();
        $player = $event->getAuthor();
        $actionParameter = $event->getActionParameter();

        $this->actionSideEffectsService->handleActionSideEffect($action, $player, $actionParameter);
        $this->gearToolService->applyChargeCost($player, $action->getActionName(), $action->getTypes());
        $player->getDaedalus()->addDailyActionPointsSpent($action->getActionCost());

        if ($actionParameter instanceof Player &&
            in_array($action->getActionName(), ActionEnum::getForceGetUpActions()) &&
            $lyingDownStatus = $actionParameter->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $actionParameter->removeStatus($lyingDownStatus);
        }

        if ($this->eventTagsContainsPatrolshipAction($event->getTags())
            && $event->getActionResult() instanceof Fail
        ) {
            $this->handlePatrolshipManoeuvreDamage($event);
        }
    }

    private function eventTagsContainsPatrolshipAction(array $tags): bool
    {
        $patrolshipActions = ActionEnum::getChangingRoomPatrolshipActions();

        foreach ($patrolshipActions as $patrolshipAction) {
            if (in_array($patrolshipAction, $tags)) {
                return true;
            }
        }

        return false;
    }

    private function handlePatrolshipManoeuvreDamage(ActionEvent $event): void
    {
        $this->inflictDamageToDaedalus($event);
        $this->inflictDamageToPlayer($event);
    }

    private function inflictDamageToDaedalus(ActionEvent $event): void
    {
        $patrolShipMechanic = $this->getPatrolShipMechanic($event);
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection(
            $patrolShipMechanic->getFailedManoeuvreDaedalusDamage()
        );
        if (!$damage) {
            throw new \LogicException('Patrolship failed manoeuvre damage should not be 0');
        }

        $daedalusVariableModifierEvent = new DaedalusVariableEvent(
            $event->getAuthor()->getDaedalus(),
            DaedalusVariableEnum::HULL,
            -$damage,
            $event->getTags(),
            new \DateTime(),
        );

        $this->eventService->callEvent($daedalusVariableModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function inflictDamageToPlayer(ActionEvent $event): void
    {
        $patrolShipMechanic = $this->getPatrolShipMechanic($event);
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection(
            $patrolShipMechanic->getFailedManoeuvrePlayerDamage()
        );
        if (!$damage) {
            throw new \LogicException('Player failed manoeuvre damage should not be 0');
        }

        $playerModifierEvent = new PlayerVariableEvent(
            $event->getAuthor(),
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $event->getTags(),
            new \DateTime(),
        );

        $playerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function getPatrolShipMechanic(ActionEvent $event): PatrolShip
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $event->getActionParameter();
        /** @var PatrolShip $patrolShipMechanic */
        $patrolShipMechanic = $patrolShip->getEquipment()->getMechanics()->filter(fn (Mechanic $mechanic) => in_array(EquipmentMechanicEnum::PATROL_SHIP, $mechanic->getMechanics()))->first();

        return $patrolShipMechanic;
    }
}
