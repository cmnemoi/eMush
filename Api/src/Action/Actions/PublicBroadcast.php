<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
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
    protected ActionEnum $name = ActionEnum::PUBLIC_BROADCAST;

    protected StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['visible'], 'type' => 'room']));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
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

            $this->addMoralPoints($player, $this->getOutputQuantity());
            $this->addWatchedPublicBroadcastStatus($player);
        }
    }

    private function addMoralPoints(Player $player, int $morale_points): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            $morale_points,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );

        $playerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function addWatchedPublicBroadcastStatus(Player $player): void
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::WATCHED_PUBLIC_BROADCAST,
            $player,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );
    }
}
