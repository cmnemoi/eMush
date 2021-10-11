<?php

namespace Mush\Tests\unit\Disease\Normalizer;

use Mockery;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Normalizer\DiseaseNormalizer;
use Mush\Game\Service\TranslationService;
use PHPUnit\Framework\TestCase;

class DiseaseNormalizerTest extends TestCase
{
    private DiseaseNormalizer $normalizer;

    /** @var TranslationService|Mockery\Mock */
    private TranslationService $translationService;

    /**
     * @before
     */
    public function before()
    {
        $this->translationService = Mockery::mock(TranslationService::class);

        $this->normalizer = new DiseaseNormalizer($this->translationService);
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testNormalize()
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setName('name');

        $playerDisease = new PlayerDisease();
        $playerDisease->setDiseaseConfig($diseaseConfig);

        $this->translationService->shouldReceive('translate')->andReturn('translated one', 'translated two');

        $normalized = $this->normalizer->normalize($playerDisease);

        $this->assertEquals([
            'key' => 'name',
            'name' => 'translated one',
            'type' => $diseaseConfig->getType(),
            'description' => 'translated two',
        ], $normalized);
    }
}
