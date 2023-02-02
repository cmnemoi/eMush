<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Service\StatusServiceInterface;

abstract class AbstractChargeStrategy
{
    protected string $name;
    protected StatusServiceInterface $statusService;

    public function __construct(StatusServiceInterface $statusService)
    {
        $this->statusService = $statusService;
    }

    public function execute(ChargeStatus $status, array $reasons): ?ChargeStatus
    {
        $status = $this->apply($status, $reasons);

        return $status;
    }

    abstract protected function apply(ChargeStatus $status, array $reasons): ?ChargeStatus;

    public function getName(): string
    {
        return $this->name;
    }
}
