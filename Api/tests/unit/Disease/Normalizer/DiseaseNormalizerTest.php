<?php

namespace Mush\Tests\unit\Disease\Normalizer;

use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Normalizer\DiseaseNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Mockery;

class DiseaseNormalizerTest extends TestCase
{
    private DiseaseNormalizer $normalizer;

    /** @var TranslatorInterface | Mockery\Mock */
    private TranslatorInterface $translator;

    /**
     * @before
     */
    public function before()
    {
        $this->translator = Mockery::mock(TranslatorInterface::class);

        $this->normalizer = new DiseaseNormalizer($this->translator);
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

        $this->translator->shouldReceive('trans')->andReturn('translated one', 'translated two');

        $normalized = $this->normalizer->normalize($playerDisease);

        $this->assertEquals([
            'key' => 'name',
            'name' => 'translated one',
            'description' => 'translated two'
        ], $normalized);
    }
}