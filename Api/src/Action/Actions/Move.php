<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\CanGoToIcarusBay;
use Mush\Action\Validator\Guardian;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
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
        $metadata->addConstraint(new Guardian(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::CANNOT_GO_TO_THIS_ROOM_BECAUSE_GUARDIAN]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Door;
    }

    public function getTags(): array
    {
        $tags = parent::getTags();

        $daedalus = $this->player->getDaedalus();
        if ($daedalus->hasStatus(DaedalusStatusEnum::NO_GRAVITY) || $daedalus->hasStatus(DaedalusStatusEnum::NO_GRAVITY_REPAIRED)) {
            $tags[] = DaedalusStatusEnum::NO_GRAVITY;
        }

        return $tags;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Door $door */
        $door = $this->target;

        $newRoom = $door->getOtherRoom($this->player->getPlace());

        $this->playerService->changePlace($this->player, $newRoom);
    }
}
