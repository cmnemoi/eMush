<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Whisper;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Chat\Repository\ChannelRepository;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class WhisperCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Whisper $whisper;
    private Player $andie;
    private ChannelServiceInterface $channelService;
    private ChannelRepository $channelRepository;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::WHISPER->value]);
        $this->whisper = $I->grabService(Whisper::class);
        $this->andie = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ANDIE);
        $this->channelService = $I->grabService(ChannelServiceInterface::class);
        $this->channelRepository = $I->grabService(ChannelRepository::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldNotBeVisibleIfTargetIsNotInSameRoom(FunctionalTester $I): void
    {
        $this->givenAndieIsInSpace();

        $this->whenISetupWhisper();

        $this->thenActionIsNotVisible($I);
    }

    public function shouldCreatePrivateChannelWhenNoneExists(FunctionalTester $I): void
    {
        $this->whenChunWhispersToAndie();

        $this->thenIShouldSeeAPrivateChannelBetweenChunAndAndie($I);
    }

    public function shouldNotBeExecutableIfTargetHasTooManyPrivateChannels(FunctionalTester $I): void
    {
        $this->givenPlayerHasTooManyPrivateChannels($this->andie);

        $this->whenISetupWhisper();

        $this->thenActionShouldNotBeExecutableWithCause(ActionImpossibleCauseEnum::WHISPER_TARGET_NO_AVAILABLE_CHANNEL, $I);
    }

    public function shouldNotBeExecutableIfPlayerHasTooManyPrivateChannels(FunctionalTester $I): void
    {
        $this->givenPlayerHasTooManyPrivateChannels($this->chun);

        $this->whenISetupWhisper();

        $this->thenActionShouldNotBeExecutableWithCause(ActionImpossibleCauseEnum::WHISPER_PLAYER_NO_AVAILABLE_CHANNEL, $I);
    }

    private function givenPlayerHasTooManyPrivateChannels(Player $player): void
    {
        $player->getVariableByName(PlayerVariableEnum::PRIVATE_CHANNELS)->setMaxValue(0);
    }

    private function givenAndieIsInSpace(): void
    {
        $this->andie->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));
    }

    private function whenISetupWhisper(): void
    {
        $this->whisper->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->andie,
            player: $this->chun,
            target: $this->andie
        );
    }

    private function whenChunWhispersToAndie(): void
    {
        $this->whenISetupWhisper();
        $this->whisper->execute();
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->whisper->isVisible());
    }

    private function thenActionShouldNotBeExecutableWithCause(string $cause, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $cause,
            actual: $this->whisper->cannotExecuteReason(),
        );
    }

    private function thenIShouldSeeAPrivateChannelBetweenChunAndAndie(FunctionalTester $I): void
    {
        /** @var ?Channel $privateChannel */
        $privateChannel = $this->channelService
            ->getPlayerChannels($this->chun)
            ->filter(static fn (Channel $channel) => $channel->isPrivate())
            ->first() ?: null;

        $I->assertNotNull($privateChannel);

        /** @var string[] $participants */
        $participants = $privateChannel->getParticipants()
            ->map(static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()->getPlayer()->getName())
            ->toArray();

        $I->assertEqualsCanonicalizing([
            $this->chun->getName(),
            $this->andie->getName(),
        ], $participants);
    }
}
