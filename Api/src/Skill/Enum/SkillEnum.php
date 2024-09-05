<?php

namespace Mush\Skill\Enum;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionTypeEnum;

enum SkillEnum: string
{
    case ANONYMUSH = 'anonymush';
    case ANTIQUE_PERFUME = 'antique_perfume';
    case APPRENTICE = 'apprentice';
    case ASTROPHYSICIST = 'astrophysicist';
    case BACTEROPHILIAC = 'bacterophiliac';
    case BIOLOGIST = 'biologist';
    case BOTANIST = 'botanist';
    case BYPASS = 'bypass';
    case CAFFEINE_JUNKIE = 'caffeine_junkie';
    case CHEF = 'chef';
    case COLD_BLOODED = 'cold_blooded';
    case CONCEPTOR = 'conceptor';
    case CONFIDENT = 'confident';
    case CRAZY_EYE = 'crazy_eye';
    case CREATIVE = 'creative';
    case DISHEARTENING_CONTACT = 'disheartening_contact';
    case DEFACER = 'defacer';
    case DETACHED_CREWMEMBER = 'detached_crewmember';
    case DETERMINED = 'determined';
    case DEVOTION = 'devotion';
    case DIPLOMAT = 'diplomat';
    case DOORMAN = 'doorman';
    case EXPERT = 'expert';
    case FERTILE = 'fertile';
    case FIREFIGHTER = 'firefighter';
    case FRUGIVORE = 'frugivore';
    case FUNGAL_KITCHEN = 'fungal_kitchen';
    case GENIUS = 'genius';
    case GREEN_JELLY = 'green_jelly';
    case GREEN_THUMB = 'green_thumb';
    case GUNNER = 'gunner';
    case HARD_BOILED = 'hard_boiled';
    case HYGIENIST = 'hygienist';
    case INFECTOR = 'infector';
    case INTIMIDATING = 'intimidating';
    case IT_EXPERT = 'it_expert';
    case LEADER = 'leader';
    case LETHARGY = 'lethargy';
    case LOGISTICS_EXPERT = 'logistics_expert';
    case MANKIND_ONLY_HOPE = 'mankind_only_hope';
    case MASSIVE_MUSHIFICATION = 'massive_mushification';
    case MEDIC = 'medic';
    case METALWORKER = 'metalworker';
    case MOTIVATOR = 'motivator';
    case MYCELIUM_SPIRIT = 'mycelium_spirit';
    case MYCOLOGIST = 'mycologist';
    case NERON_DEPRESSION = 'neron_depression';
    case NERON_ONLY_FRIEND = 'neron_only_friend';
    case NIGHTMARISH = 'nightmarish';
    case NIMBLE_FINGERS = 'nimble_fingers';
    case NINJA = 'ninja';
    case NULL = '';
    case NURSE = 'nurse';
    case OBSERVANT = 'observant';
    case OCD = 'ocd';
    case OPPORTUNIST = 'opportunist';
    case OPTIMIST = 'optimist';
    case PANIC = 'panic';
    case PARANOID = 'paranoid';
    case PHAGOCYTE = 'phagocyte';
    case PHYSICIST = 'physicist';
    case PILOT = 'pilot';
    case POLITICIAN = 'politician';
    case POLYMATH = 'polymath';
    case POLYVALENT = 'polyvalent';
    case PRESENTIMENT = 'presentiment';
    case PYROMANIAC = 'pyromaniac';
    case RADIO_EXPERT = 'radio_expert';
    case RADIO_PIRACY = 'radio_piracy';
    case REBEL = 'rebel';
    case ROBOTICS_EXPERT = 'robotics_expert';
    case SABOTEUR = 'saboteur';
    case SELF_SACRIFICE = 'self_sacrifice';
    case SHOOTER = 'shooter';
    case SHRINK = 'shrink';
    case SLIMETRAP = 'slimetrap';
    case SNEAK = 'sneak';
    case SOLID = 'solid';
    case SPLASHPROOF = 'splashproof';
    case SPRINTER = 'sprinter';
    case STRATEGURU = 'strateguru';
    case SURVIVALIST = 'survivalist';
    case TECHNICIAN = 'technician';
    case TORTURER = 'torturer';
    case TRACKER = 'tracker';
    case TRAITOR = 'traitor';
    case TRANSFER = 'transfer';
    case TRAPPER = 'trapper';
    case U_TURN = 'u_turn';
    case VICTIMIZER = 'victimizer';
    case WRESTLER = 'wrestler';

    public function toString(): string
    {
        return $this->value;
    }

    public function isMushSkill(): bool
    {
        return (new ArrayCollection([
            self::ANONYMUSH,
            self::BACTEROPHILIAC,
            self::BYPASS,
            self::DEFACER,
            self::DISHEARTENING_CONTACT,
            self::DOORMAN,
            self::FERTILE,
            self::FUNGAL_KITCHEN,
            self::GREEN_JELLY,
            self::HARD_BOILED,
            self::INFECTOR,
            self::MASSIVE_MUSHIFICATION,
            self::MYCELIUM_SPIRIT,
            self::NERON_DEPRESSION,
            self::NIGHTMARISH,
            self::NIMBLE_FINGERS,
            self::NINJA,
            self::PHAGOCYTE,
            self::PYROMANIAC,
            self::RADIO_PIRACY,
            self::SABOTEUR,
            self::SLIMETRAP,
            self::SPLASHPROOF,
            self::TRAITOR,
            self::TRANSFER,
            self::TRAPPER,
        ]))->contains($this);
    }

    public function isHumanSkill(): bool
    {
        return $this->isMushSkill() === false;
    }

    public function getSkillPointsName(): string
    {
        return match ($this) {
            self::BOTANIST => 'garden',
            self::CHEF => 'cook',
            self::CONCEPTOR => 'core',
            self::PHYSICIST => 'pilgred',
            self::IT_EXPERT => 'computer',
            self::NURSE => 'heal',
            self::TECHNICIAN => 'engineer',
            self::SHOOTER => 'shoot',
            default => '',
        };
    }

    public function getSkillActionTypes(): ArrayCollection
    {
        return new ArrayCollection(
            match ($this) {
                self::BOTANIST => [ActionTypeEnum::ACTION_BOTANIST],
                self::CHEF => [ActionTypeEnum::ACTION_COOK],
                self::CONCEPTOR => [ActionTypeEnum::ACTION_CONCEPTOR],
                self::PHYSICIST => [ActionTypeEnum::ACTION_PILGRED],
                self::IT_EXPERT => [ActionTypeEnum::ACTION_IT],
                self::NURSE => [ActionTypeEnum::ACTION_HEAL],
                self::TECHNICIAN => [ActionTypeEnum::ACTION_TECHNICIAN],
                self::SHOOTER => [ActionTypeEnum::ACTION_SHOOT, ActionTypeEnum::ACTION_SHOOT_HUNTER],
                default => [],
            }
        );
    }
}
