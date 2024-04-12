<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

abstract class AbstractChargeStrategy
{
    protected string $name;
    protected StatusServiceInterface $statusService;

    public function __construct(StatusServiceInterface $statusService)
    {
        $this->statusService = $statusService;
    }

    public function execute(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus
    {
        $statusHolder = $status->getOwner();
        if (
            $status->getName() === EquipmentStatusEnum::ELECTRIC_CHARGES
            && $statusHolder->hasStatus(EquipmentStatusEnum::BROKEN)
        ) {
            return null;
        }

        return $this->apply($status, $reasons, $time);
    }

    public function getName(): string
    {
        return $this->name;
    }

    abstract protected function apply(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus;
}
