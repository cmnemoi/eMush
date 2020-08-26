// jshint esversion: 6

let http = require('http');
let dae = require('./class_daedalus');
let fs = require('fs');
let gf = require('./getfile');


let ship = dae.CreateShip("base_layout");

/*


ship.decryogenize("Jin Su");
ship.decryogenize("Frieda");
ship.decryogenize("Kuan Ti");
ship.decryogenize("Janice");
ship.decryogenize("Roland");
ship.decryogenize("Hua");
ship.decryogenize("Paola");
ship.decryogenize("Chao");
ship.decryogenize("Finola");
ship.decryogenize("Stephen");
ship.decryogenize("Ian");
ship.decryogenize("Chun");
ship.decryogenize("Raluca");
ship.decryogenize("Gioele");
ship.decryogenize("Eleesha");
ship.decryogenize("Terrence");
ship.hull = 100000;
ship.oxygen = 6;
*/

/*
for (let p of ship.crew)
{
    p.location = ship.room[6];
}
*/

//ship.kill(ship.crew[0], "Administrator");

http.createServer(function (req, res)
{
    res.writeHead(200, { 'Content-Type': 'text/html' });
    res.write('<br>' + req.url + '<br>');

    /*
    if (req.url === "/cyclechange")
    {
        ship.cyclechange();
    }
    */
    dae.ShipShowFullStatus(ship, res);
    console.log(ship);


    res.end();
}).listen(8080);
