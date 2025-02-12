<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Normalizer;

use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Repository\LinkWithSolRepository;
use Mush\Communications\Repository\NeronVersionRepositoryInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Normalizer\TerminalNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class CommsCenterNormalizerCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private LinkWithSolRepository $linkWithSolRepository;
    private NeronVersionRepositoryInterface $neronVersionRepository;

    private GameEquipment $commsCenter;
    private array $normalizedTerminal;

    private TerminalNormalizer $terminalNormalizer;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepository::class);
        $this->neronVersionRepository = $I->grabService(NeronVersionRepositoryInterface::class);

        $this->createLinkWithSol();

        $this->givenNeronVersionIs(major: 2, minor: 9);
        $this->givenCommsCenterInPlayerRoom();
        $this->givenPlayerIsFocusedOnCommsCenter();

        $this->terminalNormalizer = $I->grabService(TerminalNormalizer::class);
        $this->terminalNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));
    }

    public function shouldNormalizeName(FunctionalTester $I): void
    {
        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(expected: 'Centre de communication', actual: $this->normalizedTerminal['name']);
    }

    public function shouldNormalizeTips(FunctionalTester $I): void
    {
        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: "Toute communication commence par **établir une liaison**.//Cette action simple peut être réalisée par n'importe quel membre de l'équipage. La liaison apporte des points de moral à toute l'équipe et débloque des nouvelles actions pour le Responsable Comm.//Si le contact échoue, au moins ça **améliorera la qualité du signal** et augmentera les chances de contact lors du prochain essai.",
            actual: $this->normalizedTerminal['tips']
        );
    }

    public function shouldNormalizeSectionTitles(FunctionalTester $I): void
    {
        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: [
                'contact' => 'Contact',
                'neron_version' => 'NERON v2.09',
            ],
            actual: $this->normalizedTerminal['sectionTitles']
        );
    }

    public function shouldNormalizeInfosWhenLinkIsNotEstablished(FunctionalTester $I): void
    {
        $this->givenLinkSignalIs(10);

        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: [
                'linkStrength' => 'Signal : 10%',
                'neronUpdateStatus' => 'Mise à jour : 9%',
            ],
            actual: $this->normalizedTerminal['infos']
        );
    }

    public function shouldNormalizeInfosWhenLinkIsEstablished(FunctionalTester $I): void
    {
        $this->givenLinkSignalIs(10);
        $this->givenLinkIsEstablished();

        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: [
                'linkStrength' => 'Signal : 10%',
                'neronUpdateStatus' => 'Mise à jour : 9%',
                'linkEstablished' => 'Connexion établie !',
            ],
            actual: $this->normalizedTerminal['infos']
        );
    }

    private function whenINormalizeTerminalForPlayer(): void
    {
        $this->normalizedTerminal = $this->terminalNormalizer->normalize($this->commsCenter, format: null, context: ['currentPlayer' => $this->player]);
    }

    private function createLinkWithSol(): void
    {
        $linkWithSol = new LinkWithSol($this->daedalus->getId());
        $this->linkWithSolRepository->save($linkWithSol);
    }

    private function givenCommsCenterInPlayerRoom(): void
    {
        $this->commsCenter = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COMMUNICATION_CENTER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsFocusedOnCommsCenter(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->commsCenter
        );
    }

    private function givenLinkSignalIs(int $quantity): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
        $linkWithSol->increaseStrength($quantity);
        $this->linkWithSolRepository->save($linkWithSol);
    }

    private function givenLinkIsEstablished(): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
        $linkWithSol->establish();
        $this->linkWithSolRepository->save($linkWithSol);
    }

    private function givenNeronVersionIs(int $major, int $minor): void
    {
        $neronVersion = new NeronVersion($this->daedalus->getId(), $major, $minor);
        $this->neronVersionRepository->save($neronVersion);
    }
}
