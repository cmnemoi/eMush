import { getImgUrl } from '../utils/getImgUrl';

export enum SkillEnum {
    ANONYMUSH = 'anonymush',
    ANTIQUE_PERFUME = 'antique_perfume',
    APPRENTICE = 'apprentice',
    ASTROPHYSICIST = 'astrophysicist',
    BACTEROPHILIAC = 'bacterophiliac',
    BIOLOGIST = 'biologist',
    BOTANIST = 'botanist',
    BYPASS = 'bypass',
    CAFFEINE_JUNKIE = 'caffeine_junkie',
    CHEF = 'chef',
    COLD_BLOODED = 'cold_blooded',
    CONCEPTOR = 'conceptor',
    CONFIDENT = 'confident',
    CRAZY_EYE = 'crazy_eye',
    CREATIVE = 'creative',
    DEFACER = 'defacer',
    DETACHED_CREWMEMBER = 'detached_crewmember',
    DETERMINED = 'determined',
    DEVOTION = 'devotion',
    DIPLOMAT = 'diplomat',
    DISHEARTENING_CONTACT = 'disheartening_contact',
    DOORMAN = 'doorman',
    EXPERT = 'expert',
    FERTILE = 'fertile',
    FIREFIGHTER = 'firefighter',
    FRUGIVORE = 'frugivore',
    FUNGAL_KITCHEN = 'fungal_kitchen',
    GENIUS = 'genius',
    GREEN_JELLY = 'green_jelly',
    GREEN_THUMB = 'green_thumb',
    GUNNER = 'gunner',
    HARD_BOILED = 'hard_boiled',
    HYGIENIST = 'hygienist',
    INFECTOR = 'infector',
    INTIMIDATING = 'intimidating',
    IT_EXPERT = 'it_expert',
    LEADER = 'leader',
    LETHARGY = 'lethargy',
    LOGISTICS_EXPERT = 'logistics_expert',
    MANKIND_ONLY_HOPE = 'mankind_only_hope',
    MASSIVE_MUSHIFICATION = 'massive_mushification',
    MEDIC = 'medic',
    METALWORKER = 'metalworker',
    MOTIVATOR = 'motivator',
    MYCELIUM_SPIRIT = 'mycelium_spirit',
    MYCOLOGIST = 'mycologist',
    NERON_DEPRESSION = 'neron_depression',
    NERON_ONLY_FRIEND = 'neron_only_friend',
    NIGHTMARISH = 'nightmarish',
    NIMBLE_FINGERS = 'nimble_fingers',
    NINJA = 'ninja',
    NURSE = 'nurse',
    OBSERVANT = 'observant',
    OCD = 'ocd',
    OPPORTUNIST = 'opportunist',
    OPTIMIST = 'optimist',
    PANIC = 'panic',
    PARANOID = 'paranoid',
    PHAGOCYTE = 'phagocyte',
    PHYSICIST = 'physicist',
    PILOT = 'pilot',
    POLITICIAN = 'politician',
    POLYMATH = 'polymath',
    POLYVALENT = 'polyvalent',
    PRESENTIMENT = 'presentiment',
    PYROMANIAC = 'pyromaniac',
    RADIO_EXPERT = 'radio_expert',
    RADIO_PIRACY = 'radio_piracy',
    REBEL = 'rebel',
    ROBOTICS_EXPERT = 'robotics_expert',
    SABOTEUR = 'saboteur',
    SELF_SACRIFICE = 'self_sacrifice',
    SHOOTER = 'shooter',
    SHRINK = 'shrink',
    SLIMETRAP = 'slimetrap',
    SNEAK = 'sneak',
    SOLID = 'solid',
    SPLASHPROOF = 'splashproof',
    SPRINTER = 'sprinter',
    STRATEGURU = 'strateguru',
    SURVIVALIST = 'survivalist',
    TECHNICIAN = 'technician',
    TORTURER = 'torturer',
    TRACKER = 'tracker',
    TRAITOR = 'traitor',
    TRANSFER = 'transfer',
    TRAPPER = 'trapper',
    U_TURN = 'u_turn',
    VICTIMIZER = 'victimizer',
    WRESTLER = 'wrestler',
}

