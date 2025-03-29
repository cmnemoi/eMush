<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Entity\TradeAsset;
use Mush\Communications\Enum\TradeAssetEnum;
use Mush\Communications\Event\TradeAssetsCreatedEvent;
use Mush\Communications\Repository\TradeOptionRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Service\FinishProjectService;
use Mush\Project\Service\FinishRandomDaedalusProjectsService;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;

final readonly class CreateOfferedTradeAssetsService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private FinishRandomDaedalusProjectsService $finishRandomDaedalusProjects,
        private FinishProjectService $finishProject,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private RoomLogServiceInterface $roomLogService,
        private TradeOptionRepositoryInterface $tradeOptionRepository
    ) {}

    public function execute(Player $trader, int $tradeOptionId): void
    {
        $tradeOption = $this->tradeOptionRepository->findByIdOrThrow($tradeOptionId);

        $daedalus = $trader->getDaedalus();

        foreach ($tradeOption->getOfferedAssets() as $offeredAsset) {
            $assetQuantity = $offeredAsset->getQuantity();

            match ($offeredAsset->getType()) {
                TradeAssetEnum::DAEDALUS_VARIABLE => $this->createDaedalusVariableAssetsInPlayerRoom($offeredAsset, $trader),
                TradeAssetEnum::ITEM => $this->createItemAssetsInPlayerRoom($offeredAsset, $trader),
                TradeAssetEnum::RANDOM_PROJECT => $this->finishRandomDaedalusProjects->execute(daedalusId: $daedalus->getId(), quantity: $assetQuantity),
                TradeAssetEnum::SPECIFIC_PROJECT => $this->finishSpecificProject($offeredAsset, $daedalus),
                default => throw new \RuntimeException('Unhandled trade asset type: ' . $offeredAsset->getType()->value),
            };
        }

        $this->eventService->callEvent(event: new TradeAssetsCreatedEvent($daedalus), name: TradeAssetsCreatedEvent::class);
    }

    private function createItemAssetsInPlayerRoom(TradeAsset $offeredAsset, Player $trader): void
    {
        $equipment = $this->gameEquipmentService->createGameEquipmentsFromName(
            equipmentName: $offeredAsset->getAssetName(),
            equipmentHolder: $trader->getPlace(),
            quantity: $offeredAsset->getQuantity(),
            author: $trader,
        );

        $this->roomLogService->createLog(
            logKey: LogEnum::TRADE_ASSETS_CREATED,
            place: $trader->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            parameters: [
                $equipment[0]->getLogKey() => $equipment[0]->getLogName(),
                'quantity' => $offeredAsset->getQuantity(),
            ],
        );
    }

    private function finishSpecificProject(TradeAsset $offeredAsset, Daedalus $daedalus): void
    {
        $projectName = ProjectName::from($offeredAsset->getAssetName());
        $project = $daedalus->getProjectByName($projectName);

        $this->finishProject->execute($project);
    }

    private function createDaedalusVariableAssetsInPlayerRoom(TradeAsset $offeredAsset, Player $trader): void
    {
        $equipment = $this->gameEquipmentService->createGameEquipmentsFromName(
            equipmentName: DaedalusVariableEnum::toOfferedTradeItem($offeredAsset->getAssetName()),
            equipmentHolder: $trader->getPlace(),
            quantity: $offeredAsset->getQuantity(),
            author: $trader,
        );

        $this->roomLogService->createLog(
            logKey: LogEnum::TRADE_ASSETS_CREATED,
            place: $trader->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            parameters: [
                $equipment[0]->getLogKey() => $equipment[0]->getLogName(),
                'quantity' => $offeredAsset->getQuantity(),
            ],
        );
    }
}
