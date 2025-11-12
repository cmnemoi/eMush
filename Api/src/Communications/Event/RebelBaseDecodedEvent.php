<?php

declare(strict_types=1);

namespace Mush\Communications\Event;

use Mush\Communications\Entity\RebelBase;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusStatistics;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

final class RebelBaseDecodedEvent extends AbstractGameEvent implements TriumphSourceEventInterface
{
    use TriumphSourceEventTrait;

    public readonly int $daedalusId;

    public function __construct(
        private RebelBase $rebelBase,
        Player $author,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ) {
        $this->author = $author;
        $this->daedalusId = $rebelBase->getDaedalusId();
        parent::__construct($tags, $time);
        $this->addTag($rebelBase->getName()->toString());
    }

    public function getDaedalus(): Daedalus
    {
        return $this->getAuthorOrThrow()->getDaedalus();
    }

    public function getLanguage(): string
    {
        return $this->getAuthorOrThrow()->getLanguage();
    }

    public function getDaedalusStatistics(): DaedalusStatistics
    {
        return $this->getDaedalus()->getDaedalusInfo()->getDaedalusStatistics();
    }

    public function getAuthorUserId(): int
    {
        return $this->getAuthorOrThrow()->getUser()->getId();
    }
}
