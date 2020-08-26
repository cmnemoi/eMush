"use strict";
// jshint node: true
// jshint esversion: 6

/*
An action always has a cost, an execution sequence, and potential logs.
*/

exports.move = function (character, door) {
  door.move(character);

};

exports.act = function (action, character, target) {


};


/*Chers amis, le système cryogénique vient de subir une légère avarie. C'est tout à fait mineur : toute future utilisation suspendra définitivement les fonctions vitales du sujet. */
