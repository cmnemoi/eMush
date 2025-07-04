<?php

declare(strict_types=1);

namespace Mush\Triumph\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Project\Event\ProjectEvent;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Event\TriumphSourceEventInterface;

/**
 * @template-extends ArrayCollection<int, TriumphConfig>
 */
final class TriumphConfigCollection extends ArrayCollection
{
    public function getByNameOrThrow(TriumphEnum $name): TriumphConfig
    {
        $triumph = $this->getByNameOrNull($name);

        if ($triumph === null) {
            throw new \RuntimeException("Triumph config {$name->value} not found");
        }

        return $triumph;
    }

    public function getByNameOrNull(TriumphEnum $name): ?TriumphConfig
    {
        $triumph = $this
            ->filter(static fn (TriumphConfig $triumphConfig) => $triumphConfig->getName() === $name)
            ->first();

        return $triumph === false ? null : $triumph;
    }

    public function getByProjectFinished(string $projectName): ?TriumphConfig
    {
        $triumph = $this
            ->filter(static fn (TriumphConfig $triumphConfig) => $triumphConfig->getTargetedEvent() === ProjectEvent::PROJECT_FINISHED
            && $triumphConfig->getScope() === TriumphScope::ALL_ALIVE_HUMANS
            && isset($triumphConfig->getTagConstraints()[$projectName])
            && $triumphConfig->getTagConstraints()[$projectName] === TriumphSourceEventInterface::ANY_TAG)
            ->first();

        return $triumph === false ? null : $triumph;
    }
}
