'use strict';
// jshint node: true
// jshint esversion: 6

const char = require('./characters');
const status = require('./statuseffects');
const hunter = require('./hunters');
const room = require('./rooms');
const logs = require('./roomlogs');

// ShipShowFullStatus is a test function that displays elements of the ship on the current game cycle.
// Specifically, output is written through "the res" parameter.
exports.ShipShowFullStatus = function (ship, res) {
    let x;
    let y;
    res.write('Day ' + ship.day + ' Cycle ' + ship.cycle + '<br>');
    res.write('Ship vitals:<br>');
    res.write(
        'o² ' +
            ship.oxygen +
            ' fuel ' +
            ship.fuel +
            ' hull ' +
            ship.hull +
            '<br>'
    );
    res.write('Overhead threats:<br>');
    for (x of ship.hunters) {
        res.write(
            x.name +
                ' ' +
                x.health +
                ' Weapons preheated: ' +
                !x.preheat +
                ' acc ' +
                x.accuracy +
                '<br>'
        );
    }

    res.write(
        '<br>Now displaying ship status. <br> Ship crew is as follows: <br>'
    );
    for (x of ship.crew) {
        res.write(
            x.name +
                ' HP ' +
                x.hp +
                ' Morale ' +
                x.morale +
                '<br>' +
                x.location.name +
                '<br>'
        );
        res.write('AP ' + x.ap + '/' + x.maxAp + '<br>');
        res.write('MP ' + x.mp + '/' + x.maxMp + '<br>');
        if (x.skills !== undefined) {
            // for (y of x.skills) {
            // }
        } else {
            res.write('No skills');
        }

        if (x.status !== undefined) {
            for (y of x.status) {
                res.write(y.constructor.name + ' ');
            }
        } else {
            res.write('No status');
        }

        res.write('<br>');
    }

    res.write('<br><br>');
    res.write('Ship rooms are as follows: <br>');
    for (x of ship.rooms) {
        res.write(x.name + '(' + x.id + ')<br>');
        for (y of x.crew) {
            res.write(y.name + ' ');
        }
        for (y of x.objects) {
            res.write(y.file + '<br>');
        }
    }

    res.write('<br>');
};

exports.CreateShip = function (mode) {
    return new Daedalus(mode);
};

/*
  The Daedalus class is designed to generate an object that will hold all the data of the current game.
  Attributes such as the crew array or the room array keep track of the ship's layout and current personnel.
  Attributes stored there also include: oxygen, amount of live Mush, hull remaining, hunters...
  This object's methods permit game start and end, cycle change management, crew death management.
*/
class Daedalus {
    constructor() {
        // The constructor initializes the ship, and the game.
        // if (season === 'anderek') {
        // } else if (season === 'chaola') {
        // }

        this.cryogenized = 16;
        this.alive = 0;
        this.aliveMush = 0;
        this.aliveMushTrue = 0;
        this.dead = 0;
        this.deadMush = 0;
        this.crew = [];
        this.hunters = [];

        this.room = [];

        room.generateRoomsByLayout('base_layout', this);

        this.oxygen = 32;
        this.fuel = 20;
        this.hull = 100;
        this.day = 1;
        this.cycle = 1;
        this.shield = -2; // The Plasma Shield is -2 when inactive, -1 when broken, 0 and up when active

        for (let i = 0; i < 4; i++) {
            this.hunters.push(hunter.c('Hunter'));
        }
    }

    // The decryogenize() method adds a character into the game.
    decryogenize(name, id) {
        // Possible error if 0 people are left decryogenized...?
        const newChar = char.c(name, id);
        this.crew.push(newChar);
        this.presets.SPAWN_ROOM.crew.push(newChar);
        newChar.location = this.presets.SPAWN_ROOM;
        this.cryogenized -= 1;
        this.refreshCrewCount();
        logs.act('DECRYOGENIZE', newChar);
    }

    // The kill() method removes a deceased character from the game (and affects the witnesses accordignly).
    // The function returns true if it killed someone
    kill(character, COD) {
        let listpos = -1;
        let listposroom = -1;
        const room = character.location;

        let i = 0;
        // for (; i < this.alive && this.crew[i] !== character; i++) {}

        if (i < this.alive) {
            listpos = i;
        } else {
            return false;
        }

        // for (i = 0; room.crew[i] !== undefined && room.crew[i] !== character; i++) {}

        if (room.crew[i] !== undefined) {
            listposroom = i;
        } else {
            return false;
        }

        room.crew.splice(listposroom, 1);
        // Potentially give characters in the room an illness

        // Death effects trigger here
        for (i of this.crew) {
            if (COD !== 'fatal_depression') i.gainMorale(-1);
            if (i.hasSkill('cold_blooded')) i.gainAp(3);
        }

        if (character.isMush) {
            this.deadMush++;
        }
        this.dead++;
        this.refreshCrewCount();

        this.crew.splice(listpos, 1);

        return true;
    }

