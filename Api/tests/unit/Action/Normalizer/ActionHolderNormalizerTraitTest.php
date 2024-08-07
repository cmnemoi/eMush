<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Action\Normalizer;

use Codeception\PHPUnit\TestCase;
use Mush\Action\Normalizer\ActionHolderNormalizerTrait;

/**
 * @internal
 */
final class ActionHolderNormalizerTraitTest extends TestCase
{
    use ActionHolderNormalizerTrait;

    public function testGetNormalizedActionsSortedBy(): void
    {
        // given I have some normalized actions
        $normalizedActions = [
            ['id' => 1,
                'key' => 'suicide',
                'name' => 'Se suicider',
                'actionPointCost' => 0,
                'movementPointCost' => 0,
                'moralPointCost' => 0,
                'successRate' => 100,
                'description' => "Vous avez accompli votre mission ici-bas,
         il est temps d'aller coder ailleurs.",
                'canExecute' => true],
            ['id' => 2,
                'key' => 'auto_destroy',
                'name' => "D\u{00e9}truire le vaisseau",
                'actionPointCost' => 0,
                'movementPointCost' => 0,
                'moralPointCost' => 0,
                'successRate' => 100,
                'description' => "Ce vaisseau est rempli d'anomalies g\u{00e9}n\u{00e9}tiques => une solution drastique s'impose.\n        \\/\\/\n        **Attention**,
         cette action d\u{00e9}truira le vaisseau **sur le champ** !",
                'canExecute' => true],
            ['id' => 4,
                'key' => 'rejuvenate',
                'name' => 'Restaurer',
                'actionPointCost' => 0,
                'movementPointCost' => 0,
                'moralPointCost' => 0,
                'successRate' => 100,
                'description' => "Permet de retrouver tout ses =>pa=>,
         =>pm=>,
         =>hp=> et =>pmo=>.\n\\/\\/\nUniquement valable pendant l'alpha.\n\\/\\/\nMerci de nous aider !",
                'canExecute' => true],
            ['id' => 7,
                'key' => 'search',
                'name' => 'Fouiller',
                'actionPointCost' => 1,
                'movementPointCost' => 0,
                'moralPointCost' => 0,
                'successRate' => 100,
                'description' => "Permet de retrouver un objet cach\u{00e9} \u{00e0} l'endroit o\u{00f9} vous vous trouvez.",
                'canExecute' => true],
            ['id' => 59,
                'key' => 'motivational_speech',
                'name' => "Discours enflamm\u{00e9}",
                'actionPointCost' => 2,
                'movementPointCost' => 0,
                'moralPointCost' => 0,
                'successRate' => 100,
                'description' => "Chaque autre \u{00e9}quipier pr\u{00e9}sent dans la salle regagne 2 =>pmo=>.",
                'canExecute' => true],
            ['id' => 60,
                'key' => 'boring_speech',
                'name' => 'Discours barbant',
                'actionPointCost' => 2,
                'movementPointCost' => 0,
                'moralPointCost' => 0,
                'successRate' => 100,
                'description' => "Chaque autre \u{00e9}quipier pr\u{00e9}sent dans la salle regagne 3 =>pm=>.",
                'canExecute' => true]];

        // when I get the actions sorted by name and then by actionPointCost
        $sortedActions = $this->getNormalizedActionsSortedBy('name', $normalizedActions);
        $sortedActions = $this->getNormalizedActionsSortedBy('actionPointCost', $sortedActions);

        // then I get the actions sorted by name and then by actionPointCost
        self::assertSame('auto_destroy', $sortedActions[0]['key']);
        self::assertSame('rejuvenate', $sortedActions[1]['key']);
        self::assertSame('suicide', $sortedActions[2]['key']);
        self::assertSame('search', $sortedActions[3]['key']);
        self::assertSame('boring_speech', $sortedActions[4]['key']);
        self::assertSame('motivational_speech', $sortedActions[5]['key']);
    }
}
