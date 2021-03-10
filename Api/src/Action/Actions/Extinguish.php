<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Status;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Extinguish extends AttemptAction
{
    protected string $name = ActionEnum::EXTINGUISH;

    /** @var GameEquipment */
    protected $parameter;

    private PlayerServiceInterface $playerService;
    private PlaceServiceInterface $placeService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        PlaceServiceInterface $placeService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator,
            $randomService,
        );

        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->placeService = $placeService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    protected static function addConstraints(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Status(['status' => StatusEnum::FIRE, 'target' => Status::PLAYER_ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Status([
            'status' => EquipmentStatusEnum::BROKEN, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        $response = $this->makeAttempt();

        if ($response instanceof Success &&
            ($fireStatus = $this->player->getPlace()->getStatusByName(StatusEnum::FIRE))
        ) {
            $this->player->getPlace()->removeStatus($fireStatus);
            $this->placeService->persist($this->player->getPlace());
        }

        $this->playerService->persist($this->player);

        return $response;
    }
}
