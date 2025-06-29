<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphEnum: string
{
    case ALIEN_FRIEND = 'alien_friend';
    case ALIEN_SCIENCE = 'alien_science';
    case ALL_PREGNANT = 'all_pregnant';
    case AMBITIOUS = 'ambitious';
    case ANDIE_FATE = 'andie_fate';
    case CHUN_DEAD = 'chun_dead';
    case CHUN_LIVES = 'chun_lives';
    case CM_ALIEN_DOWN = 'cm_alien_down';
    case CM_ALL_MUSH_HUMANICIDE = 'cm_all_mush_humanicide';
    case CM_ALL_NEW_MUSH = 'cm_all_new_mush';
    case CM_DAEDALUS_EXPLODE = 'cm_daedalus_explode';
    case CM_EXTINGUISH = 'cm_extinguish';
    case CM_MUSH_VACCINATED = 'cm_mush_vaccinated';
    case CM_PILGRED = 'cm_pilgred';
    case CM_REPAIR_OBJECT = 'cm_repair_object';
    case CM_REPAIR_HULL = 'cm_repair_hull';
    case CM_SABOTAGE = 'cm_sabotage';
    case CM_USE_EXTINGUISHER = 'cm_use_extinguisher';
    case CYCLE_HUMAN = 'cycle_human';
    case CYCLE_MUSH = 'cycle_mush';
    case CYCLE_MUSH_LATE = 'cycle_mush_late';
    case DAEDALUS_DEFENDER = 'daedalus_defender';
    case EDEN_ALIEN_PLANT = 'eden_alien_plant';
    case EDEN_ALIEN_PLANT_PLUS = 'eden_alien_plant_plus';
    case EDEN_AT_LEAST = 'eden_at_least';
    case EDEN_BIOLOGISTS = 'eden_biologists';
    case EDEN_CAT = 'eden_cat';
    case EDEN_COMPUTED = 'eden_computed';
    case EDEN_ENGINEERS = 'eden_engineers';
    case EDEN_MICROBES = 'eden_microbes';
    case EDEN_MUSH_CAT = 'eden_mush_cat';
    case EDEN_MUSH_INTRUDER = 'eden_mush_intruder';
    case EDEN_MUSH_INVASION = 'eden_mush_invasion';
    case EDEN_NO_CAT = 'eden_no_cat';
    case EDEN_ONE_MAN = 'eden_one_man';
    case EDEN_PREGNANT = 'eden_pregnant';
    case EDEN_SEXY = 'eden_sexy';
    case EXPEDITION = 'expedition';
    case EXPLORATOR = 'explorator';
    case FAST_FORWARD = 'fast_forward';
    case HUMANOCIDE = 'humanocide';
    case HUMANOCIDE_CAT = 'humanocide_cat';
    case HUNTER_NEMESIS = 'hunter_nemesis';
    case INFECT = 'infect';
    case KUBE_SOLVED = 'kube_solved';
    case LANDER = 'lander';
    case LOOKING_FOR_KIVANC = 'looking_for_kivanc';
    case LOVER = 'lover';
    case MAGELLAN_ARK = 'magellan_ark';
    case MUSH_FEAR = 'mush_fear';
    case MUSH_INITIAL_BONUS = 'mush_initial_bonus';
    case MUSH_PREGNANT = 'mush_pregnant';
    case MUSH_SPECIALIST = 'mush_specialist';
    case MUSH_VICTORY = 'mush_victory';
    case MUSHICIDE = 'mushicide';
    case MUSHICIDE_CAT = 'mushicide_cat';
    case NATURALIST = 'naturalist';
    case NEW_MUSH = 'new_mush';
    case NEW_PLANET = 'new_planet';
    case NICE_SURGERY = 'nice_surgery';
    case PERPETUAL_HYDRATION = 'perpetual_hydration';
    case PILGRED_MOTHER = 'pilgred_mother';
    case PLANET_FINDER = 'planet_finder';
    case PLANET_SEARCHER = 'planet_searcher';
    case PRECIOUS_BODY = 'precious_body';
    case PREGNANCY = 'pregnancy';
    case PREGNANT_IN_EDEN = 'pregnant_in_eden';
    case PRETTY_COOK = 'pretty_cook';
    case PSYCHOCAT = 'psychocat';
    case PSYCHOPAT = 'psychopat';
    case REBEL_CONTACT = 'rebel_contact';
    case REBEL_WOLF = 'rebel_wolf';
    case REMEDY = 'remedy';
    case RESEARCH_BRILLANT = 'research_brillant';
    case RESEARCH_BRILLANT_END = 'research_brillant_end';
    case RESEARCH_SMALL = 'research_small';
    case RESEARCH_SMALL_END = 'research_small_end';
    case RESEARCH_STANDARD = 'research_standard';
    case RESEARCH_STANDARD_END = 'research_standard_end';
    case RETURN_TO_SOL = 'return_to_sol';
    case ROBOTIC_GRAAL = 'robotic_graal';
    case SAVIOR = 'savior';
    case SOL_CONTACT = 'sol_contact';
    case SOL_MUSH_INVASION = 'sol_mush_invasion';
    case SOL_MUSH_INTRUDER = 'sol_mush_intruder';
    case STAR_MAP_1 = 'star_map_1';
    case STAR_MAP_N = 'star_map_n';
    case SUPER_NOVA = 'super_nova';
    case TR_ANATHEM = 'tr_anathem';
    case TR_ANATHEM_LOSS = 'tr_anathem_loss';
    case NONE = '';

    public function toConfigKey(string $configKey): string
    {
        return $this->value . '_' . $configKey;
    }

    public function toLogKey(): string
    {
        return "{$this->value}.log";
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function toEmoteCode(): string
    {
        return $this->isMushTriumph() ? ':triumph_mush:' : ':triumph:';
    }

    public static function personalEdenTriumphs(): array
    {
        return [
            self::EDEN_ALIEN_PLANT,
            self::REMEDY,
            self::SAVIOR,
        ];
    }

    private function isMushTriumph(): bool
    {
        return \in_array($this, [
            self::CHUN_DEAD,
            self::CYCLE_MUSH,
            self::EDEN_ALIEN_PLANT,
            self::EDEN_MUSH_INVASION,
            self::HUMANOCIDE,
            self::INFECT,
            self::MUSH_INITIAL_BONUS,
            self::MUSH_PREGNANT,
            self::MUSH_VICTORY,
            self::NEW_MUSH,
            self::SOL_MUSH_INVASION,
        ], true);
    }
}
