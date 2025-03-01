<?php

namespace Mush\Communications\Service;

use Mush\Communications\Entity\XylophEntry;
use Mush\Player\Entity\Player;

interface DecodeXylophDatabaseServiceInterface
{
    public function execute(
        XylophEntry $xylophEntry,
        Player $player,
        array $tags = [],
    ): void;
}
