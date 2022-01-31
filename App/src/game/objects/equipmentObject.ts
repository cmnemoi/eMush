import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { IsometricCoordinates, CartesianCoordinates, IsometricDistance } from "@/game/types";
import { Equipment } from "@/entities/Equipment";
import store from "@/store";
import WhiteOutlinePipeline from "@/game/pipeline/shader";
import InteractObject from "@/game/objects/interactObject";

/*eslint no-unused-vars: "off"*/
export default class EquipmentObject extends InteractObject {
    public equipment: Equipment;

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

        //If this is clicked then:
        this.on('pointerdown', (pointer: Phaser.Input.Pointer, localX: number, localY: number, event: any) => {
            store.dispatch('room/selectTarget', { target: equipment });
        });
    }

    onHovering() {
        if (this.equipment.isBroken) {
            this.setPostPipeline('outline');
            const pipeline = this.postPipelines[0];
            //@ts-ignore
            pipeline.resetFromJSON({ thickness: 1, outlineColor: 0xff0000 });
        } else {
            super.onHovering();
        }
    }

    onSelected() {
        if (this.equipment.isBroken) {
            this.setPostPipeline('outline');
            const pipeline = this.postPipelines[0];
            //@ts-ignore
            pipeline.resetFromJSON({ thickness: 1, outlineColor: 0xff0000 });
        } else {
            super.onSelected();
        }
    }
}
