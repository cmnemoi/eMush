import * as Phaser from "phaser";
import Vector2 = Phaser.Math.Vector2;
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import { Door as DoorEntity } from "@/entities/Door";

import InteractObject from "@/game/objects/interactObject";


//this shape is a polygon (in cartesian coordinates) that fit a rectangle in isometric frame
//it takes isometric coordinates of the center of the rectangle and its shape {width, height} in isometric coordinates
export default class IsometricGeom extends Phaser.Geom.Polygon implements Phaser.Types.GameObjects.Particles.RandomZoneSource {
    private iso_size : IsometricCoordinates;
    private iso_coords: IsometricCoordinates;
    private iso_Array:  Array<IsometricCoordinates>;
    private cart_Array: Array<CartesianCoordinates>;

    constructor(
        iso_coords: IsometricCoordinates,
        iso_size: IsometricCoordinates
    )
    {
        const iso_Array = [
            new IsometricCoordinates(iso_coords.x - iso_size.x/2, iso_coords.y - iso_size.y/2),
            new IsometricCoordinates(iso_coords.x - iso_size.x/2, iso_coords.y + iso_size.y/2),
            new IsometricCoordinates(iso_coords.x + iso_size.x/2, iso_coords.y + iso_size.y/2),
            new IsometricCoordinates(iso_coords.x + iso_size.x/2, iso_coords.y - iso_size.y/2)
        ];

        super(iso_Array);

        this.iso_size = iso_size;
        this.iso_coords = iso_coords;

        this.iso_Array = iso_Array;

        this.cart_Array = [];
        this.iso_Array.forEach((isoCoords: IsometricCoordinates) => {
            this.cart_Array.push((<IsometricCoordinates>isoCoords).toCartesianCoordinates());
        });
    }

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
        return new Phaser.Geom.Polygon(this.cart_Array);
    }

    getIsoArray(): Array<IsometricCoordinates>
    {
        return this.iso_Array;
    }

    getMaxIso(): IsometricCoordinates
    {
        return this.iso_Array[2];
    }

    getMinIso(): IsometricCoordinates
    {
        return this.iso_Array[0];
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

    isGeomContainingGeom(isoGeom: IsometricGeom) {
        return this.getMinIso().x <= isoGeom.getMinIso().x &&
            this.getMaxIso().x >= isoGeom.getMaxIso().x &&
            this.getMaxIso().y >= isoGeom.getMaxIso().y &&
            this.getMinIso().y <= isoGeom.getMinIso().y;
    }
}
