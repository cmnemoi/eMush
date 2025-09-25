<?php

declare(strict_types=1);

namespace Mush\Tests\api\MetaGame;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Tests\ApiTester;
use Mush\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class AdminActionsControllerCest
{
    private User $user;
    private EntityManagerInterface $entityManager;
    private ClosedDaedalus $closedDaedalus;

    public function _before(ApiTester $I): void
    {
        $this->user = $I->loginUser('admin');
        $this->entityManager = $I->grabService(EntityManagerInterface::class);
    }

    public function shouldMarkDaedalusAsCheater(ApiTester $I): void
    {
        $this->givenAClosedDaedalus();

        $this->whenSendingMarkDaedalusAsCheaterRequest($I);

        $this->thenDaedalusShouldBeMarkedAsCheater();
        $this->thenResponseShouldBeSuccessful($I);
    }

    private function givenAClosedDaedalus(): void
    {
        $this->closedDaedalus = new ClosedDaedalus();
        $this->entityManager->persist($this->closedDaedalus);
        $this->entityManager->flush();
    }

    private function whenSendingMarkDaedalusAsCheaterRequest(ApiTester $I): void
    {
        $I->sendPostRequest('admin/actions/mark-daedalus-as-cheater', ['closedDaedalusId' => $this->closedDaedalus->getId()]);
    }

    private function thenDaedalusShouldBeMarkedAsCheater(): void
    {
        \assert($this->closedDaedalus->isCheater(), 'Daedalus should be marked as cheater');
    }

    private function thenResponseShouldBeSuccessful(ApiTester $I): void
    {
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson(['detail' => "Closed daedalus {$this->closedDaedalus->getId()} marked as cheater successfully."]);
    }
}
