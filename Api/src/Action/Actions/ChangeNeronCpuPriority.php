<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ChangeNeronCpuPriority extends AbstractAction
{
    protected string $name = ActionEnum::CHANGE_NERON_CPU_PRIORITY;
    private string $cpuPriority;

    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        DaedalusServiceInterface $daedalusService
    ) {
        parent::__construct($eventService, $actionService, $validator);
        $this->daedalusService = $daedalusService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {   
        if (!isset($parameters['cpuPriority'])) {
            return false;
        }

        $this->cpuPriority = $parameters['cpuPriority'];
        return $target instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(
            new HasStatus([
                'status' => PlayerStatusEnum::FOCUSED,
                'target' => HasStatus::PLAYER,
                'statusTargetName' => EquipmentEnum::BIOS_TERMINAL,
                'groups' => ['visibility'],
            ])
        );
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $daedalus = $this->player->getDaedalus();

        $daedalus->getDaedalusInfo()->getNeron()->setCpuPriority($this->cpuPriority);

        $this->daedalusService->persist($daedalus);
    }
}
