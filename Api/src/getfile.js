"use strict";
// jshint node: true
// jshint esversion: 6

var fs = require('fs');

exports.getJSONFile = function (filepath) {
  let promiseFile = new Promise ((resolve, reject) => {
    try {
      fs.readFile(filepath, function (err, data) {
        if (err && err.code !== "ENOENT") throw err;
        else if (err) {
          console.log("missing file " + filepath);
          reject(err);
        } else {
          data = JSON.parse(data);
          resolve(data);
        }

      });
    } catch (err) {
        console.log("File error (not ENOENT)");
        console.log(err);
        reject(err);
    }
  });

  return promiseFile;
};
