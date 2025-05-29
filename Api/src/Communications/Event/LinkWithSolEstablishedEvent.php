<?php

declare(strict_types=1);

namespace Mush\Communications\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

final class LinkWithSolEstablishedEvent extends AbstractGameEvent implements TriumphSourceEventInterface
{
    use TriumphSourceEventTrait;

    public const string FIRST_CONTACT = 'first_contact';
    public readonly int $daedalusId;

    public function __construct(private readonly Daedalus $daedalus, array $tags = [], \DateTime $time = new \DateTime())
    {
        $this->daedalusId = $daedalus->getId();
        parent::__construct($tags, $time);

        if ($daedalus->doesNotHaveStatus(DaedalusStatusEnum::LINK_WITH_SOL_ESTABLISHED_ONCE)) {
            $this->addTag(self::FIRST_CONTACT);
        }
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }
}
