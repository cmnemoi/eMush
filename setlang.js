"use strict";
// jshint node: true
// jshint esversion: 6




let lang = "en";

exports.setLang = function (nlang) {
  switch (nlang) {
    case "en":
    case "es":
    case "fr":
    lang = nlang;
  }
};

exports.getLang =  function () {
  return lang;
};

exports.getLangId = function () {
  switch (lang) {
    case "en":
      return 0;
    case "fr":
      return 1;
    case "es":
      return 2;
  }
};
