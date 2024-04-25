<?php

declare(strict_types=1);

namespace Mush\Project\Exception;

final class ProjetEventShouldHaveAnAuthorException extends \LogicException
{
    public function __construct(string $projetName)
    {
        parent::__construct("Project event for project {$projetName} should have an author.");
    }
}
