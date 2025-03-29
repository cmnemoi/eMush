<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Hunter\Service;

use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Service\CreateHunterService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CreateHunterServiceCest extends AbstractFunctionalTest
{
    private CreateHunterService $createHunter;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->createHunter = $I->grabService(CreateHunterService::class);
    }

    public function shouldCreateMerchantArrivalNeronAnnouncementWhenCreatingTransport(FunctionalTester $I): void
    {
        $this->createHunter->execute(HunterEnum::TRANSPORT, $this->daedalus->getId());

        $I->seeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::MERCHANT_ARRIVAL,
            ]
        );
    }
}
