import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import store from '@/store/index';
import { CartesianCoordinates } from "@/game/types";
import InteractObject from "@/game/objects/interactObject";
import IsometricGeom from "@/game/scenes/isometricGeom";

export default class ShelfObject extends InteractObject {
    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        tileset: Phaser.Tilemaps.Tileset,
        frame: number,
        name: string,
        isFlipped: { x: boolean, y: boolean},
        collides: boolean,
        isAnimationYoyo: boolean,
        group: Phaser.GameObjects.Group | null = null
    ) {
        super(scene, cart_coords, iso_geom, tileset, frame, 'shelf', isFlipped, collides, isAnimationYoyo, group);

        //If this is clicked then:
        this.on('pointerdown', function (pointer: Phaser.Input.Pointer, localX: number, localY: number, event: any) {
            store.dispatch('room/openInventory');
        });
    }
}
