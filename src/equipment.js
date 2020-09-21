"use strict";
// jshint node: true
// jshint esversion: 6
var gf = require('./getfile');

exports.c = function (name)
{

    return gf.getJSONFile("data/equipment/" + name.toLowerCase() + ".json")
    .then(function (data) {return data;})
    .catch(function (data) {
      return {
        // placeholder equipment
      };
    });
};
