<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Specification\Mechanic;
use Mush\Action\Specification\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    protected function getVisibilitySpecifications(): array
    {
        return [
            Mechanic::class => [Mechanic::PARAMETER_KEY => EquipmentMechanicEnum::DOCUMENT],
            Reach::class => null,
        ];
    }

    protected function applyEffects(): ActionResult
    {
        return new Success();
    }
}
