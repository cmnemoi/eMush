<?php

declare(strict_types=1);

namespace Mush\Tests\unit\MetaGame\Service;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Service\GetCharacterBiographyService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GetCharacterBiographyServiceTest extends TestCase
{
    public function testShouldReturnCharacterDetailsForAndie(): void
    {
        // given Andie character translations
        $service = $this->createService([
            'andie.fullname' => 'Andie Graham',
            'andie.age' => '24 ans',
            'andie.employment' => 'Pilote',
            'andie.bioAbstract' => 'Andie est un(e) jeune pilote endoctriné(e) par la fédération.',
            'andie.biography' => '',
        ]);

        // when getting Andie biography
        $result = $service->execute('andie', 'fr');

        // then details should be correctly parsed
        self::assertSame('Andie Graham', $result['details']['fullName']);
        self::assertSame('24 ans', $result['details']['age']);
        self::assertSame('Pilote', $result['details']['employment']);
        self::assertSame('Andie est un(e) jeune pilote endoctriné(e) par la fédération.', $result['details']['abstract']);
    }

    public function testShouldParseBiographyEntriesWithDates(): void
    {
        // given a biography with multiple entries
        $service = $this->createService([
            'jin_su.fullname' => 'Kim Jin-Su',
            'jin_su.age' => '76 ans',
            'jin_su.employment' => 'Commandant',
            'jin_su.bioAbstract' => 'Le célèbre explorateur coréen.',
            'jin_su.biography' => "- 3095 : Jin-Su commence des études de pilotage à la Faculté de Sciences Économiques et Spatiales de Ganymède.
- 3097 : Au cours du survol de Barnard Star, une éruption touche son patrouilleur.
- 3103 : Jin-Su est envoyé en mission d'exploration dans le système Ross 154.",
        ]);

        // when getting Jin-Su biography
        $result = $service->execute('jin_su', 'fr');

        // then biography entries should be correctly parsed
        self::assertCount(3, $result['biography']);

        self::assertSame('3095', $result['biography'][0]['date']);
        self::assertSame('Jin-Su commence des études de pilotage à la Faculté de Sciences Économiques et Spatiales de Ganymède.', $result['biography'][0]['entry']);

        self::assertSame('3097', $result['biography'][1]['date']);
        self::assertSame('Au cours du survol de Barnard Star, une éruption touche son patrouilleur.', $result['biography'][1]['entry']);

        self::assertSame('3103', $result['biography'][2]['date']);
        self::assertSame('Jin-Su est envoyé en mission d\'exploration dans le système Ross 154.', $result['biography'][2]['entry']);
    }

    public function testShouldHandleBiographyEntriesWithoutLeadingDash(): void
    {
        // given a biography entry without leading dash
        $service = $this->createService([
            'chun.fullname' => 'Zhong Chun',
            'chun.age' => '42 ans',
            'chun.employment' => 'Agent de maintenance',
            'chun.bioAbstract' => 'La seule survivante de la catastrophe de Tamina.',
            'chun.biography' => '3129 : Zhong Chun rejoint le corps des missionaires-infirmiers.',
        ]);

        // when getting Chun biography
        $result = $service->execute('chun', 'fr');

        // then biography entry should be correctly parsed
        self::assertCount(1, $result['biography']);
        self::assertSame('3129', $result['biography'][0]['date']);
        self::assertSame('Zhong Chun rejoint le corps des missionaires-infirmiers.', $result['biography'][0]['entry']);
    }

    public function testShouldIgnoreEmptyLines(): void
    {
        // given a biography with empty lines
        $service = $this->createService([
            'derek.fullname' => 'Derek Hogan',
            'derek.age' => '48 ans',
            'derek.employment' => 'Sergent',
            'derek.bioAbstract' => 'Le sergent Hogan est un soldat chanceux.',
            'derek.biography' => "- 3121 : Derek se coupe pour la première fois.

- 3139 : Derek s'engage à la FDS.

",
        ]);

        // when getting Derek biography
        $result = $service->execute('derek', 'fr');

        // then only non-empty entries should be parsed
        self::assertCount(2, $result['biography']);
    }

    public function testShouldIgnoreLinesWithoutColonSeparator(): void
    {
        // given a biography with a line without colon
        $service = $this->createService([
            'finola.fullname' => 'Finola Keegan',
            'finola.age' => '57 ans',
            'finola.employment' => 'Biologiste',
            'finola.bioAbstract' => 'Spécialiste du Mush.',
            'finola.biography' => "- 3132 : Finola s'engage dans la corporation carcérale Rinaldo.
Cette ligne n'a pas de format valide
- 3140 : Les conditions de travail multiplient les accidents.",
        ]);

        // when getting Finola biography
        $result = $service->execute('finola', 'fr');

        // then only valid entries should be parsed
        self::assertCount(2, $result['biography']);
        self::assertSame('3132', $result['biography'][0]['date']);
        self::assertSame('3140', $result['biography'][1]['date']);
    }

