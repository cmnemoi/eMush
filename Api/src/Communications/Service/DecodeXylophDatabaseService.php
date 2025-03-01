<?php

namespace Mush\Communications\Service;

use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Exception\GameException;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Entity\Player;

final class DecodeXylophDatabaseService implements DecodeXylophDatabaseServiceInterface
{
    public function __construct(
        private ModifierCreationServiceInterface $modifierCreationService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private KillLinkWithSolService $killLinkWithSol,
        private LinkWithSolRepositoryInterface $linkWithSolRepository,
        private XylophRepositoryInterface $xylophRepository,
    ) {}

    public function execute(
        XylophEntry $xylophEntry,
        Player $player,
        array $tags = [],
    ): void {
        if ($xylophEntry->IsDecoded()) {
            throw new GameException('This Xyloph database entry is already unlocked!');
        }

        $daedalus = $player->getDaedalus();

        match ($xylophEntry->getName()) {
            XylophEnum::NOTHING => null,
            XylophEnum::SNOW => $this->killLinkWithSol($daedalus->getId(), $tags),
            XylophEnum::MAGNETITE => $this->ruinLinkWithSol($daedalus->getId(), $xylophEntry->getQuantity(), $tags),
            XylophEnum::DISK => $this->createMushGenomeDisk($player->getPlace(), $tags),
            default => throw new \LogicException('undefined xyloph entry name'),
        };

        $this->markXylophDatabaseDecoded($xylophEntry);
    }

    private function markXylophDatabaseDecoded(XylophEntry $xylophEntry)
    {
        $xylophEntry->unlockDatabase();
        $this->xylophRepository->save($xylophEntry);
    }

    private function createMushGenomeDisk(EquipmentHolderInterface $holder, array $tags): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::MUSH_GENOME_DISK,
            equipmentHolder: $holder,
            reasons: $tags,
            time: new \DateTime()
        );
    }

    private function killLinkWithSol(int $daedalusId, array $tags): void
    {
        $this->killLinkWithSol->execute(
            daedalusId: $daedalusId,
            tags: $tags,
        );
    }

    private function ruinLinkWithSol(int $daedalusId, int $quantity, array $tags): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($daedalusId);
        $linkWithSol->reduceStrength($quantity);
        $this->killLinkWithSol($daedalusId, $tags);
    }
}
