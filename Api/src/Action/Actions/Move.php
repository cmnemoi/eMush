<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\CanGoToIcarusBay;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Move extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::MOVE;

    private PlayerServiceInterface $playerService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new CanGoToIcarusBay(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::CANNOT_GO_TO_THIS_ROOM]));
        $metadata->addConstraint(new PlaceType(['groups' => ['visible'], 'type' => 'room']));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Door;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Door $target */
        $target = $this->target;

        $newRoom = $target->getOtherRoom($this->player->getPlace());

        // @TODO: use PlayerService::changePlace instead.
        // /!\ You need to delete all treatments in Modifier::ActionSubscriber before! /!\
        $this->player->changePlace($newRoom);
        $this->playerService->persist($this->player);
    }
}
