<?php
namespace Mush\Situation\Service;

use Mush\Action\ActionResult\ActionResult;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameter;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Situation\Entity\Situation;

interface SituationServiceInterface
{
    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ?Situation;
}