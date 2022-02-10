import * as Phaser from "phaser";
import Vector2 = Phaser.Math.Vector2;
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates, IsometricCoordinates, IsometricDistance, toCartesianCoords } from "@/game/types";
import { Door as DoorEntity } from "@/entities/Door";

import InteractObject from "@/game/objects/interactObject";


export default class IsometricGeom extends Phaser.Geom.Polygon implements Phaser.Types.GameObjects.Particles.RandomZoneSource {
    private iso_size : IsometricDistance;
    private iso_coords: IsometricCoordinates;

    constructor(
        iso_coords: IsometricCoordinates,
        iso_size: IsometricDistance,
    )
    {
        const cart_coords = toCartesianCoords(iso_coords);

        super([
            new Vector2(cart_coords.x, cart_coords.y),
            new Vector2(cart_coords.x + iso_size.x, cart_coords.y-iso_size.x/2),
            new Vector2( cart_coords.x + iso_size.x + iso_size.y, cart_coords.y + (iso_size.y - iso_size.x)/2),
            new Vector2(cart_coords.x + iso_size.y, cart_coords.y + iso_size.y/2)
        ]);

        this.iso_size = iso_size;
        this.iso_coords =iso_coords;
    }

    //@ts-ignore
    getRandomPoint(point: Phaser.Geom.Point): Phaser.Geom.Point
    {
        const rand_X = Phaser.Math.Between(0, this.iso_size.x);
        const rand_Y = Phaser.Math.Between(0, this.iso_size.y);

        const cart_coords = toCartesianCoords(this.iso_coords);

        point.setTo(cart_coords.x + rand_X + rand_Y, cart_coords.y + (rand_Y - rand_X)/2);
        return point;
    }
}
