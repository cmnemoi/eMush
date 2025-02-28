<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Normalizer;

use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\NeronVersionRepositoryInterface;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
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
    private LinkWithSolRepositoryInterface $linkWithSolRepository;
    private NeronVersionRepositoryInterface $neronVersionRepository;
    private RebelBaseRepositoryInterface $rebelBaseRepository;
    private GameEquipment $commsCenter;
    private array $normalizedTerminal;

    private TerminalNormalizer $terminalNormalizer;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepositoryInterface::class);
        $this->neronVersionRepository = $I->grabService(NeronVersionRepositoryInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);

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
                'contact' => 'Liaison',
                'neron_version' => 'NERON v2.09',
                'rebel_bases_network' => 'Réseau de bases rebelles',
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
                'neronUpdateStatus' => 'État de mise à jour : 9%',
                'selectRebelBaseToDecode' => 'Choisissez une base rebelle pour pouvoir décoder son signal.',
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
                'neronUpdateStatus' => 'État de mise à jour : 9%',
                'selectRebelBaseToDecode' => 'Choisissez une base rebelle pour pouvoir décoder son signal.',
                'linkEstablished' => 'Connexion établie !',
            ],
            actual: $this->normalizedTerminal['infos']
        );
    }

    public function shouldNormalizeRebelBases(FunctionalTester $I): void
    {
        $this->givenRebelBasesExists([RebelBaseEnum::WOLF, RebelBaseEnum::KALADAAN], $I);
        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: [RebelBaseEnum::WOLF->toString(), RebelBaseEnum::KALADAAN->toString()],
            actual: array_map(static fn (array $rebelBase) => $rebelBase['key'], $this->normalizedTerminal['rebelBases'])
        );
    }

    private function whenINormalizeTerminalForPlayer(): void
    {
        $this->normalizedTerminal = $this->terminalNormalizer->normalize($this->commsCenter, format: null, context: ['currentPlayer' => $this->player]);
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

    private function givenRebelBasesExists(array $rebelBaseNames, FunctionalTester $I): void
    {
        foreach ($rebelBaseNames as $rebelBaseName) {
            $config = $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => $rebelBaseName]);
            $this->rebelBaseRepository->save(new RebelBase($config, $this->daedalus->getId()));
        }
    }
}
