import * as Phaser from "phaser";
import Vector2 = Phaser.Math.Vector2;
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import { Door as DoorEntity } from "@/entities/Door";

import InteractObject from "@/game/objects/interactObject";


//this shape is a polygon (in cartesian coordinates) that fit an rectangle in isometric frame
//it takes isometric coordinates of the center of the rectangle and its shape {width, height} in isometric coordinates
export default class IsometricGeom extends Phaser.Geom.Polygon implements Phaser.Types.GameObjects.Particles.RandomZoneSource {
    private iso_size : IsometricCoordinates;
    private iso_coords: IsometricCoordinates;
    private iso_array:  Array<IsometricCoordinates>;
    private cart_array: Array<CartesianCoordinates>;

    constructor(
        iso_coords: IsometricCoordinates,
        iso_size: IsometricCoordinates,
    )
    {
        const iso_array = [
            new IsometricCoordinates(iso_coords.x - iso_size.x/2, iso_coords.y - iso_size.y/2),
            new IsometricCoordinates(iso_coords.x - iso_size.x/2, iso_coords.y + iso_size.y/2),
            new IsometricCoordinates(iso_coords.x + iso_size.x/2, iso_coords.y + iso_size.y/2),
            new IsometricCoordinates(iso_coords.x + iso_size.x/2, iso_coords.y - iso_size.y/2),
        ];

        super(iso_array);

        this.iso_size = iso_size;
        this.iso_coords = iso_coords;

        this.iso_array = iso_array;

        this.cart_array = [];
        this.iso_array.forEach((isoCoords: IsometricCoordinates) => {
            this.cart_array.push((<IsometricCoordinates>isoCoords).toCartesianCoordinates());
        });
    }

    //@ts-ignore
    getRandomPoint(point: Phaser.Geom.Point): Phaser.Geom.Point
    {
        const rand_Iso = new IsometricCoordinates(
            Phaser.Math.Between(this.iso_coords.x - this.iso_size.x/2, this.iso_coords.x + this.iso_size.x/2),
            Phaser.Math.Between(this.iso_coords.y - this.iso_size.y/2, this.iso_coords.y + this.iso_size.y/2)
        );

        const randCart = rand_Iso.toCartesianCoordinates();
        point.setTo(randCart.x, randCart.y);

        return point;
    }

    getCartesianPolygon(): Phaser.Geom.Polygon
    {
        return new Phaser.Geom.Polygon(this.cart_array);
    }

    getIsoArray(): Array<IsometricCoordinates>
    {
        return this.iso_array;
    }

    getMaxIso(): IsometricCoordinates
    {
        return this.iso_array[2];
    }

    getMinIso(): IsometricCoordinates
    {
        return this.iso_array[0];
    }

    getIsoSize(): IsometricCoordinates
    {
        return this.iso_size;
    }
    getIsoCoords(): IsometricCoordinates
    {
        return this.iso_coords;
    }

    enlargeGeom(buffer: number): IsometricGeom
    {
        return new IsometricGeom(this.iso_coords, new IsometricCoordinates(this.iso_size.x + buffer * 2, this.iso_size.y + buffer * 2));
    }

    newPosition(newIsoCoords: IsometricCoordinates): IsometricGeom
    {
        return new IsometricGeom(newIsoCoords, this.iso_size);
    }

    isPointInGeom(isoCoords: IsometricCoordinates) {
        return (
            this.getMaxIso().x >= isoCoords.x &&
            this.getMinIso().x <= isoCoords.x &&
            this.getMaxIso().y >= isoCoords.y &&
            this.getMinIso().y <= isoCoords.y
        );
    }

    isGeomOverlapingGeom(isoGeom: IsometricGeom) {
        // no horizontal overlap
        if (this.getMinIso().x >= isoGeom.getMaxIso().x || this.getMaxIso().x <= isoGeom.getMinIso().x) return false;

        // no vertical overlap
        if (this.getMaxIso().y <= isoGeom.getMinIso().y || this.getMinIso().y >= isoGeom.getMaxIso().y) return false;

        return true;
    }
}
