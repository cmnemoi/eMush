<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Event;

use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Service\TriggerNextRebelBaseContactService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RebelBaseStartedContactCest extends AbstractFunctionalTest
{
    private LinkWithSolRepositoryInterface $linkWithSolRepository;
    private RebelBaseRepositoryInterface $rebelBaseRepository;
    private TriggerNextRebelBaseContactService $triggerNextRebelBaseContact;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepositoryInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
        $this->triggerNextRebelBaseContact = $I->grabService(TriggerNextRebelBaseContactService::class);
    }

    public function shouldTriggerNeronAnnouncementIfLinkWithSolIsEstablished(FunctionalTester $I): void
    {
        $this->givenRebelBasesExists([RebelBaseEnum::WOLF], $I);
        $this->givenLinkWithSolIsEstablished();

        $this->whenRebelBaseStartedContact();

        $I->seeInRepository(
            entity: Message::class,
            params: [
                'message' => NeronMessageEnum::REBEL_SIGNAL,
                'neron' => $this->daedalus->getNeron(),
            ]
        );
    }

    public function shouldNotTriggerNeronAnnouncementIfLinkWithSolIsNotEstablished(FunctionalTester $I): void
    {
        $this->givenRebelBasesExists([RebelBaseEnum::WOLF], $I);
        $this->givenLinkWithSolIsNotEstablished();

        $this->whenRebelBaseStartedContact();

        $I->dontSeeInRepository(
            entity: Message::class,
            params: [
                'message' => NeronMessageEnum::REBEL_SIGNAL,
                'neron' => $this->daedalus->getNeron(),
            ]
        );
    }

    private function givenRebelBasesExists(array $rebelBaseNames, FunctionalTester $I): void
    {
        foreach ($rebelBaseNames as $rebelBaseName) {
            $config = $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => $rebelBaseName]);
            $this->rebelBaseRepository->save(new RebelBase($config, $this->daedalus->getId()));
        }
    }

    private function givenLinkWithSolIsEstablished(): void
    {
        $this->linkWithSolRepository->save(new LinkWithSol(
            $this->daedalus->getId(),
            isEstablished: true,
        ));
    }

    private function givenLinkWithSolIsNotEstablished(): void
    {
        $this->linkWithSolRepository->save(new LinkWithSol(
            $this->daedalus->getId(),
            isEstablished: false,
        ));
    }

    private function whenRebelBaseStartedContact(): void
    {
        $this->triggerNextRebelBaseContact->execute($this->daedalus->getId());
    }
}
