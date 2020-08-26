"use strict";
// jshint node: true
// jshint esversion: 6

/*
  , __StatusTemplate: {
    id: 0,
    Permanent: function(character, ar){},
    OnCycle:  function(character){},
    OnAction = function(character, action){},
    OnTarget: function(character){}
  }
*/

exports.ret = function (name)
{
    return StatusEffects[name];
};


// This file lists Status Effects. Not all of them are added in yet. Diseases in particular are missing.

let StatusEffects = {
    starving: {
        id: 1,
        Permanent: function (character)
        {
        },
        OnCycle: function (character)
        {
            character.gainHp(-1);
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    dirty: {
        id: 2,
        Permanent: function (character, ar)
        {
            if (ar === 'a')
            {
                character.isDirty = true;
            }
            else if (ar === 'r')
            {
                character.isDirty = false;
            }
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    germaphobe: {
        id: 3,
        Permanent: function (character, ar)
        {
        },
        OnCycle: function (character)
        {
            if (character.isDirty)
            {
                character.gainMorale(-1);
            }
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    antisocial: {
        id: 4,
        Permanent: function (character, ar)
        {
        },
        OnCycle: function (character)
        {
            if (character.location.crew[1] != undefined)
            {
                character.gainMorale(-1);
            }
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    hyperactive: {
        id: 5,
        Permanent: function (character, ar)
        {
        },
        OnCycle: function (character)
        {
            character.gainMp(1);
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    lying_down: {
        id: 6,
        Permanent: function (character, ar)
        {
        },
        OnCycle: function (character)
        {
            if (!character.hasStatus(StatusEffects.hyperactive))
            {
                character.gainAp(1);
            }
        },
        OnAction: function (character, action)
        {
            character.gainStatus(this, 'r');
        },
        OnTarget: function (character, action)
        {
            if (action !== "spike" && action !== "whisper")
            {
                character.gainStatus(this, 'r');
            }
        }
    },

    focused: {
        id: 7,
        Permanent: function (character, ar)
        {
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    immunized: {
        id: 8,
        Permanent: function (character, ar)
        {
            if (ar === 'a')
            {
                character.isImmunized = true;
            }
            else if (ar === 'r')
            {
                character.isImmunized = false;
            }
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    full_stomach: {
        id: 9,
        Permanent: function (character, ar)
        {
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    genius: {
        id: 10,
        Permanent: function (character, ar)
        {
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
            // Si l'action est une action de participation, elle est réussie...?
        },
        OnTarget: function (character)
        {
        }
    },

    gagged: {
        id: 11,
        Permanent: function (character, ar)
        {
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    demoralized: {
        id: 12,
        Permanent: function (character, ar)
        {
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    suicidal: {
        id: 13,
        Permanent: function (character, ar)
        {
            if (ar === 'a')
            {
            }
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    burdened: {
        id: 14,
        Permanent: function (character, ar)
        {
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
            if (action === "move")
            {
                character.gainMp((-2));
            }
        },
        OnTarget: function (character)
        {
        }
    },

    lost: {
        id: 15,
        Permanent: function (character, ar)
        {
            if (ar === 'r')
            {
                character.gainMorale(3);
            }
        },
        OnCycle: function (character)
        {
            character.gainMorale(2);
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    first_time: {
        id: 16,
        Permanent: function (character, ar)
        {
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
            if (action === "dtt")
            {
                character.gainMorale(16);
                character.gainStatus(this, 'r');
            }
        },
        OnTarget: function (character, action)
        {
            if (action === "dtt")
            {
                character.gainMorale(16);
                character.gainStatus(this, 'r');
            }
        }
    },

    pacifist: {
        id: 17,
        Permanent: function (character, ar)
        {
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    muted: {
        id: 18,
        Permanent: function (character, ar)
        {
            if (ar === 'a')
            {
                character.gainStatus(StatusEffects.guardian, 'a');
                character.gainHp(5);
            }
            else if (ar === 'r')
            {
                character.gainStatus(StatusEffects.guardian, 'r');
            }
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
        },
        OnTarget: function (character)
        {
        }
    },

    guardian: {
        id: 19,
        Permanent: function (character, ar)
        {
            if (ar === 'a')
            {
                character.isGuardian = true;
            }
            else if (ar === 'r')
            {
                character.isGuardian = false;
            }
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
            if (action === "move" && !character.isMuted)
            {
                character.gainStatus(this, 'r');
            }
        },
        OnTarget: function (character)
        {
        }
    },

    disabled: {
        id: 20,
        Permanent: function (character, ar)
        {
        },
        OnCycle: function (character)
        {
        },
        OnAction: function (character, action)
        {
            if (action === "convertAP")
            {
                character.gainMp(-2);
            }
        },
        OnTarget: function (character)
        {
        }
    }


    // Missing: inactive
    // Stuck in Ship is managed by the exploration functions
};
