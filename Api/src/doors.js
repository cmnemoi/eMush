'use strict';
// jshint node: true
// jshint esversion: 6

const log = require('./roomlogs');

//This constructor creates a door between two rooms

exports.c = function (room1, room2, breaking) {
    // const id1 = room1.id;
    // const id2 = room2.id;

    // First we create the door.
    const door = {
        destination: room2,
        breakChance: breaking ? breaking : 0,
        broken: false,
        move: function (character) {
            if (!this.broken && character.mp >= 1 + character.mpCost) {
                let i = 0;
                // note : ici 1 devra être remplacé par le coût du déplacement
                for (i = 0; character.location.crew[i] !== character; i++) {
                    if (!character.location.crew[i]) {
                        return false; // error
                    }
                }

                character.lastLocation = character.location;
                character.location.crew.splice(i, 1);

                character.location = this.destination;
                this.destination.crew.push(character);
                character.gainMp(-1);

                // Move logs shall be generated here
                log.act('MOVE', character);
            }

            return true;
        },
    };

    //Then we look for a corresponding door in room2 that has no link.
    //If such a door exists, the two doors will be linked.
    let x;
    let exists = false;
    for (x of room2.doors) {
        if (x.destination === room1 && !x.linked) {
            exists = true;
            break;
        }
    }

    if (exists) {
        x.linked = door;
        door.linked = x;
        x.breakChance = 0;
    } // Two linked doors break down together; they're the same door.
    // They're also repaired together.
    // An unlinked door is effectively a one-way door.

    return door;
};

exports.break = function (door) {
    door.broken = true;
    if (door.linked) {
        door.linked.broken = true;
    }
};

exports.repair = function (door) {
    door.broken = false;
    if (door.linked) {
        door.linked.broken = false;
    }
};
