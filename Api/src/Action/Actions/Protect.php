<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Implement Protect Action from Skill Bodyguard. Add a status to both user and target. When they are in the same room, all aggressive actions toward the target cost 2 extra AP.
 */
class Protect extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::PROTECT;

    public function __construct(
        protected EventServiceInterface $eventService,
        protected ActionServiceInterface $actionService,
        protected ValidatorInterface $validator,
        protected StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(
            new PlaceType([
                'type' => PlaceTypeEnum::ROOM,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::NOT_A_ROOM,
            ])
        );
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
        if ($this->alreadyHaveTarget()) {
            $this->removeOldStatus();
        }

        $this->addNewStatus();
    }

    private function alreadyHaveTarget(): bool
    {
        return $this->getPlayer()->hasStatus(PlayerStatusEnum::BODYGUARD_USER);
    }

    private function removeOldStatus(): void
    {
        $target = $this->getPlayer()->getStatusByNameOrThrow(PlayerStatusEnum::BODYGUARD_USER)->getTargetOrThrow();
        $this->statusService->removeStatus(PlayerStatusEnum::BODYGUARD_VIP, $target, $this->getTags(), new \DateTime());
        $this->statusService->removeStatus(PlayerStatusEnum::BODYGUARD_USER, $this->getPlayer(), $this->getTags(), new \DateTime());
    }

    private function addNewStatus(): void
    {
        /**
         * @var Player $target
         */
        $target = $this->getTarget();
        $this->statusService->createStatusFromName(PlayerStatusEnum::BODYGUARD_USER, $this->player, $this->getTags(), new \DateTime(), $target);
        $this->statusService->createStatusFromName(PlayerStatusEnum::BODYGUARD_VIP, $target, $this->getTags(), new \DateTime(), $this->player);
    }
}
