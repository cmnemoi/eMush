<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Event\RebelBaseDecodedEvent;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Status\Service\StatusServiceInterface;

final readonly class DecodeRebelSignalService
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
        private ModifierCreationServiceInterface $modifierCreationService,
        private RebelBaseRepositoryInterface $rebelBaseRepository,
        private EventServiceInterface $eventService,
        private StatusServiceInterface $statusService,
    ) {}

    public function execute(RebelBase $rebelBase, int $progress, array $tags = []): void
    {
        $this->increaseDecodingProgress($rebelBase, $progress);

        if ($rebelBase->isDecoded()) {
            $this->createRebelBaseModifiers($rebelBase, $tags);
            $this->createRebelBaseStatus($rebelBase, $tags);
            $this->endRebelBaseContact($rebelBase);
        }
    }

    private function increaseDecodingProgress(RebelBase $rebelBase, int $progress): void
    {
        $rebelBase->increaseDecodingProgress($progress);
        $this->rebelBaseRepository->save($rebelBase);
    }

    private function createRebelBaseModifiers(RebelBase $rebelBase, array $tags): void
    {
        $daedalus = $this->daedalusRepository->findByIdOrThrow($rebelBase->getDaedalusId());

        foreach ($rebelBase->getModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $daedalus,
                modifierProvider: $rebelBase,
                tags: $tags,
                time: new \DateTime(),
            );
        }

        $tags[] = $rebelBase->getName()->toString();
        $this->eventService->callEvent(
            event: new RebelBaseDecodedEvent($daedalus, $tags),
            name: RebelBaseDecodedEvent::class,
        );
    }

    private function createRebelBaseStatus(RebelBase $rebelBase, array $tags): void
    {
        $statusConfig = $rebelBase->getStatusConfig();
        if ($statusConfig === null) {
            return;
        }

        $daedalus = $this->daedalusRepository->findByIdOrThrow($rebelBase->getDaedalusId());
        foreach ($daedalus->getAlivePlayers() as $player) {
            $this->statusService->createStatusFromConfig(
                statusConfig: $statusConfig,
                holder: $player,
                tags: $tags,
                time: new \DateTime(),
            );
        }
    }

    private function endRebelBaseContact(RebelBase $rebelBase): void
    {
        $rebelBase->endContact();
        $this->rebelBaseRepository->save($rebelBase);
    }
}
