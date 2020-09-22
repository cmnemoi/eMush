'use strict';
// jshint node: true
// jshint esversion: 6
const lang = require('./setlang');
const gf = require('./getfile');

/*
This constructor creates an object that contains the data of a game item
*/
exports.c = function (name) {
    return gf
        .getJSONFile('data/items/' + name + '.json')
        .then(data => {
            data.name = data['name_' + lang.getLang()];
            return data;
        })
        .catch(() => {
            console.log(
                `Item creation error: ITEM ${name} NOT FOUND, or another error`
            );
            return {
                id: -1,
                file: `ITEM ${name} could not be created`,
                type: 'RATIONS',
                name: 'Breakfast',
                can_drop: true,
                can_move: true,
            };
        });
};
