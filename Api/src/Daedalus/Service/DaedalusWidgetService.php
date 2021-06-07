<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DaedalusWidgetService implements DaedalusWidgetServiceInterface
{
    public const HULL_ALERT = 33;

    private TranslatorInterface $translator;
    private StatusServiceInterface $statusService;

    public function __construct(TranslatorInterface $translator, StatusServiceInterface $statusService)
    {
        $this->translator = $translator;
        $this->statusService = $statusService;
    }

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
