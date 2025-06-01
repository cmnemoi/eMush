<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Service;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;

/**
 * @internal
 */
final class ChangeTriumphFromEventServiceCest extends AbstractFunctionalTest
{
    private ChangeTriumphFromEventService $changeTriumphFromEventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->changeTriumphFromEventService = $I->grabService(ChangeTriumphFromEventService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function shouldPrintLogWhenTriumphIsChanged(FunctionalTester $I): void
    {
        $event = new DaedalusCycleEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $event->setEventName(DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $this->changeTriumphFromEventService->execute($event);

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Vous avez gagné 1 :triumph: car vous avez survécu un cycle de plus.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: TriumphEnum::CYCLE_HUMAN->toString(),
                visibility: VisibilityEnum::PRIVATE,
                inPlayerRoom: true,
            ),
            I: $I,
        );
    }

    public function shouldUseAlteredLogKey(FunctionalTester $I): void
    {
        $cat = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $event = new InteractWithEquipmentEvent(
            equipment: $cat,
            author: $this->player,
            visibility: VisibilityEnum::PUBLIC,
            tags: [ActionEnum::SHOOT_CAT->value, EquipmentStatusEnum::CAT_INFECTED],
            time: new \DateTime(),
        );
        $event->setEventName(EquipmentEvent::EQUIPMENT_DESTROYED);

        // When event fitting MUSHICIDE_CAT config is executed
        $this->changeTriumphFromEventService->execute($event);

        // Then MUSHICIDE triumph log is generated
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => TriumphEnum::MUSHICIDE->toString(),
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );

        // Then MUSHICIDE_CAT triumph log is not generated
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => TriumphEnum::MUSHICIDE_CAT->toString(),
            ]
        );
    }
}