    public function testShouldHandleBiographyWithMultipleColonsInEntry(): void
    {
        // given a biography entry with multiple colons (e.g., time references)
        $service = $this->createService([
            'eleesha.fullname' => 'Eleesha Williams',
            'eleesha.age' => '39 ans',
            'eleesha.employment' => 'Journaliste',
            'eleesha.bioAbstract' => 'La journaliste dissidente.',
            'eleesha.biography' => "- 3152 : Le 13 décembre, Finola et Ian reçoivent un signal de Jupiter : celui-ci les avertit du stade avancé de l'invasion.",
        ]);

        // when getting Eleesha biography
        $result = $service->execute('eleesha', 'fr');

        // then the entry should preserve the second colon
        self::assertCount(1, $result['biography']);
        self::assertSame('3152', $result['biography'][0]['date']);
        self::assertSame("Le 13 décembre, Finola et Ian reçoivent un signal de Jupiter : celui-ci les avertit du stade avancé de l'invasion.", $result['biography'][0]['entry']);
    }

    public function testShouldReturnEmptyBiographyArrayWhenNoBiographyProvided(): void
    {
        // given a character with empty biography
        $service = $this->createService([
            'gioele.fullname' => 'Gioele Rinaldo',
            'gioele.age' => '53 ans',
            'gioele.employment' => 'Administrateur',
            'gioele.bioAbstract' => 'Le mécène du projet.',
            'gioele.biography' => '',
        ]);

        // when getting Gioele biography
        $result = $service->execute('gioele', 'fr');

        // then biography array should be empty
        self::assertSame([], $result['biography']);
    }

    public function testShouldTrimWhitespaceFromDateAndEntry(): void
    {
        // given a biography with extra whitespace
        $service = $this->createService([
            'frieda.fullname' => 'Frieda Bergmann',
            'frieda.age' => '63 ans',
            'frieda.employment' => 'Astrophysicienne',
            'frieda.bioAbstract' => 'La scientifique endormie pendant 1024 ans.',
            'frieda.biography' => '-   2059   :   En moins de 2 ans, Frieda recense plus de 100 nouvelles étoiles.   ',
        ]);

        // when getting Frieda biography
        $result = $service->execute('frieda', 'fr');

        // then whitespace should be trimmed
        self::assertCount(1, $result['biography']);
        self::assertSame('2059', $result['biography'][0]['date']);
        self::assertSame('En moins de 2 ans, Frieda recense plus de 100 nouvelles étoiles.', $result['biography'][0]['entry']);
    }

    public function testShouldHandleChaoWangCompleteBiography(): void
    {
        // given Chao Wang's complete biography from the translation file
        $service = $this->createService([
            'chao.fullname' => 'Chao Wang',
            'chao.age' => '33 ans',
            'chao.employment' => 'Officier',
            'chao.bioAbstract' => 'Un soldat qui doute de la fédération.',
            'chao.biography' => "- 3139 : Incorporation à la FDS (Fédération de Défense du sytème Sol)
- 3146 : Fait prisonnier lors de la bataille de Sol, l'officier Wang a cependant réussi à détourner le transporteur rebelle.
- 3150 : L'officier Wang est transféré sur Encelade en vue de préparer l'invasion des systèmes Ross248 et Tau-Ceti.
- 3151 : Les Blast-IOMs se révèlent inefficaces. Chao voit l'intégralité de son commando massacré.
- 3152 : De retour dans l'équipe Magellan, Chao et Derek apprennent le changement d'objectif du projet.
- 3153 : Le responsable du projet Daedalus Kim Jin Su missionne officieusement Chao et Derek pour enlever Finola Keegan.
- 3154 : Lorsqu'il apprend que celui-ci est infecté par le Mush, Chao élimine le nouveau responsable.",
        ]);

        // when getting Chao biography
        $result = $service->execute('chao', 'fr');

        // then all entries should be correctly parsed
        self::assertCount(7, $result['biography']);
        self::assertSame('3139', $result['biography'][0]['date']);
        self::assertSame('3154', $result['biography'][6]['date']);
        self::assertStringContainsString('Incorporation à la FDS', $result['biography'][0]['entry']);
        self::assertStringContainsString('Mush', $result['biography'][6]['entry']);
    }

    /**
     * @param array<string, string> $translations
     */
    private function createService(array $translations): GetCharacterBiographyService
    {
        $translationService = new class($translations) implements TranslationServiceInterface {
            public function __construct(private array $translations) {}

            public function __invoke(string $key, array $parameters = [], string $domain = 'messages', ?string $language = null): string
            {
                return $this->translate($key, $parameters, $domain, $language);
            }

            public function translate(string $key, array $parameters, string $domain, ?string $language = null): string
            {
                return $this->translations[$key] ?? $key;
            }
        };

        return new GetCharacterBiographyService($translationService);
    }
}
