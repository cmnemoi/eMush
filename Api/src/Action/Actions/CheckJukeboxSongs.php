<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\JukeboxService;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CheckJukeboxSongs extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::CHECK_JUKEBOX_SONGS;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private JukeboxService $jukeBoxService,
        private RoomLogServiceInterface $roomLogService,
        private TranslationServiceInterface $translationService
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $target = $this->gameEquipmentTarget();
        $language = $target->getDaedalus()->getLanguage();

        // get the next three songs
        $player1 = $this->jukeBoxService->getSong($target->getDaedalus(), $target, 1)->getLogName();
        $player2 = $this->jukeBoxService->getSong($target->getDaedalus(), $target, 2)->getLogName();
        $player3 = $this->jukeBoxService->getSong($target->getDaedalus(), $target, 3)->getLogName();

        $this->roomLogService->createLog(
            'check_jukebox_songs_success',
            $this->player->getPlace(),
            VisibilityEnum::PRIVATE,
            'actions_log',
            $this->player,
            [
                'song_1' => $this->getSong($player1, $language),
                'song_2' => $this->getSong($player2, $language),
                'song_3' => $this->getSong($player3, $language),
            ]
        );
    }

    private function getSong(string $player, string $language): string
    {
        return $this->translationService->translate($player . '.song_name', [], 'characters', $language);
    }
}
