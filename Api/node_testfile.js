// jshint esversion: 6

const http = require('http');
const dae = require('./src/class_daedalus');

const ship = dae.CreateShip('base_layout');

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

http.createServer((req, res) => {
    res.writeHead(200, {'Content-Type': 'text/html'});
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
