"use strict";
// jshint node: true
// jshint esversion: 6

let status = require('./statuseffects');
let skills = require('./skills');
let items = require('./items');

exports.c = function (name)
{
    return new Character(name);
};

/*
  Character objects need to hold data relative to each character : health, morale, energy, controlling player...?
  and Mush status, as well as status effects, available and possessed skills.
*/
class Character
{
    // TODO: The constructor for characters is incomplete and needs to be re-done
    constructor(name_char)
    {
        //let json_char = gf.getJSONFile("./data/characters/" + name_char + ".json");

        this.name = name_char;
        this.hp = 16; this.maxHp = 16;
        this.morale = 8; this.maxMorale = 8;
        this.ap = 8; this.maxAp = 12;
        this.mp = 12; this.maxMp = 12;

        this.triumph = 0;

        this.status = [];



        this.satiety = 0;
        this.isDirty = false;
        this.isGuardian = false;
        this.isImmunized = false;

        skills.reset(this);

        this.inventory = [];

        this.inventory.push(items.c("itrakie"));
        this.location = 6;    // TODO: location needs to point towards the room the character spawned in
        this.lastLocation = undefined;

        // TODO: The special object holds the value of any special points (IT Expert, Technician...) a character may have?


        //Characters also have positions on Commander, Comms Manager and NERON Administrator lists
    }

    gainAp(v)
    {
        this.ap += v;
        if (this.ap > this.maxAp)
        {
            this.ap = this.maxAp;
        }
        if (this.ap < 0)
        {
            this.ap = 0;
        }
    }

    gainMp(v)
    {
        this.mp += v;
        if (this.mp > this.maxMp)
        {
            this.mp = this.maxMp;
        }
        if (this.mp < 0)
        {
            this.mp = 0;
        }
    }

    gainHp(v)
    { // This function returns true if HP reaches 0.
        this.hp += v;
        if (this.hp > this.maxHp)
        {
            this.hp = this.maxHp;
        }

        if (this.hp <= 0)
        {
            // You die !
            return true;
        }

        return false;
    }

    gainMorale(v)
    { // This function returns true if morale reaches 0.
        if (!this.isMush)
        {
            this.morale += v;
        }

        if (this.morale > this.maxMorale)
        {
            this.morale = this.maxMorale;
        }

        if (this.morale < 0)
        {
            this.morale = 0;
        }

        if (this.morale <= 0)
        {
            return true;
        }

        if (this.morale > 4)
        {
            this.gainStatus(status.ret("demoralized"), 'r');
            this.gainStatus(status.ret("suicidal"), 'r');
        }
        else if (this.morale <= 1)
        {
            this.gainStatus(status.ret("suicidal"), 'a');
            this.gainStatus(status.ret("demoralized"), 'r');
        }
        else if (this.morale <= 4)
        {
            this.gainStatus(status.ret("demoralized"), 'a');
            this.gainStatus(status.ret("suicidal"), 'r');
        }

        return false;
    }

    // The gainStatus method either adds or removes a status effect to a character.
    gainStatus(status, ar)
    {
        let i;
        let exists = false;
        if (ar === 'a') {
            for (i of this.status) {
                if (i === status) {
                    exists = true;
                }
            }
            if (!exists) {
                this.status.push(status);
                status.Permanent(this, 'a');
            }
        } else if (ar === 'r') {
            for (i of this.status) {
                if (i === status) {
                    exists = true;
                    break;
                }
            }
            if (exists) {
                status.Permanent(this, 'r');
                this.status.splice(i, 1);
            }
        }
    }

    // hasStatus verifies if a status is owned by the character, either by name or object
    hasStatus(testedStatus) {
        if (Array.isArray(testedStatus)) {
          testedStatus = status.ret(testedStatus);

        }
        for (let i of this.status) {
            if (i === testedStatus) {
                return true;
            }
        }
        return false;
    }

    // hasSkill checks if the character posesses a given skill, either by name (JSON filename) or ID
    hasSkill(skillName) {
      let x;
      var attribute;
      if (isNaN(skillName)) {
        attribute = "name";
      } else {
        attribute = "id";
      }

      for (x of this.skills) {
        if (x[attribute] == skillName) {
          return true;
        }
      }
      for (x of this.skillsMush) {
        if (x[attribute] == skillName) {
          return true;
        }
      }

      return false;
    }
}
