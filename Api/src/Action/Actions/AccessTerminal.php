<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AccessTerminal extends AbstractAction
{
    protected string $name = ActionEnum::ACCESS_TERMINAL;
    protected GameEquipmentServiceInterface $gameEquipmentService;

    private PlayerServiceInterface $playerService;
    private PlaceServiceInterface $placeService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        PlaceServiceInterface $placeService,
        RandomServiceInterface $randomService,
        GameEquipmentServiceInterface $gameEquipmentService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
        $this->placeService = $placeService;
        $this->randomService = $randomService;

        $this->gameEquipmentService = $gameEquipmentService;

    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        // TODO : REVALIDATE THOSE ONE
//        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
//        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::BROKEN, 'contain' => false, 'groups' => ['visibility']]));
//        $metadata->addConstraint(new PlaceType(['groups' => ['visibility'], 'type' => PlaceTypeEnum::ROOM]));
//        $metadata->addConstraint(new HasStatus([
//            'status' => DaedalusStatusEnum::TRAVELING,
//            'contain' => false,
//            'target' => HasStatus::DAEDALUS,
//            'groups' => ['execute'],
//            'message' => ActionImpossibleCauseEnum::DAEDALUS_TRAVELING,
//        ]));
    }

    protected function applyEffect(ActionResult $result): void
    {

        $this->gameEquipmentService->createGameEquipmentFromName(
            GameRationEnum::COFFEE,
            $this->player,
            $this->getAction()->getActionTags(),
            new \DateTime()
        );

//        /** @var GameEquipment $patrolship */
//        $patrolship = $this->target;
//
//        $patrolshipRoom = $this->placeService->findByNameAndDaedalus($patrolship->getName(), $this->player->getDaedalus());
//        if ($patrolshipRoom === null) {
//            throw new \RuntimeException('Patrol ship room not found');
//        }
//
//        // @TODO: use PlayerService::changePlace instead.
//        // /!\ You need to delete all treatments in Modifier::ActionSubscriber before! /!\
//        $this->player->changePlace($patrolshipRoom);
//        $this->playerService->persist($this->player);
//
//        $equipmentEvent = new MoveEquipmentEvent(
//            equipment: $patrolship,
//            newHolder: $patrolshipRoom,
//            author: $this->player,
//            visibility: VisibilityEnum::HIDDEN,
//            tags: $this->getAction()->getActionTags(),
//            time: new \DateTime(),
//        );
//        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::CHANGE_HOLDER);
    }
}
