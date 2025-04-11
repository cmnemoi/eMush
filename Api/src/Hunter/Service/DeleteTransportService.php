<?php

declare(strict_types=1);

namespace Mush\Hunter\Service;

use Mush\Communications\Repository\TradeRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Event\MerchantLeaveEvent;
use Mush\Hunter\Repository\HunterRepositoryInterface;
use Mush\Hunter\Repository\HunterTargetRepositoryInterface;

final readonly class DeleteTransportService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private HunterRepositoryInterface $hunterRepository,
        private HunterTargetRepositoryInterface $hunterTargetRepository,
        private TradeRepositoryInterface $tradeRepository,
    ) {}

    public function byTradeOptionId(int $tradeOptionId): void
    {
        $transport = $this->hunterRepository->findByTradeOptionIdOrThrow($tradeOptionId);
        $this->tradeRepository->deleteByTradeOptionId($tradeOptionId);

        $this->deleteTransport($transport);
    }

    public function byTradeId(int $tradeId): void
    {
        $transport = $this->hunterRepository->findByTradeIdOrThrow($tradeId);
        $this->tradeRepository->deleteByTransportId($transport->getId());

        $this->deleteTransport($transport);
    }

    public function byId(int $id, array $tags = []): void
    {
        $transport = $this->hunterRepository->findByIdOrThrow($id);
        $this->tradeRepository->deleteByTransportId($transport->getId());

        $this->deleteTransport($transport, $tags);
    }

    private function deleteTransport(Hunter $transport, array $tags = []): void
    {
        $this->deleteTargetsInvolvingTransport($transport);
        $this->hunterRepository->delete($transport);

        $this->eventService->callEvent(
            event: new MerchantLeaveEvent($transport->getDaedalus(), $tags),
            name: MerchantLeaveEvent::class,
        );
    }

    private function deleteTargetsInvolvingTransport(Hunter $hunter): void
    {
        $hunterTargets = $this->hunterTargetRepository->findAllBy(['hunter' => $hunter]);

        foreach ($hunterTargets as $hunterTarget) {
            $owner = $this->hunterRepository->findOneByTargetOrThrow($hunterTarget);
            $owner->resetTarget();
            $this->hunterRepository->save($owner);
        }
    }
}
