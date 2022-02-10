import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { IsometricCoordinates, CartesianCoordinates, IsometricDistance } from "@/game/types";
import { Equipment } from "@/entities/Equipment";
import store from "@/store";
import WhiteOutlinePipeline from "@/game/pipeline/shader";
import InteractObject from "@/game/objects/interactObject";
import IsometricGeom from "@/game/objects/isometricGeom";
import Vector2 = Phaser.Math.Vector2;


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


        //const groundShape = new IsometricGeom({ x: this.x, y: this.y }, { x: this.width, y: this.height });
        const groundShape = new Phaser.Geom.Polygon(
            [
                new Vector2(this.x - this.width/2, this.y - this.height/2),
                new Vector2(this.x + this.width/2, this.y - this.height/2),
                new Vector2(this.x + this.width/2, this.y + this.height/2),
                new Vector2(this.x - this.width/2, this.y + this.height/2),
            ]
        );

        if (this.equipment.isBroken) {
            const particles = this.scene.add.particles('smoke_particle');

            particles.createEmitter({
                x: 0,
                y: 0,
                lifespan: { min: 1000, max: 1200 },
                speed: { min: 10, max: 30 },
                angle: { min: 260, max: 280 },
                gravityY: 10,
                scale: { start: 0.5, end: 2, ease: 'Quad.easeIn' },
                alpha: { start: 0.5, end: 0, ease: 'Quad.easeIn' },
                tint: [ 0x666666, 0xFFFFFF, 0x10EEEEEE ],
                quantity: 1,
                //@ts-ignore
                emitZone: { type: 'random', source: this }
            });
        }
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
