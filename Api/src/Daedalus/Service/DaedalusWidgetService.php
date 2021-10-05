<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Status\Enum\StatusEnum;

class DaedalusWidgetService implements DaedalusWidgetServiceInterface
{
    public const HULL_ALERT = 33;

    public function getMinimap(Daedalus $daedalus): array
    {
        $minimap = [];
        foreach ($daedalus->getRooms() as $room) {
            $minimap[$room->getName()] = [
                'players' => $room->getPlayers()->count(),
                'fire' => $room->getStatusByName(StatusEnum::FIRE) !== null,
            ];
        }

        return $minimap;
    }
}
