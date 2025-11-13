<?php

declare(strict_types=1);

namespace Mush\Communications\Event;

use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\XylophEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

final class XylophEntryDecodedEvent extends AbstractGameEvent implements TriumphSourceEventInterface
{
    use TriumphSourceEventTrait;

    public readonly int $daedalusId;
    public readonly XylophEnum $entryName;

    public function __construct(
        private XylophEntry $xylophEntry,
        Player $author,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);

        $this->author = $author;
        $this->daedalusId = $xylophEntry->getDaedalusId();
        $this->entryName = $xylophEntry->getName();
    }

    public function getDaedalus(): Daedalus
    {
        return $this->getAuthorOrThrow()->getDaedalus();
    }

    public function getAlivePlayers(): PlayerCollection
    {
        return $this->getDaedalus()->getAlivePlayers();
    }

    public function getLanguage(): string
    {
        return $this->getDaedalus()->getLanguage();
    }
}
