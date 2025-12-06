<?php

namespace Mush\Tests\functional\Daedalus\Service;

use Mush\Action\Actions\Hit;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Enum\FunFactEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class GenerateFunFactsCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    private array $funFacts;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testGenerateFunFacts(FunctionalTester $I): void
    {
        $this->whenDaedalusIsFinishedWithTags([EndCauseEnum::SUPER_NOVA]);

        // assert true to earliest death and lowest value related fun facts
        // assert false to highest value related fun facts (since they don't include players with stat value of 0)
        // exclude 'best_diseased' to avoid flaky test caused by trauma
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_ACTION_WASTER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_AGRO]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_ALIEN_TRAITOR]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_CARESSER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_COM_TECHNICIAN]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_COOK]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_EATER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_HACKER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_HUNTER_KILLER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_KILLER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_LOST]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_PLANET_SCANNER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_SANDMAN]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_TECHNICIAN]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_TERRORIST]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_WOUNDED]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::DEAD_DURING_SLEEP]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::DRUG_ADDICT]));
        $I->assertTrue(isset($this->funFacts[FunFactEnum::EARLIEST_DEATH]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::KNIFE_EVADER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::KUBE_ADDICT]));
        $I->assertTrue(isset($this->funFacts[FunFactEnum::LESS_ACTIVE]));
        $I->assertTrue(isset($this->funFacts[FunFactEnum::LESS_TALKATIVE]));
        $I->assertTrue(isset($this->funFacts[FunFactEnum::LESSER_DRUGGED]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::MOST_ACTIVE]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::MOST_TALKATIVE]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::SOL_COLLABS]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::STEALTHIEST]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::UNLUCKIER_TECHNICIAN]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::UNSTEALTHIEST]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::UNSTEALTHIEST_AND_KILLED]));
        $I->assertTrue(isset($this->funFacts[FunFactEnum::WORST_ACTION_WASTER]));
        $I->assertTrue(isset($this->funFacts[FunFactEnum::WORST_AGRO]));
    }

    public function testHitAndReturnToSol(FunctionalTester $I): void
    {
        $this->givenChunHitsKuanTi($I);

        $this->whenDaedalusIsFinishedWithTags([ActionEnum::RETURN_TO_SOL->toString()]);

        // 'best_agro', 'earliest_death', 'most_active', 'best_diseased' are the only ones different from `testGenerateFunFacts`
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_ACTION_WASTER]));
        $I->assertTrue(isset($this->funFacts[FunFactEnum::BEST_AGRO]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_ALIEN_TRAITOR]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_CARESSER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_COM_TECHNICIAN]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_COOK]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_DISEASED]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_EATER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_HACKER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_HUNTER_KILLER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_KILLER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_LOST]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_PLANET_SCANNER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_SANDMAN]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_TECHNICIAN]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_TERRORIST]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::BEST_WOUNDED]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::DEAD_DURING_SLEEP]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::DRUG_ADDICT]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::EARLIEST_DEATH]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::KNIFE_EVADER]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::KUBE_ADDICT]));
        $I->assertTrue(isset($this->funFacts[FunFactEnum::LESS_ACTIVE]));
        $I->assertTrue(isset($this->funFacts[FunFactEnum::LESS_TALKATIVE]));
        $I->assertTrue(isset($this->funFacts[FunFactEnum::LESSER_DRUGGED]));
        $I->assertTrue(isset($this->funFacts[FunFactEnum::MOST_ACTIVE]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::MOST_TALKATIVE]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::SOL_COLLABS]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::STEALTHIEST]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::UNLUCKIER_TECHNICIAN]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::UNSTEALTHIEST]));
        $I->assertFalse(isset($this->funFacts[FunFactEnum::UNSTEALTHIEST_AND_KILLED]));
        $I->assertTrue(isset($this->funFacts[FunFactEnum::WORST_ACTION_WASTER]));
        $I->assertTrue(isset($this->funFacts[FunFactEnum::WORST_AGRO]));
    }

    private function givenChunHitsKuanTi(FunctionalTester $I): void
    {
        $hit = $I->grabService(Hit::class);

        /** @var ActionConfig $hitConfig */
        $hitConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::HIT]);
        // to avoid broken nose
        $hitConfig->setSuccessRate(0);
        $hit->loadParameters(
            actionConfig: $hitConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $hit->execute();
    }

    private function whenDaedalusIsFinishedWithTags(array $tags): void
    {
        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: $tags,
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, DaedalusEvent::FINISH_DAEDALUS);
        $this->funFacts = $this->daedalus->getDaedalusInfo()->getFunFacts();
    }
}
