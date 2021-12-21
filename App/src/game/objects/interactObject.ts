import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { IsometricCoordinates, CartesianCoordinates, IsometricDistance } from "@/game/types";
import { Equipment } from "@/entities/Equipment";
import store from "@/store";
import DecorationObject from "@/game/objects/decorationObject";


/*eslint no-unused-vars: "off"*/
export default class InteractObject extends DecorationObject {
    protected equipment: Equipment;

    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_coords: IsometricCoordinates,
        tileset: Phaser.Tilemaps.Tileset,
        frame: number,
        equipment: Equipment,
        sceneAspectRatio: IsometricDistance
    )
    {
        super(scene, cart_coords, iso_coords, tileset, frame, equipment.key, sceneAspectRatio);

        this.equipment = equipment;

        this.setInteractive({ pixelPerfect: true });


        //If this is clicked then:
        this.on('pointerdown', function (pointer: Phaser.Input.Pointer, localX: number, localY: number, event: any) {
            store.dispatch('room/selectTarget', { target: equipment });
            event.stopPropagation(); //Need that one to prevent other effects
        });
    }
}
