<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\MushDamage;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\Collection\ProbaCollection;
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

/** Class implementing a generic Wash action.
 * Should not be used directly -- see Shower, WashInSink, and WashWithPerfume classes instead.
 */
abstract class AbstractWashSelfAction extends AbstractAction
{
    // $name needs to be set by child classes

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        protected StatusServiceInterface $statusService,
        private RandomServiceInterface $randomService
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        // extra validators should be added to the child classes, not here
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    public function getTags(): array
    {
        $tags = parent::getTags();

        $tags[] = 'wash_self';

        return $tags;
    }

    protected function checkResult(): ActionResult
    {
        return $this->player->shouldBeHurtByShower() ? new MushDamage() : new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($this->player->hasStatus(PlayerStatusEnum::DIRTY)) {
            $this->statusService->removeStatus(
                PlayerStatusEnum::DIRTY,
                $this->player,
                $this->actionConfig->getActionTags(),
                new \DateTime()
            );
        }

        if ($this->player->shouldBeHurtByShower()) {
            $this->handleWaterDamage();
        }
    }

    private function handleWaterDamage()
    {
        $damageProbaCollection = new ProbaCollection([
            3 => 1,
            4 => 1,
        ]);
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
