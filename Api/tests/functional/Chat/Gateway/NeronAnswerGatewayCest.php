<?php

declare(strict_types=1);

namespace Mush\tests\functional\Chat\Gateway;

use Mush\Chat\Gateway\NeronAnswerGatewayInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class NeronAnswerGatewayCest extends AbstractFunctionalTest
{
    public function testShouldGetAnswerFromNeron(FunctionalTester $I): void
    {
        $neronAnswerGateway = $I->grabService(NeronAnswerGatewayInterface::class);
        $answer = $neronAnswerGateway->getFor('Quelle est la réponse à la vie, l\'univers et le reste ?');

        $I->assertEquals('Je ne sais pas.', $answer);
    }
}
