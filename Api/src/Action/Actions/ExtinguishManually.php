<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the Manual Extinguish action.
 * This action is granted by the Firefighter skill. (@TODO).
 *
 * For 1 Action Point, this action gives a 10% chance to extinguish a fire.
 *
 * More info : https://mushpedia.com/wiki/Firefighter
 */
class ExtinguishManually extends AttemptAction
{
    protected string $name = ActionEnum::EXTINGUISH_MANUALLY;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $randomService
        );

        $this->statusService = $statusService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus([
            'status' => StatusEnum::FIRE,
            'target' => HasStatus::PLAYER_ROOM,
            'groups' => ['visibility'],
        ]));
        // @TODO validator on Firefighter skill
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result instanceof Success) {
            $this->statusService->removeStatus(
                StatusEnum::FIRE,
                $this->player->getPlace(),
                $this->getAction()->getActionTags(),
                new \DateTime()
            );
        }
    }
}
