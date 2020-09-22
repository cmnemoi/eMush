"use strict";
// jshint node: true
// jshint esversion: 6

exports.c = function (name)
{
    return new Hunter(name);
};

/*
This file manages Hunter waves and individual Hunters.
*/

// The Hunter object holds hunter data, allows it to fire and get destroyed.
class Hunter
{
    constructor(vname)
    {
        this.name = vname;
        this.damageRange = [2, 4];
        this.preheat = true;
        // TODO: (Yami) this switch case should be data-driven instead
        switch (vname)
        {
            default:
                // for all unrecongined vname, we consider this is a Hunter.
                // will fallback to the next case.
                // jshint -W086
                this.name = "Hunter";
            case "Hunter":
            case "Arack":
                this.health = 6;
                this.attacks = 1;
                this.accuracy = 60;
                break;
            case "Trax":
                this.health = 10;
                this.attacks = 1;
                this.accuracy = 60;
                break;
            case "D1000":
                this.health = 30;
                this.attacks = 3;
                this.accuracy = 50;
                break;
            case "Merchant":
                this.health = 16;
                this.attacks = 0;
                this.accuracy = 0;
                this.damageRange[0] = 0; this.damageRange[1] = 0;
                break;
        }
    }
    // The attack() method returns the damage inflicted by the Hunter.
    attack()
    {
        let dmg = 0;

        if (this.preheat) {
            this.preheat = false;
        } else {
            for (let i = 0; i < this.attacks; i++) {
                if (Math.random() * 100 <= this.accuracy) {
                    dmg += Math.floor( Math.random() * (this.damageRange[1] - this.damageRange[0] + 1) + this.damageRange[0]);
                }
                this.accuracy += 5;
            }
        }

        return dmg;
    }
    // The getDamaged() method inflicts damage to a Hunter, and returns true if its health hits 0.
    getDamaged(dmg)
    {
        this.health -= dmg;
        if (this.health <= 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
