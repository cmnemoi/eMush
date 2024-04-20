<?php

declare(strict_types=1);

namespace Mush\Action\Entity;

interface ActionTargetInterface
{
    public function getActionTargetName(array $context): string;
}
