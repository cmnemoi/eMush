<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Entity\Trade;
use Mush\Communications\Entity\TradeAsset;
use Mush\Communications\Entity\TradeOption;
use Mush\Communications\Enum\TradeAssetEnum;
use Mush\Communications\Enum\TradeEnum;
use Mush\Communications\Repository\TradeRepositoryInterface;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Repository\HunterRepositoryInterface;

/**
 * @psalm-suppress InvalidArgument
 */
final readonly class CreateTransportTradeService
{
    public function __construct(
        private HunterRepositoryInterface $hunterRepository,
        private TradeRepositoryInterface $tradeRepository,
    ) {}

    public function execute(int $transportId): void
    {
        $transport = $this->hunterRepository->findByIdOrThrow($transportId);

        if ($transport->getHunterConfig()->getHunterName() !== HunterEnum::TRANSPORT) {
            throw new \InvalidArgumentException('Cannot create trade for non-transport hunter');
        }

        // @TODO: randomize trade name
        // @TODO: randomize trade asset quantity

        $randomPlayerAsset = new TradeAsset(
            type: TradeAssetEnum::RANDOM_PLAYER,
            quantity: 1,
        );
        $oxygenAsset = new TradeAsset(
            type: TradeAssetEnum::DAEDALUS_VARIABLE,
            assetName: DaedalusVariableEnum::OXYGEN,
            quantity: 10,
        );
        $tradeOption = new TradeOption(
            requiredAssets: [$randomPlayerAsset],
            offeredAssets: [$oxygenAsset],
        );

        $trade = new Trade(
            name: TradeEnum::HUMAN_VS_OXY,
            tradeOptions: [$tradeOption],
            transportId: $transport->getId()
        );
        $this->tradeRepository->save($trade);
    }
}
