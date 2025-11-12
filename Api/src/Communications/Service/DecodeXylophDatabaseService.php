<?php

namespace Mush\Communications\Service;

use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Event\XylophEntryDecodedEvent;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Exception\GameException;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final readonly class DecodeXylophDatabaseService implements DecodeXylophDatabaseServiceInterface
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
        private EventServiceInterface $eventService,
        private ModifierCreationServiceInterface $modifierCreationService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private KillLinkWithSolService $killLinkWithSol,
        private LinkWithSolRepositoryInterface $linkWithSolRepository,
        private PrintDocumentServiceInterface $printDocumentService,
        private RoomLogServiceInterface $roomLogService,
        private RandomServiceInterface $randomService,
        private StatusServiceInterface $statusService,
        private TranslationServiceInterface $translationService,
        private XylophRepositoryInterface $xylophRepository,
        private UpdateNeronVersionService $updateNeronVersionService,
    ) {}

    public function execute(
        XylophEntry $xylophEntry,
        Player $player,
        array $tags = [],
    ): void {
        if ($xylophEntry->isDecoded()) {
            throw new GameException('This Xyloph database entry is already unlocked!');
        }

        $daedalus = $player->getDaedalus();

        $this->createXylophDecodedLog($xylophEntry->getName(), $player);

        match ($xylophEntry->getName()) {
            XylophEnum::BLUEPRINTS => $this->printXylophDocument($player, $xylophEntry, $tags),
            XylophEnum::COOK => $this->printXylophDocument($player, $xylophEntry, $tags),
            XylophEnum::DISK => $this->createMushGenomeDisk($player->getPlace(), $tags),
            XylophEnum::GHOST_CHUN => $this->createDaedalusStatus($daedalus, DaedalusStatusEnum::GHOST_CHUN, $tags),
            XylophEnum::GHOST_SAMPLE => $this->createDaedalusStatus($daedalus, DaedalusStatusEnum::GHOST_SAMPLE, $tags),
            XylophEnum::KIVANC => $this->createXylophModifiers($daedalus, $xylophEntry, $tags),
            XylophEnum::LIST => $this->printXylophDocument($player, $xylophEntry, $tags),
            XylophEnum::MAGE_BOOKS => $this->printXylophDocument($player, $xylophEntry, $tags),
            XylophEnum::MAGNETITE => $this->ruinLinkWithSol($daedalus->getId(), $xylophEntry->getQuantity(), $tags),
            XylophEnum::NOTHING => null,
            XylophEnum::SNOW => $this->killLinkWithSol($daedalus->getId(), $tags),
            XylophEnum::VERSION => $this->increaseNeronVersion($daedalus->getId(), $xylophEntry->getQuantity()),
            default => throw new \LogicException('undefined xyloph entry name'),
        };

        $tags[] = $xylophEntry->getName()->toString();
        $this->eventService->callEvent(
            event: new XylophEntryDecodedEvent($xylophEntry, $player, $tags),
            name: XylophEntryDecodedEvent::class,
        );
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

    private function printXylophDocument(Player $player, XylophEntry $entry, array $tags): void
    {
        $tabulatrix = $player->getPlace()->getEquipmentByName(EquipmentEnum::TABULATRIX);

        if (!$tabulatrix) {
            return;
        }

        $queue = $player->getDaedalus()->getTabulatrixQueue();

        match ($entry->getName()) {
            XylophEnum::BLUEPRINTS => $this->receiveBlueprints($queue, $entry->getQuantity(), $tags),
            XylophEnum::COOK => $this->receiveChefBook($queue, $tags),
            XylophEnum::LIST => $this->receiveLostResearch($queue, $entry->getQuantity(), $tags),
            XylophEnum::MAGE_BOOKS => $this->receiveMageBooks($queue, $entry->getQuantity(), $tags),
            default => throw new \LogicException('received an entry unrelated to printing'),
        };

        if ($tabulatrix->isNotOperational()) {
            return;
        }

        $this->printDocumentService->execute($tabulatrix, $tags);
    }

    private function receiveChefBook(Place $queue, array $tags)
    {
        $this->queueDocumentOfName('apprentron_chef', $queue, $tags);
    }

    private function receiveMageBooks(Place $queue, int $quantity, array $tags)
    {
        $mageBookNames = $this->randomService->getRandomElementsFromProbaCollection(
            array: $queue->getDaedalus()->getDaedalusConfig()->getStartingApprentrons(),
            number: $quantity
        );
        foreach ($mageBookNames as $mageBook) {
            $this->queueDocumentOfName($mageBook, $queue, $tags);
        }
    }

    private function receiveBlueprints(Place $queue, int $quantity, array $tags)
    {
        $daedalus = $queue->getDaedalus();
        $availableBlueprints = $daedalus->getDaedalusConfig()->getRandomBlueprints()->withdrawElements($daedalus->getUniqueItems()->getStartingBlueprints());
        $selectedBlueprints = $this->randomService->getRandomElementsFromProbaCollection($availableBlueprints, $quantity);
        foreach ($selectedBlueprints as $blueprint) {
            $this->queueDocumentOfName($blueprint, $queue, $tags);
        }
    }

    private function receiveLostResearch(Place $queue, int $negativePercent, array $tags)
    {
        $document = $this->queueDocumentOfName(ItemEnum::DOCUMENT, $queue, $tags);
        $this->statusService->createContentStatus(
            content: $this->translatedList($queue->getDaedalus(), $negativePercent),
            holder: $document,
            tags: $tags,
        );
    }

    private function translatedList(Daedalus $daedalus, int $negativePercent): string
    {
        $players = $daedalus->getPlayers();

        $translatedContent = $this->translationService->translate(
            key: 'lost_research_headline',
            parameters: [],
            domain: 'event_log',
            language: $daedalus->getLanguage()
        );

        foreach ($players as $player) {
            $translatedPlayer = $this->translationService->translate(
                key: \sprintf('%s.name', $player->getLogName()),
                parameters: [],
                domain: 'characters',
                language: $daedalus->getLanguage()
            );

            $translatedSample = $this->translationService->translate(
                key: 'lost_research_sample',
                parameters: [
                    'character' => $translatedPlayer,
                    'is_negative' => $this->isNegativeResult($player, $negativePercent),
                ],
                domain: 'event_log',
                language: $daedalus->getLanguage()
            );

            $translatedContent = $translatedContent . '//' . $translatedSample;
        }

        return $translatedContent;
    }

    private function isNegativeResult(Player $player, int $negativePercent): string
    {
        if ($player->isAlphaMush()) {
            return 'false';
        }

        return $this->randomService->isSuccessful($negativePercent) ? 'true' : 'false';
    }

    private function queueDocumentOfName(string $documentName, Place $queue, array $tags): GameEquipment
    {
        if ($queue->getType() !== PlaceTypeEnum::QUEUE) {
            throw new \LogicException('the type should be queue');
        }

        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $documentName,
            equipmentHolder: $queue,
            reasons: $tags,
            time: new \DateTime()
        );
    }
}
