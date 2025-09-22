<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Skill\Normalizer;

use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Normalizer\SkillNormalizer;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SkillNormalizerCest extends AbstractFunctionalTest
{
    private SkillNormalizer $skillNormalizer;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->skillNormalizer = $I->grabService(SkillNormalizer::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldNormalizeHumanSkill(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::EXPERT, $I);
        $skill = $this->player->getSkillByNameOrThrow(SkillEnum::EXPERT);

        $normalized = $this->skillNormalizer->normalize($skill, format: null, context: []);

        $I->assertEquals([
            'key' => 'expert',
            'name' => 'Expert',
            'description' => 'Vos compétences sont incroyables mais vous travaillez sans vraiment vous préoccuper de certaines conséquences...//:point: Vos **réussites aux actions** sont **améliorées de 20%**, mais les **blessures** et les **salissures** le sont aussi.',
            'isMushSkill' => false,
        ], $normalized);
    }

    public function shouldNormalizeMushSkill(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::ANONYMUSH, $I);
        $skill = $this->player->getSkillByNameOrThrow(SkillEnum::ANONYMUSH);

        $normalized = $this->skillNormalizer->normalize($skill, format: null, context: []);

        $I->assertEquals([
            'key' => 'anonymush',
            'name' => 'Anonyme',
            'description' => 'Vos mycotoxines sont indétectables dans l\'atmosphère. Vous êtes le prédateur après tout !//:point: Vous apparaissez comme un Humain dans la barre d\'équipage sauf à votre mort.',
            'isMushSkill' => true,
        ], $normalized);
    }

    public function shouldNormalizeDisabledSprinterSkill(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::SPRINTER, $I);
        $skill = $this->player->getSkillByNameOrThrow(SkillEnum::SPRINTER);

        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DISABLED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        $normalized = $this->skillNormalizer->normalize($skill, format: null, context: []);

        $I->assertEquals([
            'key' => 'disabled_sprinter',
            'name' => 'Sprinter',
            'description' => 'Le Sprinter débute toujours sa journée du bon pied ! Il profite de Points de Mouvement supplémentaires.
//
:point: Vous gagnez **2** :pm: de plus à chaque conversion :pa::pm: (non cumulatif avec la **Trottinette**).
//
:point: **+1 étape** en exploration.',
            'isMushSkill' => false,
        ], $normalized);
    }
}
