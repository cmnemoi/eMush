<?php

namespace Mush\Tests\unit\Disease\Normalizer;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomActivationRequirement;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\SymptomActivationRequirementEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Normalizer\DiseaseNormalizer;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationService;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
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
        $this->translationService = \Mockery::mock(TranslationService::class);

        $this->normalizer = new DiseaseNormalizer($this->translationService);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testNormalize()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $player = new Player();
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setDiseaseName('name');

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

        $normalized = $this->normalizer->normalize($playerDisease, null, ['currentPlayer' => $player]);

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
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $player = new Player();
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $player->setDaedalus($daedalus);

        $modifierConfig = new VariableEventModifierConfig();
        $modifierConfig
            ->setDelta(-6)
            ->setTargetEvent(ModifierScopeEnum::INJURY)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
        ;
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('name')
            ->setModifierConfigs(new ModifierCollection([$modifierConfig]))
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
                'injury_decrease.description',
                ['chance' => 100,  'action_name' => '', 'emote' => ':pmo:', 'quantity' => 6],
                'modifiers',
                LanguageEnum::FRENCH
            )
            ->andReturn('translated three')
            ->once()
        ;

        $normalized = $this->normalizer->normalize($playerDisease, null, ['currentPlayer' => $player]);

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
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $player = new Player();
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $player->setDaedalus($daedalus);

        $symptomActivationRequirement = new SymptomActivationRequirement(SymptomActivationRequirementEnum::RANDOM);
        $symptomActivationRequirement->setValue(15);

        $symptomConfig = new SymptomConfig(SymptomEnum::BITING);
        $symptomConfig->addSymptomActivationRequirement($symptomActivationRequirement);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
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

        $normalized = $this->normalizer->normalize($playerDisease, null, ['currentPlayer' => $player]);

        $this->assertEquals([
            'key' => 'name',
            'name' => 'translated one',
            'type' => $diseaseConfig->getType(),
            'description' => 'translated two//translated three',
        ], $normalized);
    }
}
