<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\OperationalDoorInRoom;
use Mush\Action\Validator\PlaceType;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlaceStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PutThroughDoor extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::PUT_THROUGH_DOOR;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private RandomServiceInterface $randomService,
        private PlayerServiceInterface $playerService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new PlaceType([
                'groups' => [ClassConstraint::VISIBILITY],
                'type' => 'room',
            ]),
            new HasStatus([
                'status' => PlaceStatusEnum::CEASEFIRE->toString(),
                'target' => HasStatus::PLAYER_ROOM,
                'contain' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::CEASEFIRE,
            ]),
            new OperationalDoorInRoom([
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::NO_WORKING_DOOR,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Player $targetPlayer */
        $targetPlayer = $this->target;

        $this->playerService->changePlace(player: $targetPlayer, place: $this->getRandomRoom());
    }

    private function getRandomRoom(): Place
    {
        $accessibleRooms = $this->player->getAccessibleRooms();

        return $this->randomService->getRandomElement($accessibleRooms->toArray());
    }
}
