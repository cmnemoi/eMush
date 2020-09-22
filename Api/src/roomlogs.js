'use strict';
// jshint node: true
// jshint esversion: 6

const lang = require('./setlang');

let lastLang = lang.getLang();

// ?
const gf = require('./getfile');
let logContent = gf.getJSONFile('data/logs/logs_' + lang.getLang() + '.json');

// This function is called at the beginning of every RoomLogs function
// Its purpose is to make sure the language used by the game is
// the current language.
// This implementation is perhaps not time-efficient enough.
const updateLang = function () {
    if (lang.getLang !== lastLang) {
        lastLang = lang.getLang;
        logContent = gf.getJSONFile('data/logs/logs_' + lastLang + '.json');
    }
};

exports.act = function (action, character, target) {
    updateLang();
    const content = logContent[action.toLowerCase];
    action = action.toUpperCase;

    let log;
    switch (action) {
        case 'MOVE':
            {
                const logexit = new RoomLog(
                    character.lastLocation,
                    character,
                    'public'
                );
                const logenter = new RoomLog(
                    character.location,
                    character,
                    'public'
                );

                logexit.content =
                    character.name + logContent.move_exit + character.location;
                logenter.content =
                    character.name +
                    logContent.move_enter +
                    character.lastLocation;
            }

            break;

        case 'DECRYOGENIZE':
        case 'LIE_DOWN':
        case 'GET_UP':
            log = new RoomLog(character.location, character, 'public');
            if (!Array.isArray(content)) log.content = character.name + content;
            else
                log.content =
                    character.name + content[Math.random * content.length];

            break;

        case 'ACCESS':
        case 'DROP':
        case 'PICK_UP':
            log = new RoomLog(character.location, character, 'public');

            log.content = character.name + content + target.name;
    }
};

exports.gains = function (character, resource, value) {
    updateLang();
    const log = new RoomLog(character.location, character, 'private');
    log.content = logContent.resource_gain + value + ' ' + resource;
};

class RoomLog {
    constructor(location, owner, privacy) {
        this.owner = owner;
        this.privacy = privacy;
        // if owner is Pariah and privacy is Covert privacy becomes Secret
        if (privacy === 'private') {
            this.seen = false;
        } else if (
            privacy === 'covert' /* && RoomHasNoCamera && ObserverNotNoticing*/
        ) {
            this.seen = false;
        } else if (
            privacy === 'secret' &&
            location.crew[1] === undefined /* && RoomHasNoCamera */
        ) {
            this.seen = false;
        } else {
            this.seen = true;
        }

        this.content = 'empty';
        this.date = new Date();
        location.logs.push(this);
    }
}

// https://www.youtube.com/watch?v=YNm3Ggv01Ns
