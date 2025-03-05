<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Entity\Trade;
use Mush\Communications\Entity\XylophConfig;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Enum\TradeEnum;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\NeronVersionRepositoryInterface;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Repository\TradeRepositoryInterface;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Normalizer\TerminalNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
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
    private XylophRepositoryInterface $xylophEntryRepository;
    private TradeRepositoryInterface $tradeRepository;
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
        $this->xylophEntryRepository = $I->grabService(XylophRepositoryInterface::class);
        $this->tradeRepository = $I->grabService(TradeRepositoryInterface::class);

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
                'xyloph_db' => 'Xyloph BDD',
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

    public function shouldNormalizeXylophEntries(FunctionalTester $I): void
    {
        $this->givenXylophEntriesExists([XylophEnum::DISK, XylophEnum::MAGNETITE], $I);
        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: [XylophEnum::DISK->toString(), XylophEnum::MAGNETITE->toString()],
            actual: array_map(static fn (array $xylophEntry) => $xylophEntry['key'], $this->normalizedTerminal['xylophEntries'])
        );
    }

    public function shouldNormalizeTrades(FunctionalTester $I): void
    {
        // given a transport with a trade
        $transport = $this->createHunterFromName($this->daedalus, HunterEnum::TRANSPORT, $I);
        $trade = new Trade(
            name: TradeEnum::FOREST_DEAL,
            tradeOptions: new ArrayCollection(),
            hunterId: $transport->getId()
        );
        $this->tradeRepository->save($trade);

        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: ['forest_deal'],
            actual: array_map(static fn (array $trade) => $trade['key'], $this->normalizedTerminal['trades'])
        );
    }

    public function shouldNotNormalizeTradesIfHuntersAreAttacking(FunctionalTester $I): void
    {
        // given an attacking hunter
        $this->createHunterFromName($this->daedalus, HunterEnum::HUNTER, $I);

        // given a transport with a trade
        $transport = $this->createHunterFromName($this->daedalus, HunterEnum::TRANSPORT, $I);
        $trade = new Trade(
            name: TradeEnum::FOREST_DEAL,
            tradeOptions: new ArrayCollection(),
            hunterId: $transport->getId()
        );
        $this->tradeRepository->save($trade);

        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: [],
            actual: $this->normalizedTerminal['trades']
        );
    }

    public function shouldNormalizeTradesIfAsteroidIsAttacking(FunctionalTester $I): void
    {
        // given an asteroid attacking
        $this->createHunterFromName($this->daedalus, HunterEnum::ASTEROID, $I);

        // given a transport with a trade
        $transport = $this->createHunterFromName($this->daedalus, HunterEnum::TRANSPORT, $I);
        $trade = new Trade(
            name: TradeEnum::FOREST_DEAL,
            tradeOptions: new ArrayCollection(),
            hunterId: $transport->getId()
        );
        $this->tradeRepository->save($trade);

        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: ['forest_deal'],
            actual: array_map(static fn (array $trade) => $trade['key'], $this->normalizedTerminal['trades'])
        );
    }

    public function shouldNormalizeTradeInfosWhenHunterAreAttacking(FunctionalTester $I): void
    {
        // given an attacking hunter
        $this->createHunterFromName($this->daedalus, HunterEnum::HUNTER, $I);

        // given a transport with a trade
        $transport = $this->createHunterFromName($this->daedalus, HunterEnum::TRANSPORT, $I);
        $trade = new Trade(
            name: TradeEnum::FOREST_DEAL,
            tradeOptions: new ArrayCollection(),
            hunterId: $transport->getId()
        );
        $this->tradeRepository->save($trade);

        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: [
                'linkStrength' => 'Signal : 0%',
                'neronUpdateStatus' => 'État de mise à jour : 9%',
                'selectRebelBaseToDecode' => 'Choisissez une base rebelle pour pouvoir décoder son signal.',
                'cannotTradeUnderAttack' => 'Un vaisseau ennemi est à portée, il n\'est pas prudent de commercer sous la menace !',
            ],
            actual: $this->normalizedTerminal['infos']
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

    private function givenXylophEntriesExists(array $xylophEntryNames, FunctionalTester $I): void
    {
        foreach ($xylophEntryNames as $xylophEntryName) {
            $config = $I->grabEntityFromRepository(XylophConfig::class, ['name' => $xylophEntryName]);
            $this->xylophEntryRepository->save(new XylophEntry($config, $this->daedalus->getId()));
        }
    }

    private function createHunterFromName(Daedalus $daedalus, string $hunterName, FunctionalTester $I): Hunter
    {
        $hunterConfig = $daedalus->getGameConfig()->getHunterConfigs()->getByNameOrThrow($hunterName);

        $hunter = new Hunter($hunterConfig, $daedalus);
        $hunter->setHunterVariables($hunterConfig);
        $daedalus->addHunter($hunter);
        $I->haveInRepository($hunter);

        return $hunter;
    }
}
