'use strict';
// jshint node: true
// jshint esversion: 8

// const logs = require('./roomlogs');
const items = require('./items');
const doors = require('./doors');
const gf = require('./getfile');

exports.c = function (name, id) {
    return new Room(name, id);
};

class Room {
    constructor(vname, id) {
        this.name = vname;
        this.id = id;
        this.crew = [];
        this.objects = [];
        this.equipment = [];
        this.doors = [];
        this.fire = false;
        this.logs = [];
        this.doors = [];
    }
}

/*
function pushNewRoom (ship, name_json_folder, elem, id) {
  let promiseRoom = new Promise ((resolve, reject) => {
    let newRoom = gf.getJSONFile(name_json_folder + '/' + elem + ".json")
      .then(
        (data) => {
          data.name = elem;
          data.crew = [];
          //console.log(data);
          ship.rooms.push(data);
          return data;
      }).then((newRoom) => {
          resolve(newRoom);
      }).catch((reject) => {
        let newRoom = new Room (elem, id);
        ship.rooms.push(newRoom);
        resolve(newRoom);
        if (false) {reject(newRoom);}
      });
  });
  return promiseRoom;

}
*/
function pushNewRoom(ship, name_json_folder, elem, id) {
    return gf
        .getJSONFile(name_json_folder + '/' + elem + '.json')
        .then(newRoom => {
            newRoom.name = elem;
            newRoom.crew = [];
            ship.rooms.push(newRoom);
            return newRoom;
        })
        .catch(() => {
            const newRoom = new Room(elem, id);
            ship.rooms.push(newRoom);
            return newRoom;
        });
}

exports.generateRoomsByLayout = function (name_json_folder, ship) {
    if (ship.rooms) {
        return;
    }

    ship.rooms = [];
    console.log('reached step 1');
    // First execution step: get a JSON file
    gf.getJSONFile(name_json_folder + '/_base_layout.json').then(shipmode => {
        console.log('reached step 2');
        // Second execution step: Room creation, resource addition

        Promise.allSettled(
            shipmode.layout.map((elem, index) => {
                return pushNewRoom(ship, name_json_folder, elem, index);
            })
        )
            .then(() => {
                console.log('reached step 3');
                // Third execution step: Room sorting, in case of potential delays in the last loop?
                // TODO: Pill effect randomization, fruit effect randomization
                ship.rooms.sort((a, b) => {
                    return a.id - b.id;
                });
                console.log(ship.rooms.length + ' rooms in ship');
                return Promise.resolve(shipmode);
            })
            .then(shipmode => {
                console.log('reached step 4');
                // Fourth execution step: Door generation, room population, preset addition
                for (const x of ship.rooms) {
                    console.log(`working on room ${x.name}`);
                    for (let y of x.doors) {
                        y = doors.c(x, ship.rooms[y]);
                    }
                    x.bpObjects = x.objects;
                    x.objects = [];

                    for (
                        let y = 0, itemAmount = 1;
                        x.bpObjects[y] !== undefined;
                        y++
                    ) {
                        if (!isNaN(x.bpObjects[y])) {
                            itemAmount = x.bpObjects[y];
                        } else {
                            for (let z = 0; z < itemAmount; z++) {
                                // add an object lol
                                items.c(x.bpObjects[y]).then(data => {
                                    x.objects.push(data);
                                });
                            }
                            itemAmount = 1;
                        }
                    }
                    // for (const y of x.equipment) {
                    //   // TODO: equipment generation
                    // }
                }

                ship.presets = {
                    SPAWN_ROOM:
                        ship.rooms[
                            !isNaN(shipmode.starting_room.id)
                                ? shipmode.starting_room.id
                                : 0
                        ],
                    OXYGEN_ROOM: [],
                    GRAVITY_SIMULATOR: [],
                    STORAGE_ROOM: [],
                };

                for (const x of shipmode.storages) {
                    ship.presets.STORAGE_ROOM.push(ship.rooms[x]);
                }
                for (const x of shipmode.oxygen_room) {
                    ship.presets.OXYGEN_ROOM.push(ship.rooms[x.id]);
                }
                for (const x of shipmode.gravity_simulator) {
                    ship.presets.GRAVITY_SIMULATOR.push(ship.rooms[x.id]);
                }

                // TODO: add starting items

                // if (shipmode.resources) {
                // }
                return Promise.resolve(shipmode);
            })
            .catch(errRejection => {
                // Error handling
                console.log('an as-of-yet unhandled error occured');
                console.log(errRejection);
            });

        // TODO: Presets operations (well, some of them anyways)
    });
};
