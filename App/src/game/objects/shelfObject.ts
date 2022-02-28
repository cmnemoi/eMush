import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import store from '@/store/index';
import { IsometricCoordinates, CartesianCoordinates } from "@/game/types";
import DecorationObject from "@/game/objects/decorationObject";
import InteractObject from "@/game/objects/interactObject";
import IsometricGeom from "@/game/scenes/isometricGeom";

/*eslint no-unused-vars: "off"*/
export default class ShelfObject extends InteractObject {
    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        tileset: Phaser.Tilemaps.Tileset,
        frame: number,
        name: string,
        collides: boolean,
        isAnimationYoyo: boolean,
        group: Phaser.GameObjects.Group | null = null
    ) {
        super(scene, cart_coords, iso_geom, tileset, frame, 'shelf', collides, isAnimationYoyo, group);


        //If this is clicked then:
        this.on('pointerdown', function (pointer: Phaser.Input.Pointer, localX: number, localY: number, event: any) {
            store.dispatch('room/openInventory');
        });
    }
}
