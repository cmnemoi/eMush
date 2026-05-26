<?php

declare(strict_types=1);

namespace Mush\Equipment\CycleHandler;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Equipment\Service\JukeboxService;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
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
        private RoomLogServiceInterface $roomLogService,
        private JukeboxService $jukeBoxService,
        private TranslationServiceInterface $translationService,
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
        if ($player === null) {
            throw new \UnexpectedValueException('juxebox status should not have a null value');
        }

        $language = $player->getDaedalus()->getLanguage();

        $songName = $this->translationService->translate($player->getLogName() . '.song_name', [], 'characters', $language);
        $songArtist = $this->translationService->translate($player->getLogName() . '.song_artist', [], 'characters', $language);

        $this->roomLogService->createLog(
            logKey: LogEnum::JUKEBOX_PLAYED,
            place: $jukebox->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $player,
            parameters: ['song_name' => $songName, 'song_artist' => $songArtist],
            dateTime: $dateTime,
        );
    }

    private function changeJukeboxSong(GameEquipment $jukebox): void
    {
        $daedalus = $jukebox->getDaedalus();

        $selectedPlayer = $this->jukeBoxService->getSong($daedalus, $jukebox);

        $jukebox->updateSongWithPlayerFavorite($selectedPlayer);
        $this->gameEquipmentRepository->save($jukebox);
    }
}