    refreshCrewCount() {
        this.alive = 0;
        this.aliveMushTrue = 0;
        this.aliveMush = 0;
        let x;
        // let y;
        for (x of this.crew) {
            this.alive++;
            if (x.isMush) {
                this.aliveMushTrue++;
                if (!x.effects.includes('anonymush')) {
                    this.aliveMush++;
                }
            }
        }
    }

    cyclechange() {
        this.cycle += 1;

        // Cycle gains
        for (const i of this.crew) {
            i.gainAp(1);
            //Also gain one AP if lying down, unless hyperactive (done)
            //Also gain one AP if panicking
            // If gravity simulator isn't broken:
            i.gainMp(1);
            //Also gain one MP if hyperactive (done)
            //Also gain one MP if panicking
            //Also lose one morale point if antisocial and someone else is in the room (done)

            i.triumph++;

            //Status are computed prior to starvation check
            for (const j of i.status) {
                j.OnCycle(i);
                if (j.duration !== undefined) {
                    if (j.duration-- <= 0) {
                        i.gainStatus(j, 'r');
                    }
                }
            }

            if (i.satiety > -24) {
                i.satiety--;
            } else if (!i.isMush) {
                i.gainStatus(status.ret('starving'), 'a');
            }

            /*
            Note : to avoid having to compare status with every possibility, slowing down code considerably,
            each status will have its own instructions on how it applies.
            These effects are documented (and pulled from) statuseffects.js
            */
        }

        // oxygen loss
        this.oxygen -= 1;
        // to implement: lose one additional point for each broken o² tank
        if (this.oxygen <= 0) {
            this.oxygen = 0;
            // someone dies
            if (this.crew !== undefined && this.crew[0] !== undefined) {
                const chosen = this.crew[
                    Math.floor(Math.random() * this.crew.length)
                ];
                this.kill(chosen, 'No Oxygen Disease');
            }
        }

        // Day change happens
        if (this.cycle > 8) {
            this.cycle = 1;
            this.day += 1;
            // Daily changes:
            // Start by checking if the Only Hope is alive.
            let moralePenalty = -2;
            for (const i of this.crew) {
                if (i.hasSkill('only_hope')) {
                    moralePenalty = -1;
                    break;
                }
            }

            // Then, adjust crew morale, hit points and special points.
            for (const i of this.crew) {
                i.gainMorale(
                    moralePenalty + (i.hasSkill('the_optimist') ? 1 : 0)
                );

                i.gainHp(1);
                for (const j of i.special) {
                    j[0] += j[1];
                    if (j[0] > j[2]) j[0] = j[2];
                }
            }

            // TODO: Each alive, mature plant may produce its oxygen and fruits. Then, they lose one hydration state.
            // Admitting the Distributor, existing fruits are carried to the garden.
        }
        // People die of having no morale left:
        let loop;
        do {
            loop = false;
            for (const i of this.crew) {
                if (i.gainMorale(0)) {
                    this.kill(i, 'fatal_depression');
                    loop = true;
                    break;
                }
            }
        } while (loop); // The loop ends once it can run without anyone dying.

        // Plants may fall ill.
        // Drones act.
        // Equipment and doors break.
        // Fires, plates, discharges, room shakes, panic attacks are created.

        // Fires act.

        // Hunter attack
        //If the shield is up, it will absorb any damage
        if (this.shield > 0) {
            for (const i of this.hunters) {
                this.shield -= i.attack();
            }

            if (this.shield < 0) {
                // the shield is broken if the attack brings it to less than 0 points
                this.shield = -1;
                // hull must then take an additional 2-4 points of damage
            }
        } else {
            // If the shield is not up then hull takes damage
            for (const i of this.hunters) {
                this.hull -= i.attack();
            }
        }

        if (this.hull <= 0) {
            // If the hull reaches 0 points, it's over!
            while (this.crew !== undefined && this.crew[0] !== undefined) {
                this.kill(this.crew[0], 'daedalus_destroyed');
            }
        }

        if (this.shield >= 0) {
            this.shield += 5;
            if (this.shield >= 100) {
                this.shield = 100;
            }
        } else if (this.shield === -1) {
            this.shield = 0;
        }

        // TODO: New hunters spawn in
        // TODO: Roles are reattributed
    }
}
