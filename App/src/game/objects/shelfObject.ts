import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import store from '@/store/index';
import { IsometricCoordinates, CartesianCoordinates, CartesianDistance, IsometricDistance, toIsometricCoords } from "@/game/types";
import DecorationObject from "@/game/objects/decorationObject";
import InteractObject from "@/game/objects/interactObject";

/*eslint no-unused-vars: "off"*/
export default class ShelfObject extends InteractObject {
    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_coords: IsometricCoordinates,
        tileset: Phaser.Tilemaps.Tileset,
        frame: number,
        name: string,
        sceneAspectRatio: IsometricDistance)
    {
        super(scene, cart_coords, iso_coords, tileset, frame, 'shelf', sceneAspectRatio);


        //If this is clicked then:
        this.on('pointerdown', function (pointer: Phaser.Input.Pointer, localX: number, localY: number, event: any) {
            store.dispatch('room/openInventory');
        });

        //if clicked outside
        this.scene.input.on('pointerdown', (pointer: Phaser.Input.Pointer, currentlyOver: Phaser.GameObjects.GameObject[]) => {
            if (!currentlyOver.includes(this)) {
                store.dispatch('room/closeInventory');
            }
        }, this);
    }
}
