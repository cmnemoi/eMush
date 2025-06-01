<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\ExchangeBody;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class ExchangeBodyCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ExchangeBody $exchangeBody;

    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private Player $source;
    private Player $target;

    private User $oldMushUser;
    private User $oldHumanUser;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::EXCHANGE_BODY]);
        $this->exchangeBody = $I->grabService(ExchangeBody::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->source = $this->player;
        $this->target = $this->player2;
        $this->oldMushUser = $this->source->getUser();
        $this->oldHumanUser = $this->target->getUser();

        $this->givenSourcePlayerIsMush();
        $this->givenTargetPlayerHasSpores(1);
        $this->addSkillToPlayer(SkillEnum::TRANSFER, $I);
    }

    public function shouldNotBeVisibleIfTargetPlayerIsMush(FunctionalTester $I): void
    {
        $this->givenTargetPlayerIsMush();

        $this->whenSourceTriesToExchangeBodyWithTarget();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeExecutableIfTargetPlayerDoesNotHaveSpores(FunctionalTester $I): void
    {
        $this->givenTargetPlayerHasSpores(0);

        $this->whenSourceTriesToExchangeBodyWithTarget();

        $this->thenActionShouldNotBeExecutable(
            message: ActionImpossibleCauseEnum::TRANSFER_NO_SPORE,
            I: $I,
        );
    }

    public function shouldBeExecutableOncePerPlayer(FunctionalTester $I): void
    {
        $this->givenSourceExchangesBodyWithTarget();

        $this->givenSourcePlayerHasSpores(1);

        $this->whenTargetTriesToExchangeBodyWithTarget();

        $this->thenActionShouldNotBeExecutable(
            message: ActionImpossibleCauseEnum::UNIQUE_ACTION,
            I: $I,
        );
    }

    public function shouldMakeUsersExchangePlayers(FunctionalTester $I): void
    {
        $this->whenSourceExchangesBodyWithTarget();

        $this->thenPlayerUsersAreSwapped($I);
    }

    public function shouldMakeTargetPlayerMush(FunctionalTester $I): void
    {
        $this->whenSourceExchangesBodyWithTarget();

        $this->thenTargetPlayerIsMush($I);
    }

    public function shouldMakeSourcePlayerHuman(FunctionalTester $I): void
    {
        $this->whenSourceExchangesBodyWithTarget();

        $this->thenPlayerIsHuman($I);
    }

    public function shouldMakeTargetPlayerJoiningMushChannel(FunctionalTester $I): void
    {
        $this->whenSourceExchangesBodyWithTarget();

        $this->thenPlayerIsInMushChannel($this->target, $I);
    }

    public function shouldMakeSourcePlayerExitingMushChannel(FunctionalTester $I): void
    {
        $this->whenSourceExchangesBodyWithTarget();

        $this->thenPlayerIsNotInMushChannel($this->source, $I);
    }

    public function shouldRemoveSourcePlayerSpores(FunctionalTester $I): void
    {
        $this->givenSourcePlayerHasSpores(1);

        $this->whenSourceExchangesBodyWithTarget();

        $this->thenSourcePlayerSporesAreRemoved($I);
    }

    public function shouldRemoveTargetPlayerSpores(FunctionalTester $I): void
    {
        $this->whenSourceExchangesBodyWithTarget();

        $this->thenTargetPlayerSporesAreRemoved($I);
    }

    public function shouldMarkSourcePlayerAsHuman(FunctionalTester $I): void
    {
        $this->whenSourceExchangesBodyWithTarget();

        $this->thenSourceClosedPlayerIsHuman($I);
    }

    public function shouldMarkTargetPlayerAsMush(FunctionalTester $I): void
    {
        $this->whenSourceExchangesBodyWithTarget();

        $this->thenTargetClosedPlayerIsMush($I);
    }

    public function shouldRemoveSourcePlayerHumanSkills(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I, $this->source);

        $this->whenSourceExchangesBodyWithTarget();

        $this->thenSourcePlayerHumanSkillsAreRemoved($I);
    }

    public function shouldRemoveTargetPlayerHumanSkills(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I, $this->target);

        $this->whenSourceExchangesBodyWithTarget();

        $this->thenTargetPlayerHumanSkillsAreRemoved($I);
    }

    public function shouldExchangeMushSkills(FunctionalTester $I): void
    {
        $this->whenSourceExchangesBodyWithTarget();

        $this->thenTargetPlayerShouldHaveSkill(SkillEnum::TRANSFER, $I);
    }

    public function shouldCreateNotificationForSourcePlayer(FunctionalTester $I): void
    {
        $this->whenSourceExchangesBodyWithTarget();

        $this->thenSourcePlayerShouldHaveNotification($I);
    }

    public function shouldCreateNotificationForTargetPlayer(FunctionalTester $I): void
    {
        $this->whenSourceExchangesBodyWithTarget();

        $this->thenTargetPlayerShouldHaveNotification($I);
    }

    public function shouldDeleteHasUsedMageBookStatusOnPlayer(FunctionalTester $I): void
    {
        $this->givenSourcePlayerHasUsedMageBook();

        $this->whenSourceExchangesBodyWithTarget();

        $this->thenSourcePlayerShouldNotHaveUsedMageBookStatus($I);
    }

    public function shouldDeleteHasUsedMageBookStatusOnTarget(FunctionalTester $I): void
    {
        $this->givenTargetPlayerHasUsedMageBook();

        $this->whenSourceExchangesBodyWithTarget();

        $this->thenTargetPlayerShouldNotHaveUsedMageBookStatus($I);
    }

    public function shouldMakeMycoAlarmRing(FunctionalTester $I): void
    {
        $this->givenSourcePlayerHasSpores(1);

        $this->givenMycoAlarmInRoom();

        $this->whenSourceExchangesBodyWithTarget();

        $this->thenMycoAlarmPrintsPublicLog($I);
    }

    public function targetShouldNotGainSkillPointsAfterTransfer(FunctionalTester $I): void
    {
        $this->givenTargetPlayerHasShooterSkill($I);

        $this->whenSourceExchangesBodyWithTargetAndNewDayOccurs();

        $this->thenTargetPlayerShouldNotGainSkillPoints($I);
    }

    public function shouldTriumphStickToPlayers(FunctionalTester $I): void
    {
        $this->givenSourcePlayerHasSpores(1);

        $this->givenSourcePlayerHasTriumph(2);
        $this->givenTargetPlayerHasTriumph(3);

        $this->whenSourceExchangesBodyWithTarget();

        $this->thenSourcePlayerShouldHaveTriumph(2, $I);
        $this->thenTargetPlayerShouldHaveTriumph(3, $I);
    }

    private function givenTargetPlayerHasShooterSkill(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::SHOOTER, $I, $this->target);
    }

    private function whenSourceExchangesBodyWithTargetAndNewDayOccurs(): void
    {
        $this->whenSourceExchangesBodyWithTarget();

        $this->eventService->callEvent(
            event: new PlayerCycleEvent($this->target, [EventEnum::NEW_DAY], new \DateTime()),
            name: PlayerCycleEvent::PLAYER_NEW_CYCLE
        );
    }

    private function thenTargetPlayerShouldNotGainSkillPoints(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => StatusEventLogEnum::GAIN_SHOOT_POINT,
            ]
        );
    }

    private function givenTargetPlayerIsMush(): void
    {
        $this->eventService->callEvent(
            event: new PlayerEvent(
                player: $this->target,
                tags: [],
                time: new \DateTime(),
            ),
            name: PlayerEvent::CONVERSION_PLAYER,
        );
    }

    private function givenSourcePlayerIsMush(): void
    {
        $this->eventService->callEvent(
            event: new PlayerEvent(
                player: $this->player,
                tags: [],
                time: new \DateTime(),
            ),
            name: PlayerEvent::CONVERSION_PLAYER,
        );
    }

    private function givenSourcePlayerHasSpores(int $spores): void
    {
        $this->player->setSpores($spores);
    }

    private function givenTargetPlayerHasSpores(int $spores): void
    {
        $this->target->setSpores($spores);
    }

    private function givenSourceExchangesBodyWithTarget(): void
    {
        $this->whenSourceExchangesBodyWithTarget();
    }

    private function givenSourcePlayerHasUsedMageBook(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_READ_MAGE_BOOK,
            holder: $this->source,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenTargetPlayerHasUsedMageBook(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_READ_MAGE_BOOK,
            holder: $this->target,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenMycoAlarmInRoom(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::MYCO_ALARM,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenSourcePlayerHasTriumph(int $quantity): void
    {
        $this->source->setTriumph($quantity);
    }

    private function givenTargetPlayerHasTriumph(int $quantity): void
    {
        $this->target->setTriumph($quantity);
    }

    private function whenSourceTriesToExchangeBodyWithTarget(): void
    {
        $this->exchangeBody->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->source,
            player: $this->source,
            target: $this->target,
        );
    }

    private function whenSourceExchangesBodyWithTarget(): void
    {
        $this->whenSourceTriesToExchangeBodyWithTarget();
        $this->exchangeBody->execute();
    }

    private function whenTargetTriesToExchangeBodyWithTarget(): void
    {
        $this->exchangeBody->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->target,
            player: $this->target,
            target: $this->source,
        );
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->exchangeBody->isVisible());
    }

    private function thenActionShouldNotBeExecutable(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->exchangeBody->cannotExecuteReason());
    }

    private function thenPlayerUsersAreSwapped(FunctionalTester $I): void
    {
        $I->assertEquals($this->oldHumanUser->getUserId(), $this->source->getUser()->getUserId());
        $I->assertEquals($this->oldMushUser->getUserId(), $this->target->getUser()->getUserId());
    }

    private function thenTargetPlayerIsMush(FunctionalTester $I): void
    {
        $I->assertTrue($this->target->isMush());
    }

    private function thenPlayerIsHuman(FunctionalTester $I): void
    {
        $I->assertTrue($this->source->isHuman());
    }

    private function thenPlayerIsInMushChannel(Player $player, FunctionalTester $I): void
    {
        $I->assertContains($player->getPlayerInfo(), $this->mushChannel->getParticipants()->map(static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()));
    }

    private function thenPlayerIsNotInMushChannel(Player $player, FunctionalTester $I): void
    {
        $I->assertNotContains($player->getPlayerInfo(), $this->mushChannel->getParticipants()->map(static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()));
    }

    private function thenSourcePlayerSporesAreRemoved(FunctionalTester $I): void
    {
        $I->assertEquals(0, $this->player->getSpores());
    }

    private function thenTargetPlayerSporesAreRemoved(FunctionalTester $I): void
    {
        $I->assertEquals(0, $this->target->getSpores());
    }

    private function thenSourceClosedPlayerIsHuman(FunctionalTester $I): void
    {
        $I->assertTrue($this->source->getPlayerInfo()->getClosedPlayer()->isHuman());
    }

    private function thenTargetClosedPlayerIsMush(FunctionalTester $I): void
    {
        $I->assertTrue($this->target->getPlayerInfo()->getClosedPlayer()->isMush());
    }

    private function thenSourcePlayerHumanSkillsAreRemoved(FunctionalTester $I): void
    {
        $I->assertEmpty($this->source->getSkills()->filter(static fn (Skill $skill) => $skill->isHumanSkill()));
    }

    private function thenTargetPlayerHumanSkillsAreRemoved(FunctionalTester $I): void
    {
        $I->assertEmpty($this->target->getSkills()->filter(static fn (Skill $skill) => $skill->isHumanSkill()));
    }

    private function thenTargetPlayerShouldHaveSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $I->assertTrue($this->target->hasSkill($skill));
    }

    private function thenSourcePlayerShouldHaveNotification(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: \sprintf('%s_human', ActionEnum::EXCHANGE_BODY->value),
            actual: $this->source->getNotificationOrThrow()->getMessage(),
        );
    }

    private function thenTargetPlayerShouldHaveNotification(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: \sprintf('%s_mush', ActionEnum::EXCHANGE_BODY->value),
            actual: $this->target->getNotificationOrThrow()->getMessage(),
        );
    }

    private function thenSourcePlayerShouldNotHaveUsedMageBookStatus(FunctionalTester $I): void
    {
        $I->assertFalse($this->source->hasStatus(PlayerStatusEnum::HAS_READ_MAGE_BOOK));
    }

    private function thenTargetPlayerShouldNotHaveUsedMageBookStatus(FunctionalTester $I): void
    {
        $I->assertFalse($this->target->hasStatus(PlayerStatusEnum::HAS_READ_MAGE_BOOK));
    }

    private function thenMycoAlarmPrintsPublicLog(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ':mycoalarm: DRIIIIIIIIIIIIIIIIIIIIIIIIIINNNNNGGGGG!!!!',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: LogEnum::MYCO_ALARM_RING,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    private function thenSourcePlayerShouldHaveTriumph(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->source->getTriumph());
    }
    
    private function thenTargetPlayerShouldHaveTriumph(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->target->getTriumph());
    }
}
