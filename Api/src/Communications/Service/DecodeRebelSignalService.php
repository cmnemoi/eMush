<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Modifier\Service\ModifierCreationServiceInterface;

final readonly class DecodeRebelSignalService
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
        private ModifierCreationServiceInterface $modifierCreationService,
        private RebelBaseRepositoryInterface $rebelBaseRepository,
    ) {}

    public function execute(RebelBase $rebelBase, int $progress, array $tags = []): void
    {
        $this->increaseDecodingProgress($rebelBase, $progress);

        if ($rebelBase->isDecoded()) {
            $this->createRebelBaseModifiers($rebelBase, $tags);
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
        foreach ($rebelBase->getModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $this->daedalusRepository->findByIdOrThrow($rebelBase->getDaedalusId()),
                modifierProvider: $rebelBase,
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
