<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "Public Broadcast" action (watching the TV Alien).
 * This action is granted by the Alien Holographic TV.
 *
 * For 2 PA, "Public Broadcast" gives 3 Morale Points
 * to all the players in the room, if they haven't
 * watched it before.
 *
 * More info : http://www.mushpedia.com/wiki/Alien_Holographic_TV
 */
class PublicBroadcast extends AbstractAction
{
    protected string $name = ActionEnum::PUBLIC_BROADCAST;
    protected const BASE_CONFORT = 3;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator
    ) {
        parent::__construct($eventDispatcher, $actionService, $validator);
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Item;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::BROKEN,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
    }

    private function addMoralPoints(Player $player, int $morale_points): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            $morale_points,
            $this->getActionName(),
            new \DateTime()
        );

        $playerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }

    private function addWatchedPublicBroadcastStatus(Player $player): void
    {
        $statusEvent = new StatusEvent(
            PlayerStatusEnum::WATCHED_PUBLIC_BROADCAST,
            $player,
            $this->getActionName(),
            new \DateTime()
        );

        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $playersInTheRoom = $this->player
                                    ->getPlace()
                                    ->getPlayers();

        foreach ($playersInTheRoom as $player) {
            $alreadyWatchedPublicBroadcast = $player->getStatusbyName(PlayerStatusEnum::WATCHED_PUBLIC_BROADCAST);

            if ($alreadyWatchedPublicBroadcast) {
                continue;
            }

            $this->addMoralPoints($player, self::BASE_CONFORT);
            $this->addWatchedPublicBroadcastStatus($player);
        }
    }
}
