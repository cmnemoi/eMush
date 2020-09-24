'use strict';
// jshint node: true
// jshint esversion: 6
const gf = require('./getfile');

exports.c = function (name) {
    return gf
        .getJSONFile('data/equipment/' + name.toLowerCase() + '.json')
        .then(data => {
            return data;
        })
        .catch(() => {
            return {
                // placeholder equipment
            };
        });
};
