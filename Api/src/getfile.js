'use strict';
// jshint node: true
// jshint esversion: 6

const fs = require('fs');

exports.getJSONFile = function (filepath) {
    const promiseFile = new Promise((resolve, reject) => {
        try {
            fs.readFile(filepath, (err, data) => {
                if (err && err.code !== 'ENOENT') throw err;
                else if (err) {
                    console.log('missing file ' + filepath);
                    reject(err);
                } else {
                    data = JSON.parse(data);
                    resolve(data);
                }
            });
        } catch (err) {
            console.log('File error (not ENOENT)');
            console.log(err);
            reject(err);
        }
    });

    return promiseFile;
};
