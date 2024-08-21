import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import store from '@/store/index';
import { CartesianCoordinates } from "@/game/types";
import InteractObject from "@/game/objects/interactObject";
import IsometricGeom from "@/game/scenes/isometricGeom";
import mushTextureProperties from "@/game/tiled/mushTextureProperties";

export default class ShelfObject extends InteractObject {
    constructor(
        scene: DaedalusScene,
        name: string,
        textureProperties: mushTextureProperties,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        collides: boolean,
        group: Phaser.GameObjects.Group | null = null
    ) {
        super(scene, name, textureProperties, cart_coords, iso_geom, collides, group);

        //If this is clicked then:
        this.on('pointerdown', function (pointer: Phaser.Input.Pointer, localX: number, localY: number, event: any) {
            store.dispatch('room/openInventory');
        });
    }
}
