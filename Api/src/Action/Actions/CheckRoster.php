<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Entity\Daedalus;
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

final class CheckRoster extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::CHECK_ROSTER;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private RoomLogServiceInterface $roomLogService,
        private TranslationServiceInterface $translationService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
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
        $this->roomLogService->createTableLog(
            table: $this->createRosterTable(),
            place: $this->player->getPlace(),
            visibility: VisibilityEnum::PRIVATE,
            player: $this->player,
        );
    }

    private function createRosterTable(): RoomLogTableContent
    {
        $daedalus = $this->player->getDaedalus();
        $language = $this->player->getLanguage();
        $awakenPlayers = $daedalus->getPlayers()->getSortedBy('createdAt');

        $table = new RoomLogTableContent();

        foreach ($awakenPlayers as $player) {
            $table->addEntry($this->createCrewmemberRow($player, $language));
        }

        $this->addDaedalusCryogenizedPlayersToTable($daedalus, $table);

        return $table;
    }

    private function addDaedalusCryogenizedPlayersToTable(Daedalus $daedalus, RoomLogTableContent $table): void
    {
        $missingSlots = $daedalus->getDaedalusConfig()->getPlayerCount() - $daedalus->getPlayers()->count();

        for ($i = 0; $i < $missingSlots; ++$i) {
            $table->addEntry($this->createCrewmemberRow(Player::createNull(), $daedalus->getLanguage()));
        }
    }

    private function createCrewmemberRow(Player $player, string $language): array
    {
        $characterName = $this->translatePlayerName($player, $language);
        $characterActivity = $this->translateActivityLevel($player, $language);

        return [$characterName, $characterActivity];
    }

    private function translatePlayerName(Player $player, string $language): string
    {
        if ($player->isNull()) {
            return '???';
        }

        return $this->translationService->translate(
            $player->getLogName() . '.name',
            [],
            'characters',
            $language
        );
    }

    private function translateActivityLevel(Player $player, string $language): string
    {
        return $this->translationService->translate(
            $this->getPlayerActivityLevel($player),
            [$player->getLogKey() => $player->getLogName()],
            'misc',
            $language,
        );
    }

    private function getPlayerActivityLevel(Player $player): string
    {
        return match (true) {
            $player->isNull() => PlayerActivityLevelEnum::CRYOGENIZED->value,
            $player->isDead() => PlayerActivityLevelEnum::DEAD->value,
            $player->isInactive() => PlayerActivityLevelEnum::INACTIVE->value,
            default => PlayerActivityLevelEnum::AWAKE->value,
        };
    }
}
