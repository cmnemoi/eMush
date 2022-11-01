<?php

namespace Mush\Tests\unit\Disease\Normalizer;

use Mockery;
use Mush\Disease\DataFixtures\DiseaseCausesConfigFixtures;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomCondition;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\SymptomConditionEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Normalizer\DiseaseNormalizer;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\TranslationService;
use Mush\Modifier\DataFixtures\DiseaseModifierConfigFixtures;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Entity\ModifierCollection;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use PHPUnit\Framework\TestCase;

class DiseaseNormalizerTest extends TestCase
{
    private DiseaseNormalizer $normalizer;

    /** @var TranslationService|Mockery\Mock */
    private TranslationService|Mockery\Mock $translationService;

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
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setName('name')->setGameConfig($gameConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease->setDiseaseConfig($diseaseConfig);

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'disease', LanguageEnum::FRENCH)
            ->andReturn('translated one')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('name.description', [], 'disease', LanguageEnum::FRENCH)
            ->andReturn('translated two')
            ->once()
        ;

        $normalized = $this->normalizer->normalize($playerDisease);

        $this->assertEquals([
            'key' => 'name',
            'name' => 'translated one',
            'type' => $diseaseConfig->getType(),
            'description' => 'translated two',
        ], $normalized);
    }

    public function testNormalizeWithEffectModifier()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);

        $modifierConfig = new ModifierConfig(
            'a random modifier name',
            ModifierReachEnum::PLAYER,
            -6,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MORAL_POINT
        );
        $modifierConfig->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('name')
            ->setModifierConfigs(new ModifierCollection([$modifierConfig]))
            ->setGameConfig($gameConfig)
        ;

        $playerDisease = new PlayerDisease();
        $playerDisease->setDiseaseConfig($diseaseConfig);

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'disease', LanguageEnum::FRENCH)
            ->andReturn('translated one')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('name.description', [], 'disease', LanguageEnum::FRENCH)
            ->andReturn('translated two')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with(
                'post_action_decrease.description',
                ['chance' => 100,  'action_name' => '', 'emote' => ':pmo:', 'quantity' => 6],
                'modifiers',
                LanguageEnum::FRENCH
            )
            ->andReturn('translated three')
            ->once()
        ;

        $normalized = $this->normalizer->normalize($playerDisease);

        $this->assertEquals([
            'key' => 'name',
            'name' => 'translated one',
            'type' => $diseaseConfig->getType(),
            'description' => 'translated two//translated three',
        ], $normalized);
    }

    public function testNormalizeWithSymptom()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);

        $symptomCondition = new SymptomCondition(SymptomConditionEnum::RANDOM);
        $symptomCondition->setValue(15);

        $symptomConfig = new SymptomConfig(SymptomEnum::BITING);
        $symptomConfig->addSymptomCondition($symptomCondition);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
            ->setGameConfig($gameConfig)
        ;

        $playerDisease = new PlayerDisease();
        $playerDisease->setDiseaseConfig($diseaseConfig);

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'disease', LanguageEnum::FRENCH)
            ->andReturn('translated one')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('name.description', [], 'disease', LanguageEnum::FRENCH)
            ->andReturn('translated two')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with(
                'biting.description',
                ['chance' => 15],
                'modifiers',
                LanguageEnum::FRENCH
            )
            ->andReturn('translated three')
            ->once()
        ;

        $normalized = $this->normalizer->normalize($playerDisease);

        $this->assertEquals([
            'key' => 'name',
            'name' => 'translated one',
            'type' => $diseaseConfig->getType(),
            'description' => 'translated two//translated three',
        ], $normalized);
    }
}
