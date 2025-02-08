<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Communications\Service\EstablishLinkWithSolService;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EstablishLinkWithSol extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::ESTABLISH_LINK_WITH_SOL;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private readonly EstablishLinkWithSolService $establishLinkWithSol
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->establishLinkWithSol->execute(daedalusId: $this->daedalusId(), strengthIncrease: $this->getOutputQuantity());
    }

    private function daedalusId(): int
    {
        return $this->player->getDaedalus()->getId();
    }
}