export const SkillIconRecord: {[index: string]: any} = {
    [SkillEnum.ANONYMUSH]: {
        'icon': getImgUrl('skills/mush/anonymous.png')
    },

    [SkillEnum.ANTIQUE_PERFUME]: {
        'icon': getImgUrl('skills/human/antic_perfume.png')
    },

    [SkillEnum.APPRENTICE]: {
        'icon': getImgUrl('skills/human/adaptable.png')
    },

    [SkillEnum.ASTROPHYSICIST]: {
        'icon': getImgUrl('skills/human/astrophysicist.png')
    },

    [SkillEnum.BACTEROPHILIAC]: {
        'icon': getImgUrl('skills/mush/bacterial_contact.png')
    },

    [SkillEnum.BIOLOGIST]: {
        'icon': getImgUrl('skills/human/biologist.png')
    },

    [SkillEnum.BOTANIST]: {
        'icon': getImgUrl('skills/human/botanic.png')
    },

    [SkillEnum.BYPASS]: {
        'icon': getImgUrl('skills/mush/neron_access.png')
    },

    [SkillEnum.CAFFEINE_JUNKIE]: {
        'icon': getImgUrl('skills/human/caffeinomaniac.png')
    },

    [SkillEnum.CHEF]: {
        'icon': getImgUrl('skills/human/cook.png')
    },

    [SkillEnum.COLD_BLOODED]: {
        'icon': getImgUrl('skills/human/cold_blood.png')
    },

    [SkillEnum.CONFIDENT]: {
        'icon': getImgUrl('skills/human/confident.png')
    },

    [SkillEnum.CRAZY_EYE]: {
        'icon': getImgUrl('skills/human/crazy_eye.png')
    },

    [SkillEnum.CREATIVE]: {
        'icon': getImgUrl('skills/human/creative.png')
    },

    [SkillEnum.CONCEPTOR]: {
        'icon': getImgUrl('skills/human/conceptor.png')
    },

    [SkillEnum.DEFACER]: {
        'icon': getImgUrl('skills/mush/defacer.png')
    },

    [SkillEnum.DETACHED_CREWMEMBER]: {
        'icon': getImgUrl('skills/human/unconcerned.png')
    },

    [SkillEnum.DETERMINED]: {
        'icon': getImgUrl('skills/human/persistent.png')
    },

    [SkillEnum.DEVOTION]: {
        'icon': getImgUrl('skills/human/devotion.png')
    },

    [SkillEnum.DIPLOMAT]: {
        'icon': getImgUrl('skills/human/diplomacy.png')
    },

    [SkillEnum.DISHEARTENING_CONTACT]: {
        'icon': getImgUrl('skills/mush/disheartening_contact.png')
    },

    [SkillEnum.DOORMAN]: {
        'icon': getImgUrl('skills/mush/doorman.png')
    },

    [SkillEnum.EXPERT]: {
        'icon': getImgUrl('skills/human/expert.png')
    },

    [SkillEnum.FERTILE]: {
        'icon': getImgUrl('skills/mush/fertile.png')
    },

    [SkillEnum.FIREFIGHTER]: {
        'icon': getImgUrl('skills/human/fireman.png')
    },

    [SkillEnum.FRUGIVORE]: {
        'icon': getImgUrl('skills/human/frugivore.png')
    },

    [SkillEnum.FUNGAL_KITCHEN]: {
        'icon': getImgUrl('skills/mush/fungus_cook.png')
    },

    [SkillEnum.GENIUS]: {
        'icon': getImgUrl('skills/human/genius.png')
    },

    [SkillEnum.GREEN_JELLY]: {
        'icon': getImgUrl('skills/mush/electro_slime.png')
    },

    [SkillEnum.GREEN_THUMB]: {
        'icon': getImgUrl('skills/human/green_thumb.png')
    },

    [SkillEnum.GUNNER]: {
        'icon': getImgUrl('skills/human/gunner.png')
    },

    [SkillEnum.HARD_BOILED]: {
        'icon': getImgUrl('skills/mush/tough.png')
    },

    [SkillEnum.HYGIENIST]: {
        'icon': getImgUrl('skills/human/strong_skin.png')
    },

    [SkillEnum.INFECTOR]: {
        'icon': getImgUrl('skills/mush/infectious_agent.png')
    },

    [SkillEnum.INTIMIDATING]: {
        'icon': getImgUrl('skills/human/daunting.png')
    },

    [SkillEnum.IT_EXPERT]: {
        'icon': getImgUrl('skills/human/it_expert.png')
    },

    [SkillEnum.LEADER]: {
        'icon': getImgUrl('skills/human/leadership.png')
    },

    [SkillEnum.LETHARGY]: {
        'icon': getImgUrl('skills/human/lethargy.png')
    },

    [SkillEnum.LOGISTICS_EXPERT]: {
        'icon': getImgUrl('skills/human/logistics.png')
    },

    [SkillEnum.MANKIND_ONLY_HOPE]: {
        'icon': getImgUrl('skills/human/mankind_only_hope.png')
    },

    [SkillEnum.MASSIVE_MUSHIFICATION]: {
        'icon': getImgUrl('skills/mush/massggedon.png')
    },

    [SkillEnum.MEDIC]: {
        'icon': getImgUrl('skills/human/medic.png')
    },

    [SkillEnum.METALWORKER]: {
        'icon': getImgUrl('skills/human/metaliciste.png')
    },

    [SkillEnum.MOTIVATOR]: {
        'icon': getImgUrl('skills/human/motivator.png')
    },

    [SkillEnum.MYCOLOGIST]: {
        'icon': getImgUrl('skills/human/mycologist.png')
    },

    [SkillEnum.MYCELIUM_SPIRIT]: {
        'icon': getImgUrl('skills/mush/mycellium_spirit.png')
    },

    [SkillEnum.NERON_DEPRESSION]: {
        'icon': getImgUrl('skills/mush/nerondepress.png')
    },

    [SkillEnum.NERON_ONLY_FRIEND]: {
        'icon': getImgUrl('skills/human/neron_only_friend.png')
    },

    [SkillEnum.NIMBLE_FINGERS]: {
        'icon': getImgUrl('skills/mush/nimble_fingers.png')
    },

    [SkillEnum.NIGHTMARISH]: {
        'icon': getImgUrl('skills/mush/nightmare.png')
    },

    [SkillEnum.NINJA]: {
        'icon': getImgUrl('skills/mush/ninja.png')
    },

    [SkillEnum.NURSE]: {
        'icon': getImgUrl('skills/human/first_aid.png')
    },

    [SkillEnum.OBSERVANT]: {
        'icon': getImgUrl('skills/human/observant.png')
    },

    [SkillEnum.OCD]: {
        'icon': getImgUrl('skills/human/stickler.png')
    },

    [SkillEnum.OPPORTUNIST]: {
        'icon': getImgUrl('skills/human/opportunist.png')
    },

    [SkillEnum.OPTIMIST]: {
        'icon': getImgUrl('skills/human/optimistic.png')
    },

    [SkillEnum.PANIC]: {
        'icon': getImgUrl('skills/human/panic.png')
    },

    [SkillEnum.PARANOID]: {
        'icon': getImgUrl('skills/human/paranoid.png')
    },

    [SkillEnum.PHAGOCYTE]: {
        'icon': getImgUrl('skills/mush/phagocytosis.png')
    },

    [SkillEnum.PHYSICIST]: {
        'icon': getImgUrl('skills/human/physicist.png')
    },

    [SkillEnum.PILOT]: {
        'icon': getImgUrl('skills/human/pilot.png')
    },

    [SkillEnum.POLITICIAN]: {
        'icon': getImgUrl('skills/human/politician.png')
    },

    [SkillEnum.POLYMATH]: {
        'icon': getImgUrl('skills/human/polymath.png')
    },

    [SkillEnum.POLYVALENT]: {
        'icon': getImgUrl('skills/human/polyvalent.png')
    },

    [SkillEnum.PRESENTIMENT]: {
        'icon': getImgUrl('skills/human/premonition.png')
    },

    [SkillEnum.PYROMANIAC]: {
        'icon': getImgUrl('skills/mush/pyromane.png')
    },

    [SkillEnum.RADIO_EXPERT]: {
        'icon': getImgUrl('skills/human/communication.png')
    },

    [SkillEnum.RADIO_PIRACY]: {
        'icon': getImgUrl('skills/mush/radio_piracy.png')
    },

    [SkillEnum.REBEL]: {
        'icon': getImgUrl('skills/human/rebel.png')
    },

    [SkillEnum.ROBOTICS_EXPERT]: {
        'icon': getImgUrl('skills/human/robotics.png')
    },

    [SkillEnum.SABOTEUR]: {
        'icon': getImgUrl('skills/mush/sapper.png')
    },

    [SkillEnum.SELF_SACRIFICE]: {
        'icon': getImgUrl('skills/human/abnegation.png')
    },

    [SkillEnum.SLIMETRAP]: {
        'icon': getImgUrl('skills/mush/slimetrap.png')
    },

    [SkillEnum.SHOOTER]: {
        'icon': getImgUrl('skills/human/gunman.png')
    },

    [SkillEnum.SHRINK]: {
        'icon': getImgUrl('skills/human/shrink.png')
    },

    [SkillEnum.SNEAK]: {
        'icon': getImgUrl('skills/human/escape.png')
    },

    [SkillEnum.SOLID]: {
        'icon': getImgUrl('skills/human/sturdy.png')
    },

    [SkillEnum.SPLASHPROOF]: {
        'icon': getImgUrl('skills/mush/water_resistant.png')
    },

    [SkillEnum.SPRINTER]: {
        'icon': getImgUrl('skills/human/sprinter.png')
    },

    [SkillEnum.STRATEGURU]: {
        'icon': getImgUrl('skills/human/space_tactics.png')
    },

    [SkillEnum.SURVIVALIST]: {
        'icon': getImgUrl('skills/human/survival.png')
    },

    [SkillEnum.TECHNICIAN]: {
        'icon': getImgUrl('skills/human/engineer.png')
    },

    [SkillEnum.TRANSFER]: {
        'icon': getImgUrl('skills/mush/transfert.png')
    },

    [SkillEnum.TRAPPER]: {
        'icon': getImgUrl('skills/mush/trapper.png')
    },

    [SkillEnum.TORTURER]: {
        'icon': getImgUrl('skills/human/torturer.png')
    },

    [SkillEnum.TRACKER]: {
        'icon': getImgUrl('skills/human/hunt.png')
    },

    [SkillEnum.TRAITOR]: {
        'icon': getImgUrl('skills/mush/traitor.png')
    },

    [SkillEnum.U_TURN]: {
        'icon': getImgUrl('skills/human/fast_backward.png')
    },

    [SkillEnum.VICTIMIZER]: {
        'icon': getImgUrl('skills/human/juge.png')
    },

    [SkillEnum.WRESTLER]: {
        'icon': getImgUrl('skills/human/wrestler.png')
    }
};

