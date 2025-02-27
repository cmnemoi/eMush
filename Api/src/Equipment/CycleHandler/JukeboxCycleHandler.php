<?php

declare(strict_types=1);

namespace Mush\Equipment\CycleHandler;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;

final class JukeboxCycleHandler extends AbstractCycleHandler
{
    protected string $name = EquipmentEnum::JUKEBOX;

    public function __construct(
        private EventServiceInterface $eventService,
        private GameEquipmentRepositoryInterface $gameEquipmentRepository,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
        private RoomLogServiceInterface $roomLogService,
    ) {}

    public function handleNewCycle(GameEquipment $gameEquipment, \DateTime $dateTime): void
    {
        if ($this->isNotJukebox($gameEquipment) || $gameEquipment->isNotOperational()) {
            return;
        }

        $jukebox = $gameEquipment;
        $this->changeJukeboxSong($jukebox);
        $jukeboxPlayer = $jukebox->getCurrentJukeboxPlayer();
        if ($jukeboxPlayer?->canReachEquipment($jukebox)) {
            $this->applyJukeboxMoraleGainToPlayer($jukeboxPlayer, $dateTime);
        }
        $this->createJukeboxPlayedLog($jukebox, $dateTime);
    }

    public function handleNewDay(GameEquipment $gameEquipment, \DateTime $dateTime): void {}

    private function isNotJukebox(GameEquipment $gameEquipment): bool
    {
        return $gameEquipment->getName() !== $this->name;
    }

    private function applyJukeboxMoraleGainToPlayer(Player $player, \DateTime $dateTime): void
    {
        $moraleGain = $player->getDaedalus()->getProjectByName(ProjectName::BEAT_BOX)->getActivationRate();
        $playerVariableEvent = new PlayerVariableEvent(
            $player,
            variableName: PlayerVariableEnum::MORAL_POINT,
            quantity: $moraleGain,
            tags: [],
            time: $dateTime,
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function createJukeboxPlayedLog(GameEquipment $jukebox, \DateTime $dateTime): void
    {
        $player = $jukebox->getCurrentJukeboxPlayer();
        $this->roomLogService->createLog(
            logKey: LogEnum::JUKEBOX_PLAYED,
            place: $jukebox->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $player,
            parameters: ['player' => $player?->getLogName()],
            dateTime: $dateTime,
        );
    }

    private function changeJukeboxSong(GameEquipment $jukebox): void
    {
        $daedalus = $jukebox->getDaedalus();
        $players = $daedalus->getPlayers();
        $jukeboxPlayer = $jukebox->getCurrentJukeboxPlayer();

        if ($this->areThereNoOtherCandidates($jukeboxPlayer, $players)) {
            return;
        }

        $candidatePlayers = $jukeboxPlayer ? $players->getAllExcept($jukeboxPlayer)->toArray() : $players->toArray();
        $selectedPlayer = $this->getRandomElementsFromArray->execute($candidatePlayers, 1)->first();

        $jukebox->updateSongWithPlayerFavorite($selectedPlayer);
        $this->gameEquipmentRepository->save($jukebox);
    }

    private function areThereNoOtherCandidates(?Player $currentJukeboxPlayer, PlayerCollection $players): bool
    {
        return $players->first() === $currentJukeboxPlayer && $players->count() < 2;
    }
}
