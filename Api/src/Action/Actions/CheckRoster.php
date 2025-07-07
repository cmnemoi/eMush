<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Entity\RoomLogTableContent;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerActivityLevelEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckRoster extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::CHECK_ROSTER;
    protected GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        protected RoomLogServiceInterface $roomLogService,
        protected TranslationServiceInterface $translationService,
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    public function translatePlayerName(string $character, string $language): string
    {
        return $this->translationService->translate(
            $character . '.name',
            [],
            'characters',
            $language
        );
    }

    public function translateActivityLevel(string $activity, string $language): string
    {
        return $this->translationService->translate(
            $activity . '.name',
            [],
            'status',
            $language
        );
    }

    public function getPlayerActivityLevel(Player $player): string
    {
        if ($player->isDead()) {
            return PlayerActivityLevelEnum::DEAD->value;
        }
        if ($player->isInactive()) {
            return PlayerActivityLevelEnum::IDLE->value;
        }

        return PlayerActivityLevelEnum::AWAKE->value;
    }

    public function createCrewmemberRow(string $character, string $activity, string $language): array
    {
        if ($character === '???') {
            $characterName = $character;
        } else {
            $characterName = $this->translatePlayerName($character, $language);
        }

        $characterActivity = $this->translateActivityLevel($activity, $language);

        return [$characterName, $characterActivity];
    }

    public function createRosterTable(): array
    {
        $daedalus = $this->gameEquipmentTarget()->getDaedalus();
        $language = $daedalus->getLanguage();
        $playerCount = $daedalus->getDaedalusConfig()->getPlayerCount();
        $players = $daedalus->getPlayers()->getSortedBy('createdAt');
        $currentlyJoinedPlayers = $players->count();
        $tableContent = new RoomLogTableContent();

        for ($character = 1; $character <= $playerCount; ++$character) {
            // First, let's create the rows for the players that have already joined the Daedalus, and update the counter after each
            if ($character <= $currentlyJoinedPlayers) {
                foreach ($players as $player) {
                    $playerName = $player->getName();
                    $playerActivity = $this->getPlayerActivityLevel($player);

                    $playerRow = $this->createCrewmemberRow($playerName, $playerActivity, $language);
                    $tableContent->addOneEntry($playerRow);
                    ++$character;
                }
            }

            // Then, let's create rows for the missing players, if any
            if ($character <= $playerCount) {
                $ghostName = '???';
                $ghostActivity = PlayerActivityLevelEnum::CRYOGENIZED->value;

                $ghostRow = $this->createCrewmemberRow($ghostName, $ghostActivity, $language);
                $tableContent->addOneEntry($ghostRow);
            }
        }

        return $tableContent->toArray();
    }

    public function createRosterLog(): void
    {
        $tableContent = $this->createRosterTable();

        $this->roomLogService->createTableLog(
            logKey: '',
            place: $this->gameEquipmentTarget()->getPlace(),
            visibility: VisibilityEnum::PRIVATE,
            type: '',
            player: $this->player,
            parameters: [],
            dateTime: new \DateTime(),
            tableLog: $tableContent,
        );
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->createRosterLog();
    }
}
