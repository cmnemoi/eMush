<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ReadDocument extends AbstractAction
{
    protected string $name = ActionEnum::READ_DOCUMENT;

    /** @var GameItem */
    protected $parameter;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadVisibilityValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Mechanic(['mechanic' => EquipmentMechanicEnum::DOCUMENT]));
        $metadata->addConstraint(new Reach());
    }

    protected function applyEffects(): ActionResult
    {
        return new Success();
    }
}
