<?php

namespace Mush\Communications\Service;

use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Exception\GameException;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final readonly class DecodeXylophDatabaseService implements DecodeXylophDatabaseServiceInterface
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
        private ModifierCreationServiceInterface $modifierCreationService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private KillLinkWithSolService $killLinkWithSol,
        private LinkWithSolRepositoryInterface $linkWithSolRepository,
        private PrintDocumentServiceInterface $printDocumentService,
        private RoomLogServiceInterface $roomLogService,
        private StatusServiceInterface $statusService,
        private XylophRepositoryInterface $xylophRepository,
        private UpdateNeronVersionService $updateNeronVersionService,
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

        $this->createXylophDecodedLog($xylophEntry->getName(), $player);

        match ($xylophEntry->getName()) {
            XylophEnum::COOK => $this->printChefBook($player, $tags),
            XylophEnum::DISK => $this->createMushGenomeDisk($player->getPlace(), $tags),
            XylophEnum::GHOST_CHUN => $this->createDaedalusStatus($daedalus, DaedalusStatusEnum::GHOST_CHUN, $tags),
            XylophEnum::GHOST_SAMPLE => $this->createDaedalusStatus($daedalus, DaedalusStatusEnum::GHOST_SAMPLE, $tags),
            XylophEnum::KIVANC => $this->createXylophModifiers($daedalus, $xylophEntry, $tags),
            XylophEnum::MAGNETITE => $this->ruinLinkWithSol($daedalus->getId(), $xylophEntry->getQuantity(), $tags),
            XylophEnum::NOTHING => null,
            XylophEnum::SNOW => $this->killLinkWithSol($daedalus->getId(), $tags),
            XylophEnum::VERSION => $this->increaseNeronVersion($daedalus->getId(), $xylophEntry->getQuantity()),
            default => throw new \LogicException('undefined xyloph entry name'),
        };

        $this->markXylophDatabaseDecoded($xylophEntry);
    }

    private function markXylophDatabaseDecoded(XylophEntry $xylophEntry)
    {
        $xylophEntry->unlockDatabase();
        $this->xylophRepository->save($xylophEntry);
    }

    private function createXylophDecodedLog(XylophEnum $xylophEnum, Player $player): void
    {
        $logKey = 'xyloph_decoded_' . $xylophEnum->toString();
        $visibility = VisibilityEnum::PRIVATE;
        if (XylophEnum::requiresPrinting($xylophEnum)) {
            $tabulatrix = $player->getPlace()->getEquipmentByName(EquipmentEnum::TABULATRIX);
            if (!$tabulatrix) {
                $logKey = 'xyloph_decoded_tabulatrix_none';
                $visibility = VisibilityEnum::PUBLIC;
            } elseif ($tabulatrix->hasStatus(EquipmentStatusEnum::BROKEN)) {
                $logKey = 'xyloph_decoded_tabulatrix_broken';
                $visibility = VisibilityEnum::PUBLIC;
            }
        }

        $this->roomLogService->createLog(
            logKey: $logKey,
            place: $player->getPlace(),
            visibility: $visibility,
            type: 'xyloph_log',
            player: $player,
            parameters: [],
            dateTime: new \DateTime()
        );
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

    private function increaseNeronVersion(int $daedalusId, int $quantity): void
    {
        $this->updateNeronVersionService->execute(
            daedalusId: $daedalusId,
            fixedIncrement: $quantity,
        );
    }

    private function createDaedalusStatus(Daedalus $daedalus, string $status, array $tags): void
    {
        $this->statusService->createStatusFromName(
            statusName: $status,
            holder: $daedalus,
            tags: $tags,
            time: new \DateTime(),
        );
    }

    private function createXylophModifiers(Daedalus $daedalus, XylophEntry $entry, array $tags): void
    {
        foreach ($entry->getModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $daedalus,
                modifierProvider: $entry,
                tags: $tags,
                time: new \DateTime(),
            );
        }
    }

    private function printChefBook(Player $player, array $tags): void
    {
        $tabulatrix = $player->getPlace()->getEquipmentByName(EquipmentEnum::TABULATRIX);

        if (!$tabulatrix) {
            return;
        }

        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: 'apprentron_chef',
            equipmentHolder: $player->getDaedalus()->getTabulatrixQueue(),
            reasons: $tags,
            time: new \DateTime()
        );

        if ($tabulatrix->isNotOperational()) {
            return;
        }

        $this->printDocumentService->execute($tabulatrix, $tags);
    }
}
