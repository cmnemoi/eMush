<?php

namespace Mush\Tests\functional\Chat\Listener;

use Mush\Action\Actions\Drop;
use Mush\Action\Actions\GoBerserk;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PrivateChannelAuthorizationCest extends AbstractFunctionalTest
{
    private ActionConfig $dropActionConfig;
    private ActionConfig $moveActionConfig;
    private ActionConfig $mutateActionConfig;
    private Drop $dropAction;
    private Move $moveAction;
    private GoBerserk $mutateAction;
    private StatusServiceInterface $statusService;
    private PlayerServiceInterface $playerService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private ChannelServiceInterface $channelService;
    private Channel $privateChannel;
    private GameItem $chunTalkie;
    private Player $raluca;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->dropActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DROP]);
        $this->moveActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::MOVE]);
        $this->mutateActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::GO_BERSERK]);
        $this->dropAction = $I->grabService(Drop::class);
        $this->moveAction = $I->grabService(Move::class);
        $this->mutateAction = $I->grabService(GoBerserk::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->channelService = $I->grabService(ChannelServiceInterface::class);

        $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);
    }

    public function shouldRemovePlayerFromPrivateChannelWhenDroppingTalkie(FunctionalTester $I): void
    {
        $this->givenChunHasWalkieTalkie();
        $this->givenKuanTiHasWalkieTalkie();
        $this->givenChunIsInLaboratory($I);
        $this->givenKuanTiIsInSpace($I);
        $this->givenPrivateChannelBetweenChunAndKuanTi();

        $this->whenChunDropsHerTalkie();

        $this->thenPublicChannelShouldBeEmpty($I);
        $this->thenOnlyKuanTiShouldBeInPrivateChannel($I);
        $this->thenPrivateChannelShouldHaveLeaveMessage($I);
    }

    public function shouldAllowPlayerToWhisperAfterDroppingTalkie(FunctionalTester $I): void
    {
        $this->givenPrivateChannelBetweenChunAndKuanTi();
        $this->givenChunHasWalkieTalkie();

        $this->whenChunDropsHerTalkie();

        $this->thenPublicChannelShouldBeEmpty($I);
        $this->thenBothChunAndKuanTiShouldBeInPrivateChannel($I);
        $this->thenPrivateChannelShouldNotHaveLeaveMessage($I);
    }

    public function shouldRemovePlayerFromPrivateChannelWhenMutating(FunctionalTester $I): void
    {
        $this->givenChunHasWalkieTalkie();
        $this->givenKuanTiHasWalkieTalkie();
        $this->givenChunIsInLaboratory($I);
        $this->givenKuanTiIsInFrontCorridor($I);
        $this->givenPrivateChannelBetweenChunAndKuanTi();

        $this->whenKuanTiMutates();

        $this->thenPublicChannelShouldBeEmpty($I);
        $this->thenOnlyChunShouldBeInPrivateChannel($I);
        $this->thenPrivateChannelShouldHaveMutateMessage($I);
        $this->thenPrivateChannelShouldNotHaveLeaveMessage($I);
        $this->thenPrivateChannelShouldNotHaveDeathMessage($I);
    }

    public function shouldKeepPlayersInPrivateChannelWhenMovingWithTalkie(FunctionalTester $I): void
    {
        $this->givenPrivateChannelBetweenChunAndKuanTi();
        $this->givenChunHasWalkieTalkie();
        $this->givenKuanTiHasWalkieTalkie();
        $this->givenChunIsInLaboratory($I);
        $this->givenKuanTiIsInSpace($I);
        $door = $this->givenADoorBetweenLaboratoryAndFrontCorridor($I);

        $this->whenChunMoves($door);

        $this->thenPublicChannelShouldBeEmpty($I);
        $this->thenBothChunAndKuanTiShouldBeInPrivateChannel($I);
        $this->thenPrivateChannelShouldNotHaveLeaveMessage($I);
    }

    public function shouldKeepAllPlayersInPrivateChannelWhenEachPlayerHasSomeoneWithTalkie(FunctionalTester $I): void
    {
        $this->givenRalucaInLaboratory($I);
        $this->givenPrivateChannelBetweenChunKuanTiAndRaluca();
        $this->givenChunHasWalkieTalkie();
        $this->givenKuanTiHasWalkieTalkie();
        $this->givenChunIsInLaboratory($I);
        $this->givenKuanTiIsInLaboratory($I);
        $door = $this->givenADoorBetweenLaboratoryAndFrontCorridor($I);

        $this->whenChunMoves($door);

        $this->thenPublicChannelShouldBeEmpty($I);
        $this->thenAllChunKuanTiAndRalucaShouldBeInPrivateChannel($I);
        $this->thenPrivateChannelShouldNotHaveLeaveMessage($I);
    }

    public function shouldRemovePlayersBesideChunWhenSheMovesAsTheOnlyOneWithTalkie(FunctionalTester $I): void
    {
        $this->givenRalucaInLaboratory($I);
        $this->givenPrivateChannelBetweenChunKuanTiAndRaluca();
        $this->givenChunHasWalkieTalkie();
        $this->givenChunIsInLaboratory($I);
        $this->givenKuanTiIsInLaboratory($I);
        $door = $this->givenADoorBetweenLaboratoryAndFrontCorridor($I);

        $this->whenChunMoves($door);

        $this->thenPublicChannelShouldBeEmpty($I);
        $this->thenOnlyChunShouldBeInPrivateChannel($I);
        $this->thenPrivateChannelShouldHaveLeaveMessage($I);
    }

    public function shouldRemovePlayerFromPrivateChannelWhenMovingWithoutTalkie(FunctionalTester $I): void
    {
        $this->givenPrivateChannelBetweenChunAndKuanTi();
        $door = $this->givenADoorBetweenLaboratoryAndFrontCorridor($I);

        $this->whenChunMoves($door);

        $this->thenPublicChannelShouldBeEmpty($I);
        $this->thenOnlyKuanTiShouldBeInPrivateChannel($I);
        $this->thenPrivateChannelShouldHaveLeaveMessage($I);
    }

    public function shouldRemoveOtherPlayerFromPrivateChannelWhenCannotCommunicate(FunctionalTester $I): void
    {
        $this->givenPrivateChannelBetweenChunAndKuanTi();
        $this->givenChunHasWalkieTalkie();
        $this->givenChunIsInLaboratory($I);
        $this->givenKuanTiIsInSpace($I);
        $door = $this->givenADoorBetweenLaboratoryAndFrontCorridor($I);

        $this->whenChunMoves($door);

        $this->thenPublicChannelShouldBeEmpty($I);
        $this->thenOnlyChunShouldBeInPrivateChannel($I);
        $this->thenPrivateChannelShouldHaveLeaveMessage($I);
    }

    public function shouldNotAddDeathMessageWhenPlayerDiesAfterLeavingPrivateChannel(FunctionalTester $I): void
    {
        $this->givenChunHasWalkieTalkie();
        $this->givenKuanTiHasWalkieTalkie();
        $this->givenChunIsInLaboratory($I);
        $this->givenKuanTiIsInFrontCorridor($I);
        $this->givenPrivateChannelBetweenChunAndKuanTi();

        $this->whenKuanTiDropsHisTalkie();
        $this->whenKuanTiDies();

        $this->thenPublicChannelShouldContainOnlyDeathMessage($I);
        $this->thenOnlyChunShouldBeInPrivateChannel($I);
        $this->thenPrivateChannelShouldHaveLeaveMessage($I);
        $this->thenPrivateChannelShouldNotHaveDeathMessage($I);
    }

    public function shouldRemovePlayerFromPrivateChannelWhenDyingAfterDroppingTalkie(FunctionalTester $I): void
    {
        $this->givenChunHasWalkieTalkie();
        $this->givenKuanTiHasWalkieTalkie();
        $this->givenChunIsInLaboratory($I);
        $this->givenKuanTiIsInLaboratory($I);
        $this->givenPrivateChannelBetweenChunAndKuanTi();
        $door = $this->givenADoorBetweenLaboratoryAndFrontCorridor($I);
        $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);

        $this->whenChunDropsHerTalkie();
        $this->whenKuanTiDies();
        $this->whenChunMoves($door);

        $this->thenPublicChannelShouldContainOnlyDeathMessage($I);
        $this->thenOnlyChunShouldBeInPrivateChannel($I);
        $this->thenPrivateChannelShouldHaveDeathMessage($I);
        $this->thenPrivateChannelShouldNotHaveLeaveMessage($I);
    }

    private function givenKuanTiIsInLaboratory(FunctionalTester $I): void
    {
        $I->assertTrue($this->kuanTi->isIn(RoomEnum::LABORATORY));
    }

    private function givenKuanTiIsInFrontCorridor(FunctionalTester $I): void
    {
        $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        $this->kuanTi->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::FRONT_CORRIDOR));
    }

    private function whenKuanTiDies(): void
    {
        $this->playerService->killPlayer(
            player: $this->kuanTi,
            endReason: EndCauseEnum::BLED,
            time: new \DateTime(),
        );
    }

    private function whenKuanTiMutates(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        $this->mutateAction->loadParameters(
            actionConfig: $this->mutateActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
        $this->mutateAction->execute();
    }

    private function thenPrivateChannelShouldHaveDeathMessage(FunctionalTester $I): void
    {
        $I->seeInRepository(Message::class, [
            'channel' => $this->privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_DEATH,
        ]);
    }

    private function givenChunHasWalkieTalkie(): void
    {
        $this->chunTalkie = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::WALKIE_TALKIE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenKuanTiHasWalkieTalkie(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::WALKIE_TALKIE,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunIsInLaboratory(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->isIn(RoomEnum::LABORATORY));
    }

    private function givenPrivateChannelBetweenChunAndKuanTi(): void
    {
        $this->privateChannel = $this->channelService->createPrivateChannel($this->chun);
        $this->channelService->invitePlayer($this->kuanTi, $this->privateChannel);
    }

    private function givenPrivateChannelBetweenChunKuanTiAndRaluca(): void
    {
        $this->givenPrivateChannelBetweenChunAndKuanTi();
        $this->channelService->invitePlayer($this->raluca, $this->privateChannel);
    }

    private function givenADoorBetweenLaboratoryAndFrontCorridor(FunctionalTester $I): Door
    {
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        $door = Door::createFromRooms($frontCorridor, $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));
        $door->setEquipment($I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']));
        $I->haveInRepository($door);

        return $door;
    }

    private function givenRalucaInLaboratory(FunctionalTester $I): void
    {
        $this->raluca = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::RALUCA);
        $I->assertTrue($this->raluca->isIn(RoomEnum::LABORATORY));
    }

    private function whenChunDropsHerTalkie(): void
    {
        $this->dropAction->loadParameters(
            actionConfig: $this->dropActionConfig,
            actionProvider: $this->chunTalkie,
            player: $this->chun,
            target: $this->chunTalkie
        );
        $this->dropAction->execute();
    }

    private function whenChunMoves(Door $door): void
    {
        $this->moveAction->loadParameters(
            actionConfig: $this->moveActionConfig,
            actionProvider: $door,
            player: $this->chun,
            target: $door,
        );
        $this->moveAction->execute();
    }

    private function thenOnlyChunShouldBeInPrivateChannel(FunctionalTester $I): void
    {
        $I->assertEqualsCanonicalizing(
            [$this->chun->getLogName()],
            array_map(
                static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()->getPlayer()->getLogName(),
                $this->privateChannel->getParticipants()->toArray()
            ),
            'Only Chun should be in the private channel'
        );
    }

    private function thenPrivateChannelShouldNotHaveLeaveMessage(FunctionalTester $I): void
    {
        $I->assertNotContains(
            NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
            array_map(
                static fn (Message $message) => $message->getMessage(),
                $this->privateChannel->getMessages()->toArray()
            ),
            'Private channel should not contain "Player left the room without talkie" message'
        );
    }

    private function whenKuanTiDropsHisTalkie(): void
    {
        $kuanTiTalkie = $this->kuanTi->getEquipmentByNameOrThrow(ItemEnum::WALKIE_TALKIE);
        $this->dropAction->loadParameters(
            actionConfig: $this->dropActionConfig,
            actionProvider: $kuanTiTalkie,
            player: $this->kuanTi,
            target: $kuanTiTalkie
        );
        $this->dropAction->execute();
    }

    private function thenPrivateChannelShouldHaveLeaveMessage(FunctionalTester $I): void
    {
        $I->seeInRepository(Message::class, [
            'channel' => $this->privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);
    }

    private function thenPrivateChannelShouldNotHaveDeathMessage(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(Message::class, [
            'channel' => $this->privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_DEATH,
        ]);
    }

    private function thenPrivateChannelShouldHaveMutateMessage(FunctionalTester $I): void
    {
        $I->seeInRepository(Message::class, [
            'channel' => $this->privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_MUTATED,
        ]);
    }

    private function thenPublicChannelShouldContainOnlyDeathMessage(FunctionalTester $I): void
    {
        $channelMessages = $this->publicChannel->getMessages()->map(static fn (Message $message) => $message->getMessage())->toArray();
        $I->assertEqualsCanonicalizing(
            expected: [
                NeronMessageEnum::PLAYER_DEATH,
            ],
            actual: $channelMessages,
        );
    }

    private function givenKuanTiIsInSpace(FunctionalTester $I): void
    {
        $this->kuanTi->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));
        $I->haveInRepository($this->kuanTi);
    }

    private function thenPublicChannelShouldBeEmpty(FunctionalTester $I): void
    {
        $I->assertEmpty($this->publicChannel->getMessages(), 'Public channel should be empty');
    }

    private function thenOnlyKuanTiShouldBeInPrivateChannel(FunctionalTester $I): void
    {
        $I->assertEqualsCanonicalizing(
            [$this->kuanTi->getLogName()],
            array_map(
                static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()->getPlayer()->getLogName(),
                $this->privateChannel->getParticipants()->toArray()
            ),
            'Only Kuan Ti should be in the private channel'
        );
    }

    private function thenBothChunAndKuanTiShouldBeInPrivateChannel(FunctionalTester $I): void
    {
        $I->assertEqualsCanonicalizing(
            [$this->chun->getLogName(), $this->kuanTi->getLogName()],
            array_map(
                static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()->getPlayer()->getLogName(),
                $this->privateChannel->getParticipants()->toArray()
            ),
            'Both Chun and Kuan Ti should be in the private channel'
        );
    }

    private function thenAllChunKuanTiAndRalucaShouldBeInPrivateChannel(FunctionalTester $I): void
    {
        $I->assertEqualsCanonicalizing(
            [$this->chun->getLogName(), $this->kuanTi->getLogName(), $this->raluca->getLogName()],
            array_map(
                static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()->getPlayer()->getLogName(),
                $this->privateChannel->getParticipants()->toArray()
            ),
            'All Chun, Kuan Ti and Raluca should be in the private channel'
        );
    }
}
