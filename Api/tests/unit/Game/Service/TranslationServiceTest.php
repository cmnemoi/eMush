<?php

namespace Mush\Tests\unit\Game\Service;

use Mockery;
use Mush\Communication\Enum\DiseaseMessagesEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationService;
use Mush\Player\Enum\EndCauseEnum;
use Mush\RoomLog\Enum\LogDeclinationEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationServiceTest extends TestCase
{
    /** @var TranslatorInterface|Mockery\Mock */
    private TranslatorInterface $translator;

    private TranslationService $translationService;

    /**
     * @before
     */
    public function before()
    {
        $this->translator = \Mockery::mock(TranslatorInterface::class);

        $this->translationService = new TranslationService(
            $this->translator,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testGetSimpleTranslationParameters()
    {
        // test simple parameter
        $this->translator->shouldReceive('trans')
            ->with('key', ['quantity' => 1], 'domain', LanguageEnum::FRENCH)
            ->andReturn('translated message')
            ->once()
        ;
        $this->translationService->translate('key', ['quantity' => 1], 'domain', LanguageEnum::FRENCH);
    }

    public function testGetCharacterTranslationParameters()
    {
        // test character parameter
        $this->translator
            ->shouldReceive('trans')
            ->with(CharacterEnum::PAOLA . '.name', ['character' => 'paola'], 'characters', LanguageEnum::FRENCH)
            ->andReturn('Andie')
            ->once()
        ;

        $this->translator
            ->shouldReceive('trans')
            ->with('key', ['character' => 'Andie', 'character_gender' => 'female'], 'someOtherDomain', LanguageEnum::FRENCH)
            ->andReturn('translated message')
            ->once()
        ;

        $this->translationService->translate('key', ['character' => CharacterEnum::PAOLA], 'someOtherDomain', LanguageEnum::FRENCH);
    }

    public function testGetTargetCharacterTranslationParameters()
    {
        // test targetPlayer parameter
        $this->translator
            ->shouldReceive('trans')
            ->with(CharacterEnum::PAOLA . '.name', ['target_character' => 'paola'], 'characters', LanguageEnum::FRENCH)
            ->andReturn('Andie')
        ;

        $this->translator->shouldReceive('trans')
            ->with('key', ['target_character' => 'Andie', 'target_character_gender' => 'female'], 'someOtherDomain', LanguageEnum::FRENCH)
            ->andReturn('translated message')
            ->once()
        ;
        $this->translationService->translate('key', ['target_character' => CharacterEnum::PAOLA], 'someOtherDomain', LanguageEnum::FRENCH);
    }

    public function testGetTargetEquipmentTranslationParameters()
    {
        // test targetEquipment parameter

        $this->translator
            ->shouldReceive('trans')
            ->with(EquipmentEnum::ANTENNA . '.short_name', ['target_equipment' => EquipmentEnum::ANTENNA], 'equipments', LanguageEnum::FRENCH)
            ->andReturn('Antenne')
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(
                EquipmentEnum::ANTENNA . '.gender',
                ['target_equipment' => 'Antenne'],
                'equipments',
                LanguageEnum::FRENCH
            )
            ->andReturn('female')
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(
                EquipmentEnum::ANTENNA . '.first_letter',
                ['target_equipment' => 'Antenne', 'target_equipment_gender' => 'female'],
                'equipments',
                LanguageEnum::FRENCH
            )
            ->andReturn('vowel')
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(
                EquipmentEnum::ANTENNA . '.plural_name',
                [
                    'target_equipment' => 'Antenne',
                    'target_equipment_gender' => 'female',
                    'target_equipment_first_letter' => 'vowel',
                ],
                'equipments',
                LanguageEnum::FRENCH
            )
            ->andReturn('Antennes')
        ;

        $this->translator->shouldReceive('trans')
            ->with(
                'key',
                [
                    'target_equipment' => 'Antenne',
                    'target_equipment_gender' => 'female',
                    'target_equipment_first_letter' => 'vowel',
                    'target_equipment_plural_name' => 'Antennes',
                ],
                'domain',
                LanguageEnum::FRENCH
            )
            ->andReturn('translated message')
            ->once()
        ;
        $this->translationService->translate('key', ['target_equipment' => EquipmentEnum::ANTENNA], 'domain', LanguageEnum::FRENCH);
    }

    public function testGetTranslationParameters()
    {
        $this->translator
            ->shouldReceive('trans')
            ->with(
                EquipmentEnum::ANTENNA . '.short_name',
                ['target_item' => EquipmentEnum::ANTENNA, 'end_cause' => EndCauseEnum::NO_INFIRMERY],
                'items',
                LanguageEnum::FRENCH
            )
            ->andReturn('Antenne')
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(
                EquipmentEnum::ANTENNA . '.gender',
                [
                    'target_item' => EquipmentEnum::ANTENNA,
                    'end_cause' => EndCauseEnum::NO_INFIRMERY,
                    'target_equipment' => 'Antenne',
                ],
                'items',
                LanguageEnum::FRENCH
            )
            ->andReturn('female')
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(
                EquipmentEnum::ANTENNA . '.first_letter',
                [
                    'target_item' => EquipmentEnum::ANTENNA,
                    'end_cause' => EndCauseEnum::NO_INFIRMERY,
                    'target_equipment' => 'Antenne',
                    'target_equipment_gender' => 'female',
                ],
                'items',
                LanguageEnum::FRENCH
            )
            ->andReturn('vowel')
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(
                EquipmentEnum::ANTENNA . '.plural_name',
                [
                    'target_item' => EquipmentEnum::ANTENNA,
                    'end_cause' => EndCauseEnum::NO_INFIRMERY,
                    'target_equipment' => 'Antenne',
                    'target_equipment_gender' => 'female',
                    'target_equipment_first_letter' => 'vowel',
                ],
                'items',
                LanguageEnum::FRENCH
            )
            ->andReturn('Antennes')
        ;

        $this->translator
            ->shouldReceive('trans')
            ->with(
                EndCauseEnum::NO_INFIRMERY . '.name',
                [
                    'target_item' => EquipmentEnum::ANTENNA,
                    'end_cause' => EndCauseEnum::NO_INFIRMERY,
                    'target_equipment' => 'Antenne',
                    'target_equipment_gender' => 'female',
                    'target_equipment_first_letter' => 'vowel',
                    'target_equipment_plural_name' => 'Antennes',
                ],
                'end_cause',
                LanguageEnum::FRENCH
            )
            ->andReturn('Pas infirmerie')
        ;

        $translatedParameters = [
            'target_item' => EquipmentEnum::ANTENNA,
            'target_equipment' => 'Antenne',
            'target_equipment_gender' => 'female',
            'target_equipment_first_letter' => 'vowel',
            'target_equipment_plural_name' => 'Antennes',
            'end_cause' => 'Pas infirmerie',
        ];

        $initialParameters = [
            'target_item' => EquipmentEnum::ANTENNA,
            'end_cause' => EndCauseEnum::NO_INFIRMERY,
        ];

        $this->translator->shouldReceive('trans')
            ->with('key', $translatedParameters, 'domain', LanguageEnum::FRENCH)
            ->andReturn('translated message')
            ->once()
        ;
        $this->translationService->translate('key', $initialParameters, 'domain', LanguageEnum::FRENCH);
    }

    public function testGetTranslationParametersCoprolaliaMessage()
    {
        $initParameters = [
            LogDeclinationEnum::VERSION => 1,
            LogDeclinationEnum::BALLS_COPROLALIA => 1,
            LogDeclinationEnum::PREFIX_COPROLALIA => 1,
            LogDeclinationEnum::ADJECTIVE_COPROLALIA => 1,
            LogDeclinationEnum::WORD_COPROLALIA => 1,
        ];

        $this->translator
            ->shouldReceive('trans')
            ->with('balls_coprolalia', $initParameters, 'disease_message', LanguageEnum::FRENCH)
            ->andReturn('baloches')
            ->once()
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'prefix_coprolalia',
                [
                    LogDeclinationEnum::VERSION => 1,
                    LogDeclinationEnum::PREFIX_COPROLALIA => 1,
                    LogDeclinationEnum::ADJECTIVE_COPROLALIA => 1,
                    LogDeclinationEnum::WORD_COPROLALIA => 1,
                    LogDeclinationEnum::BALLS_COPROLALIA => 1,
                    'balls_coprolalia' => 'baloches',
                ],
                'disease_message',
                LanguageEnum::FRENCH
            )
            ->andReturn('translated_prefix')
            ->once()
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'adjective_male_single_coprolalia',
                [
                    LogDeclinationEnum::VERSION => 1,
                    LogDeclinationEnum::PREFIX_COPROLALIA => 1,
                    'prefix_coprolalia' => 'translated_prefix',
                    LogDeclinationEnum::ADJECTIVE_COPROLALIA => 1,
                    LogDeclinationEnum::BALLS_COPROLALIA => 1,
                    'balls_coprolalia' => 'baloches',
                    LogDeclinationEnum::WORD_COPROLALIA => 1,
                ],
                'disease_message',
                LanguageEnum::FRENCH
            )
            ->andReturn('translated_adjective_male_single')
            ->once()
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'adjective_male_plural_coprolalia',
                [
                    LogDeclinationEnum::VERSION => 1,
                    LogDeclinationEnum::PREFIX_COPROLALIA => 1,
                    'prefix_coprolalia' => 'translated_prefix',
                    LogDeclinationEnum::ADJECTIVE_COPROLALIA => 1,
                    LogDeclinationEnum::BALLS_COPROLALIA => 1,
                    'balls_coprolalia' => 'baloches',
                    LogDeclinationEnum::WORD_COPROLALIA => 1,
                    'adjective_male_single_coprolalia' => 'translated_adjective_male_single',
                ],
                'disease_message',
                LanguageEnum::FRENCH
            )
            ->andReturn('translated_adjective_male_plural')
            ->once()
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'adjective_female_single_coprolalia',
                [
                    LogDeclinationEnum::VERSION => 1,
                    LogDeclinationEnum::PREFIX_COPROLALIA => 1,
                    'prefix_coprolalia' => 'translated_prefix',
                    LogDeclinationEnum::ADJECTIVE_COPROLALIA => 1,
                    LogDeclinationEnum::BALLS_COPROLALIA => 1,
                    'balls_coprolalia' => 'baloches',
                    LogDeclinationEnum::WORD_COPROLALIA => 1,
                    'adjective_male_single_coprolalia' => 'translated_adjective_male_single',
                    'adjective_male_plural_coprolalia' => 'translated_adjective_male_plural',
                ],
                'disease_message',
                LanguageEnum::FRENCH
            )
            ->andReturn('translated_adjective_female_single')
            ->once()
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'adjective_female_plural_coprolalia',
                [
                    LogDeclinationEnum::VERSION => 1,
                    LogDeclinationEnum::PREFIX_COPROLALIA => 1,
                    'prefix_coprolalia' => 'translated_prefix',
                    LogDeclinationEnum::ADJECTIVE_COPROLALIA => 1,
                    LogDeclinationEnum::BALLS_COPROLALIA => 1,
                    'balls_coprolalia' => 'baloches',
                    LogDeclinationEnum::WORD_COPROLALIA => 1,
                    'adjective_male_single_coprolalia' => 'translated_adjective_male_single',
                    'adjective_male_plural_coprolalia' => 'translated_adjective_male_plural',
                    'adjective_female_single_coprolalia' => 'translated_adjective_female_single',
                ],
                'disease_message',
                LanguageEnum::FRENCH
            )
            ->andReturn('translated_adjective_female_plural')
            ->once()
        ;

        $this->translator
            ->shouldReceive('trans')
            ->with(
                'word_coprolalia',
                [
                    LogDeclinationEnum::VERSION => 1,
                    LogDeclinationEnum::PREFIX_COPROLALIA => 1,
                    'prefix_coprolalia' => 'translated_prefix',
                    LogDeclinationEnum::ADJECTIVE_COPROLALIA => 1,
                    LogDeclinationEnum::BALLS_COPROLALIA => 1,
                    'balls_coprolalia' => 'baloches',
                    LogDeclinationEnum::WORD_COPROLALIA => 1,
                    'adjective_male_single_coprolalia' => 'translated_adjective_male_single',
                    'adjective_male_plural_coprolalia' => 'translated_adjective_male_plural',
                    'adjective_female_single_coprolalia' => 'translated_adjective_female_single',
                    'adjective_female_plural_coprolalia' => 'translated_adjective_female_plural',
                ],
                'disease_message',
                LanguageEnum::FRENCH
            )
            ->andReturn('mot')
            ->once()
        ;

        $this->translator
            ->shouldReceive('trans')
            ->with(
                'word_plural_coprolalia',
                [
                    LogDeclinationEnum::VERSION => 1,
                    'prefix_coprolalia' => 'translated_prefix',
                    LogDeclinationEnum::PREFIX_COPROLALIA => 1,
                    LogDeclinationEnum::ADJECTIVE_COPROLALIA => 1,
                    'balls_coprolalia' => 'baloches',
                    LogDeclinationEnum::BALLS_COPROLALIA => 1,
                    LogDeclinationEnum::WORD_COPROLALIA => 1,
                    'adjective_male_single_coprolalia' => 'translated_adjective_male_single',
                    'adjective_male_plural_coprolalia' => 'translated_adjective_male_plural',
                    'adjective_female_single_coprolalia' => 'translated_adjective_female_single',
                    'adjective_female_plural_coprolalia' => 'translated_adjective_female_plural',
                    'word_coprolalia' => 'mot',
                ],
                'disease_message',
                LanguageEnum::FRENCH
            )
            ->andReturn('mots')
            ->once()
        ;

        $translatedParameters = [
            LogDeclinationEnum::VERSION => 1,
            'prefix_coprolalia' => 'translated_prefix',
            LogDeclinationEnum::PREFIX_COPROLALIA => 1,
            LogDeclinationEnum::ADJECTIVE_COPROLALIA => 1,
            'balls_coprolalia' => 'baloches',
            LogDeclinationEnum::BALLS_COPROLALIA => 1,
            LogDeclinationEnum::WORD_COPROLALIA => 1,
            'adjective_male_single_coprolalia' => 'translated_adjective_male_single',
            'adjective_male_plural_coprolalia' => 'translated_adjective_male_plural',
            'adjective_female_single_coprolalia' => 'translated_adjective_female_single',
            'adjective_female_plural_coprolalia' => 'translated_adjective_female_plural',
            'word_coprolalia' => 'mot',
            'word_plural_coprolalia' => 'mots',
        ];

        $this->translator->shouldReceive('trans')
            ->with(DiseaseMessagesEnum::REPLACE_COPROLALIA, $translatedParameters, 'domain', LanguageEnum::FRENCH)
            ->andReturn('translated message')
            ->once()
        ;

        $this->translationService->translate(DiseaseMessagesEnum::REPLACE_COPROLALIA,
            $initParameters,
            'domain',
            LanguageEnum::FRENCH
        );
    }
}
