<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AreShowersDismantled;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WashInSink extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::WASH_IN_SINK;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        protected StatusServiceInterface $statusService,
        private RandomServiceInterface $randomService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach([
            'reach' => ReachEnum::ROOM,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new AreShowersDismantled([
            'groups' => ['visibility'],
        ]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        return $this->player->shouldBeHurtByShower() ? new Fail() : new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->statusService->removeStatus(
            PlayerStatusEnum::DIRTY,
            $this->player,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );

        if ($result->isAFail()) {
            $this->handleWaterDamage();
        }
    }

    private function handleWaterDamage()
    {
        $damageProbaCollection = $this->getGameEquipmentActionProvider()->getPlumbingMechanicOrThrow()->getWaterDamage();
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection($damageProbaCollection);

        $playerVariableEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $this->getTags(),
            new \DateTime(),
        );
        $playerVariableEvent->setVisibility(VisibilityEnum::PRIVATE);
        $playerVariableEvent->addTag(EndCauseEnum::INJURY);

        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
