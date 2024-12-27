<?php

namespace Mush\Hunter\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\EventEnum;

class HunterPoolEvent extends AbstractHunterEvent
{
    public const UNPOOL_HUNTERS = 'unpool.hunters';

    public function __construct(Daedalus $daedalus, array $tags, \DateTime $time)
    {
        parent::__construct($daedalus, $tags, $time);
    }

    public function shouldNotGenerateNeronAnnouncement(): bool
    {
        return $this->daedalus->getAttackingHunters()->isEmpty() || $this->hasTag(EventEnum::CREATE_DAEDALUS);
    }
}
