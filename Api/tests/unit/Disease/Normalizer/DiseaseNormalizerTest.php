<?php

namespace Mush\Tests\unit\Disease\Normalizer;

use Mockery;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\SymptomActivationRequirementEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Normalizer\DiseaseNormalizer;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationService;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DiseaseNormalizerTest extends TestCase
{
    private DiseaseNormalizer $normalizer;

    /** @var Mockery\Mock|TranslationService */
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
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('name.description', [], 'disease', LanguageEnum::FRENCH)
            ->andReturn('translated two')
            ->once();

        $normalized = $this->normalizer->normalize($playerDisease, null, ['currentPlayer' => $player]);

        self::assertSame([
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

        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig
            ->setDelta(-6)
            ->setTargetEvent(ActionEvent::PRE_ACTION)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT);
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('name')
            ->setModifierConfigs(new ModifierCollection([$modifierConfig]));

        $playerDisease = new PlayerDisease();
        $playerDisease->setDiseaseConfig($diseaseConfig);

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'disease', LanguageEnum::FRENCH)
            ->andReturn('translated one')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('name.description', [], 'disease', LanguageEnum::FRENCH)
            ->andReturn('translated two')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with(
                'pre.action_decrease.description',
                ['chance' => 100, 'emote' => ':pmo:', 'quantity' => 6],
                'modifiers',
                LanguageEnum::FRENCH
            )
            ->andReturn('translated three')
            ->once();

        $normalized = $this->normalizer->normalize($playerDisease, null, ['currentPlayer' => $player]);

        self::assertSame([
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

        $symptomActivationRequirement = new ModifierActivationRequirement(SymptomActivationRequirementEnum::RANDOM);
        $symptomActivationRequirement->setValue(15);

        $symptomConfig = new EventModifierConfig(SymptomEnum::BITING);
        $symptomConfig
            ->addModifierRequirement($symptomActivationRequirement)
            ->setTargetEvent(ActionEvent::POST_ACTION);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('name')
            ->setModifierConfigs([$symptomConfig]);

        $playerDisease = new PlayerDisease();
        $playerDisease->setDiseaseConfig($diseaseConfig);

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'disease', LanguageEnum::FRENCH)
            ->andReturn('translated one')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('name.description', [], 'disease', LanguageEnum::FRENCH)
            ->andReturn('translated two')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with(
                'biting_on_post.action.description',
                ['chance' => 15],
                'modifiers',
                LanguageEnum::FRENCH
            )
            ->andReturn('translated three')
            ->once();

        $normalized = $this->normalizer->normalize($playerDisease, null, ['currentPlayer' => $player]);

        self::assertSame([
            'key' => 'name',
            'name' => 'translated one',
            'type' => $diseaseConfig->getType(),
            'description' => 'translated two//translated three',
        ], $normalized);
    }
}
