<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Event\RebelBaseDecodedEvent;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Entity\Player;
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

    public function execute(RebelBase $rebelBase, int $progress, Player $author, array $tags = []): void
    {
        $this->increaseDecodingProgress($rebelBase, $progress);

        if ($rebelBase->isDecoded()) {
            $daedalus = $this->daedalusRepository->findByIdOrThrow($rebelBase->getDaedalusId());
            $this->createRebelBaseModifiersForDaedalus($rebelBase, $daedalus, $tags);
            $this->createRebelBaseStatusForDaedalus($rebelBase, $daedalus, $tags);
            $this->endRebelBaseContact($rebelBase);
            $this->eventService->callEvent(
                event: new RebelBaseDecodedEvent($rebelBase, $author, $tags),
                name: RebelBaseDecodedEvent::class,
            );
        }
    }

    private function increaseDecodingProgress(RebelBase $rebelBase, int $progress): void
    {
        $rebelBase->increaseDecodingProgress($progress);
        $this->rebelBaseRepository->save($rebelBase);
    }

    private function createRebelBaseModifiersForDaedalus(RebelBase $rebelBase, Daedalus $daedalus, array $tags): void
    {
        foreach ($rebelBase->getModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $daedalus,
                modifierProvider: $rebelBase,
                tags: $tags,
                time: new \DateTime(),
            );
        }
    }

    private function createRebelBaseStatusForDaedalus(RebelBase $rebelBase, Daedalus $daedalus, array $tags): void
    {
        $statusConfig = $rebelBase->getStatusConfig();
        if ($statusConfig === null) {
            return;
        }

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
