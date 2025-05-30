<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\TestDoubles;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

/**
 * @internal
 */
final class TriumphSourceEventTraitTestClass extends AbstractGameEvent implements TriumphSourceEventInterface
{
    use TriumphSourceEventTrait;

    public function __construct(array $tags)
    {
        parent::__construct($tags, new \DateTime());
    }

    public function getDaedalus(): Daedalus
    {
        return DaedalusFactory::createDaedalus();
    }

    public function getEventName(): string
    {
        return 'test';
    }
}
