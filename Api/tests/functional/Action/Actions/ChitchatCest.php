<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Chitchat;
use Mush\Action\Actions\Examine;
use Mush\Action\Actions\Search;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class ChitchatCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ChooseSkillUseCase $chooseSkillUseCase;
    private Chitchat $chitchat;
    private Player $andie;
    private PlayerDiseaseServiceInterface $diseaseService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CHITCHAT->value]);
        $this->chitchat = $I->grabService(Chitchat::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->andie = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ANDIE);
        $this->diseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->chooseSkillUseCase->execute(new ChooseSkillDto(skill: SkillEnum::CONFIDENT, player: $this->andie));
    }

    public function shouldNotBeVisibleIfPlayerAndConfidentAreNotInSamePlace(FunctionalTester $I): void
    {
        $this->givenAndieIsInSpace();

        $this->whenISetupChitchat();

        $this->thenActionIsNotVisible($I);
    }

    public function shouldNotBeExecutableIfPlayerIsGagged(FunctionalTester $I): void
    {
        $this->givenPlayerIsGagged(player: $this->chun);

        $this->whenISetupChitchat();

        $this->thenActionShouldNotBeExecutableWithCause(ActionImpossibleCauseEnum::GAGGED_PREVENT_SPOKEN_ACTION, $I);
    }

    public function shouldNotBeExecutableIfConfidentIsGagged(FunctionalTester $I): void
    {
        $this->givenPlayerIsGagged(player: $this->andie);

        $this->whenISetupChitchat();

        $this->thenActionShouldNotBeExecutableWithCause(ActionImpossibleCauseEnum::GAGGED_PREVENT_SPOKEN_ACTION, $I);
    }

    public function shouldNotBeExecutableIfPlayerIsMute(FunctionalTester $I): void
    {
        $this->givenPlayerIsMute(player: $this->chun);

        $this->whenISetupChitchat();

        $this->thenActionShouldNotBeExecutableWithCause(ActionImpossibleCauseEnum::SYMPTOMS_ARE_PREVENTING_ACTION, $I);
    }

    public function shouldNotRevealSpecificActions(FunctionalTester $I): void
    {
        $this->givenChunExecutedExamineAction($I);

        $this->whenChunChitchatsWithAndie();

        $this->thenIShouldNotSeeAnActionRevealed($I);
    }

    public function shouldGiveTwoMoralePointsToPlayer(FunctionalTester $I): void
    {
        $this->givenChunHasMoralePoints(10);

        $this->whenChunChitchatsWithAndie();

        $this->thenChunShouldHaveMoralePoints(12, $I);
    }

    public function shouldBeExecutableOncePerDay(FunctionalTester $I): void
    {
        $this->whenChunChitchatsWithAndie();

        $this->thenActionShouldNotBeExecutableWithCause(ActionImpossibleCauseEnum::DAILY_LIMIT, $I);
    }

    public function shouldPrintAPublicLog(FunctionalTester $I): void
    {
        $this->whenChunChitchatsWithAndie();

        $this->thenIShouldSeeAPublicLog($I);
    }

    public function shouldCreateAPrivateLogForConfidentWithPlayerActions(FunctionalTester $I): void
    {
        $this->givenChunExecutedSearchActionTwice($I);

        $this->whenChunChitchatsWithAndie();

        $this->thenIShouldSeeAPrivateLogForConfidentWithPlayerActions($I);
    }

    public function shouldRevealAsMuchActionsAsConfidentMoralePoints(FunctionalTester $I): void
    {
        $this->givenAndieHasMoralePoints(1);

        $this->givenChunExecutedSearchActionTwice($I);

        $this->whenChunChitchatsWithAndie();

        $this->thenIShouldSeeOneActionRevealed($I);
    }

    private function givenAndieIsInSpace(): void
    {
        $this->andie->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));
    }

    private function givenPlayerIsGagged(Player $player): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::GAGGED,
            holder: $player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsMute(Player $player): void
    {
        $this->diseaseService->createDiseaseFromName(
            diseaseName: InjuryEnum::TORN_TONGUE,
            player: $player,
            reasons: [],
        );
    }

    private function givenChunHasMoralePoints(int $moralePoints): void
    {
        $this->chun->setMoralPoint($moralePoints);
    }

    private function givenChunExecutedSearchActionTwice(FunctionalTester $I): void
    {
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SEARCH->value]);

        /** @var Search $search */
        $search = $I->grabService(Search::class);

        for ($i = 0; $i < 2; ++$i) {
            $search->loadParameters(
                actionConfig: $actionConfig,
                actionProvider: $this->chun,
                player: $this->chun,
                target: null
            );
            $search->execute();
        }
    }

    private function givenChunExecutedExamineAction(FunctionalTester $I): void
    {
        /** @var GameEquipmentServiceInterface $gameEquipmentService */
        $gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $talkie = $gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::WALKIE_TALKIE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::EXAMINE->value]);

        /** @var Examine $examine */
        $examine = $I->grabService(Examine::class);

        $examine->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $talkie,
            player: $this->chun,
            target: $talkie
        );
        $examine->execute();
    }

    private function givenAndieHasMoralePoints(int $moralePoints): void
    {
        $this->andie->setMoralPoint($moralePoints);
    }

    private function whenISetupChitchat(): void
    {
        $this->chitchat->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->andie,
            player: $this->chun,
            target: $this->andie
        );
    }

    private function whenChunChitchatsWithAndie(): void
    {
        $this->whenISetupChitchat();
        $this->chitchat->execute();
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->chitchat->isVisible());
    }

    private function thenChunShouldHaveMoralePoints(int $moralePoints, FunctionalTester $I): void
    {
        $I->assertEquals($moralePoints, $this->chun->getMoralPoint());
    }

    private function thenActionShouldNotBeExecutableWithCause(string $cause, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $cause,
            actual: $this->chitchat->cannotExecuteReason(),
        );
    }

    private function thenIShouldSeeAPublicLog(FunctionalTester $I): void
    {
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => ActionLogEnum::CHITCHAT_SUCCESS,
            ]
        );

        $roomLogParameters = $roomLog->getParameters();
        $I->assertEquals($this->andie->getLogName(), $roomLogParameters['target_character']);
        $I->assertEquals($this->chun->getLogName(), $roomLogParameters['character']);
    }

    private function thenIShouldSeeAPrivateLogForConfidentWithPlayerActions(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "**Chun** vous raconte des dernières aventures et même si ça n'a pas l'air, on peut toujours apprendre quelque chose... Ses dernières actions sont Fouiller et Fouiller.",
            actualRoomLogDto: new RoomLogDto(
                player: $this->andie,
                log: LogEnum::CONFIDENT_ACTIONS,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }

    private function thenIShouldSeeOneActionRevealed(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "**Chun** vous raconte des dernières aventures et même si ça n'a pas l'air, on peut toujours apprendre quelque chose... Sa dernière action est Fouiller.",
            actualRoomLogDto: new RoomLogDto(
                player: $this->andie,
                log: LogEnum::CONFIDENT_ACTIONS,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }

    private function thenIShouldNotSeeAnActionRevealed(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "**Chun** vous raconte ses derniers cycles avec ferveur et passion. Pas grand chose à en tirer mais elle a l'air de se sentir mieux...",
            actualRoomLogDto: new RoomLogDto(
                player: $this->andie,
                log: LogEnum::CONFIDENT_ACTIONS,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }
}
