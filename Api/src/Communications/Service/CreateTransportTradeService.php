<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Event\TradeCreatedEvent;
use Mush\Communications\Repository\TradeRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Repository\HunterRepositoryInterface;

/**
 * @psalm-suppress InvalidArgument
 */
final readonly class CreateTransportTradeService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private GenerateTradeInterface $generateTrade,
        private HunterRepositoryInterface $hunterRepository,
        private TradeRepositoryInterface $tradeRepository,
    ) {}

    public function execute(int $transportId, \DateTime $time): void
    {
        $hunter = $this->hunterRepository->findByIdOrThrow($transportId);

        $this->throwIfHunterIsNotATransport($hunter);

        $trade = $this->generateTrade->execute($hunter);
        $this->tradeRepository->save($trade);

        $this->eventService->callEvent(
            event: new TradeCreatedEvent($hunter->getDaedalus(), time: $time),
            name: TradeCreatedEvent::class
        );
    }

    private function throwIfHunterIsNotATransport(Hunter $hunter): void
    {
        if ($hunter->getHunterConfig()->getHunterName() !== HunterEnum::TRANSPORT) {
            throw new \InvalidArgumentException('Cannot create trade for non-transport hunter');
        }
    }
}
