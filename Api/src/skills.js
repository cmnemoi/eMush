'use strict';
// jshint node: true
// jshint esversion: 6

const lang = require('./setlang');
const gf = require('./getfile');

// TODO: just... make this, but again. Entirely. From the ground up.
exports.acquire = function (character, name, type, book) {
    // Vérifications :
    if (book) {
        if (character.magebook) {
            return false;
        }
    } else if (
        type === 'human' &&
        character.skillSlots +
            (character.magebook ? 1 : 0) -
            character.skills.length <=
            0
    ) {
        return false;
    } else if (character.skillSlotsMush - character.skillsMush.length <= 0) {
        return false;
    }

    gf.getJSONFile('data/skills/' + name + '.json')
        .then(skillSkeleton => {
            const newSkill = {
                id: skillSkeleton.id,
                name: skillSkeleton.file,
                displayName: skillSkeleton['name_' + lang.getLang()],
                description: skillSkeleton['description_' + lang.getLang()],
                effect: skillSkeleton['effect_' + lang.getLang()],
            };

            // const lastpos = 0;
            let x;
            let y;
            let txt;
            let exp;

            // OnCycle effect parsing:
            if (skillSkeleton.effects_day) {
                exp = /(?<=sp:)[a-z]+[0-9]\/[0-9]/gi;
                const spePa = exp.exec(skillSkeleton.effects_day.toLowerCase());

                for (x of spePa) {
                    txt = '';
                    for (y = 0; !isNaN(x[y]); y++) {
                        if (/[a-z]/i.test(x[y]))
                            // TODO: change this to a regexp
                            txt += x[y];
                    }
                    // TODO: secure this against wrong input (ex: no numbers)
                    character.special[txt][1] += x[y];
                    character.special[txt][2] += x[y + 2];
                    character.special[txt][0] = character.special[txt][2];
                }
            }

            if (skillSkeleton.effects_cycle) {
                exp = /ef:[a-z]+/i;
                const speEff = exp.exec(
                    skillSkeleton.effects_cycle.toLowerCase()
                );
                newSkill.OnCycle = CycleEffects[speEff];
            }

            if (type.toLowerCase() === 'human') character.skills.push(newSkill);
            else character.skillsMush.push(newSkill);
            if (book) character.magebook = true;
        })
        .catch
        // Create a blank skill...?
        ();

    return true;
};

exports.reset = function (character) {
    // Réinitialise un personnage.
    // Attention, n'effectue pas encore toutes les modifications nécessaires...
    // TODO: complete the reset function for Characters
    character.skillsAvailable = [];
    character.skillSlots = 4;
    character.skills = [];
    character.skillsAvailableMush = [];
    character.skillSlotsMush = 0;
    character.skillsMush = [];

    character.magebook = false;

    character.damageBonus = 0;
    character.repairBonus = 0;
    character.damageReduction = 0;
    // Ces modificateurs seront supprimés
    if (character.isMuted) {
        character.gainStatus('muted', 'r');
    }

    character.effects = [];
    character.special = {};

    character.actions = ['MOVE', 'GUARD', 'ATTACK', 'FLIRT', 'DTT', 'SEARCH'];

    character.isMush = false;
    character.spores = 0;
};

/*
Les compétences vont chacune nécessiter une attention particulière.
Leurs effets sont très variés.
*/

const CycleEffects = {
    shrink: function (character) {
        let x;
        for (x of character.location.crew) {
            if (x !== character && x.hasStatus('lying_down')) {
                x.gainMorale(1);
            }
        }
    },

    logistician: function (character) {
        if (character.location.crew[1]) {
            let loop = true;
            let x;
            while (loop) {
                x =
                    character.location.crew[
                        Math.floor(
                            Math.random() * character.location.crew.length
                        )
                    ];
                if (x !== character) {
                    character.gainAp(1);
                    loop = false;
                }
            }
        }
    },
};
