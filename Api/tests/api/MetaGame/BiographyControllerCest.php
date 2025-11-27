<?php

declare(strict_types=1);

namespace Mush\Tests\api\MetaGame;

use Mush\Game\Enum\LanguageEnum;
use Mush\Tests\ApiTester;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class BiographyControllerCest
{
    public function _before(ApiTester $I): void
    {
        $I->loginUser('default');
    }

    public function shouldReturnCharacterBiography(ApiTester $I): void
    {
        $this->whenIRequestCharacterBiography($I);

        $this->thenResponseShouldContainBiographyData($I);
    }

    private function whenIRequestCharacterBiography(ApiTester $I): void
    {
        $I->sendGetRequest('/biography/andie', [
            'language' => LanguageEnum::FRENCH,
        ]);
    }

    private function thenResponseShouldContainBiographyData(ApiTester $I): void
    {
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'details' => [
                'fullName' => 'Andie Graham',
                'age' => '***Âge :*** 24 ans',
                'employment' => '***Profession :*** Sous-lieutenant de la FDS',
                'abstract' => '***Résumé :*** Le Sous-lieutenant Andie Graham n\'est toujours pas au courant que le Daedalus a coupé ses liens avec la FDS. Il/Elle pense toujours être en mission pour le bien de la fédération et participe activement à la réussite de sa mission. Apprenant que son idole, le Sergent Derek Hogan, fait partie de l\'équipage du Daedelus, Andie postule pour une place à bord. Ses compétences variées font de lui/elle une recrue indispensable au succès de la mission.',
            ],
            'biography' => [
                [
                    'date' => '3136',
                    'entry' => 'Naissance quelque part sur Terre.',
                ],
                [
                    'date' => '3136',
                    'entry' => 'Abandonné(e) par ses parents dès les premiers mois de son existence, Andie est récupéré(e) par une commission de la FDS. Il/Elle est directement placé(e) dans un foyer spécialisé en tant que ***pupille de la FDS*** où l\'on ne tardera pas à remarquer ses facultés innées. Andie est vite placé(e) dans une école pour surdoué contrôlé par la FDS et entre dans ***le programme des jeunesses fédériennes***.',
                ],
                [
                    'date' => '3145',
                    'entry' => 'Il/Elle réussit brillamment ses études et entre dans la prestigieuse université de la fédération. Endoctriné(e) durant toutes ses études, ***Andie ne suit plus que la voie de la fédération.*** Malgré sa naïveté, Andie est très populaire et n\'a aucun mal à se faire des amis. Son visage angélique lui permet sans le vouloir d\'attirer la sympathie. ***Son compagnon de chambre Viktor Carlsson***, secrètement infiltré par son frère Nils dans l\'université, fera les frais de son amitié avec lui/elle. En lui confiant sa véritable mission au détour d\'une conversation, Viktor se retrouvera arrêté pour conspiration. Andie pensera innocemment qu\'il aura juste changé de cursus...',
                ],
                [
                    'date' => '3151',
                    'entry' => 'Andie fait la rencontre du ***sergent instructeur Derek Hogan***. Présenté comme héros de la nation, celui-ci passe la totalité de ses cours à raconter sa vie fantasmée. Andie retrouve dans son professeur une figure paternelle manquante et se met à l\'idôlatrer. Il/Elle croit sur parole toutes ces histoires et se met à rêver de parcourir l\'espace lui/elle aussi.',
                ],
                [
                    'date' => '3153',
                    'entry' => 'Andie enchaîne les études afin de servir au mieux les besoins de la fédération. Il/Elle passe brillamment trois thèses, ***en botanique*** pour son texte sur les propriétés curatives de la bulbe brachenne de Tau-Ceti, ***en biologie*** avec son étude sur le comportement social des amibes polarisées de la mer égée de la troisième planète du système sol, et enfin ***en diplomatie*** avec son rapport de médiation, qui d\'après les jury, aurait pu inspirer les négociateurs ayant échoué à prévenir la grande bataille de Sol. ***Il/Elle devient le/la premier(ère) triple lauréat(e) de la fédération.***',
                ],
                [
                    'date' => '3154',
                    'entry' => 'Ses études terminées, Andie décide de devenir pilote, il/elle s\'engage donc dans la division aérienne de la fédération et passe son brevet haut la main. ***Il/Elle demande à être affecté(e) sur Xyloph-17*** et ne tarde pas à être remarqué(e) par Kim Jin Su. Derek lui ne le/la reconnait pas.',
                ],
                [
                    'date' => '3154',
                    'entry' => 'Loin de se douter des enjeux qui se dessine dans l\'ombre, et pensant toujours servir la fédération ardemment, Andie embarque à bord du Daedalus.',
                ],
            ],
        ]);
    }
}
