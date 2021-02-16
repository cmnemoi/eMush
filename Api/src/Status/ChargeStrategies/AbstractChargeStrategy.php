<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Daedalus\Entity\Daedalus;
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

    public function execute(ChargeStatus $status, Daedalus $daedalus): ?ChargeStatus
    {
        $status = $this->apply($status, $daedalus);

        return $status;
    }

    abstract protected function apply(ChargeStatus $status, Daedalus $daedalus): ?ChargeStatus;

    public function getName(): string
    {
        return $this->name;
    }
}
