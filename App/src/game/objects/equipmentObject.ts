import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { IsometricCoordinates, CartesianCoordinates } from "@/game/types";
import { Equipment } from "@/entities/Equipment";
import store from "@/store";
import InteractObject from "@/game/objects/interactObject";
import IsometricGeom from "@/game/scenes/isometricGeom";


/*eslint no-unused-vars: "off"*/
export default class EquipmentObject extends InteractObject {
    public equipment: Equipment;

    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        tileset: Phaser.Tilemaps.Tileset,
        frame: number,
        equipment: Equipment,
        isAnimationYoyo: boolean,
        group: Phaser.GameObjects.Group | null = null
    )
    {
        super(scene, cart_coords, iso_geom, tileset, frame, equipment.key, isAnimationYoyo, group);

        this.equipment = equipment;

        //If this is clicked then:
        this.on('pointerdown', (pointer: Phaser.Input.Pointer, localX: number, localY: number, event: any) => {
            store.dispatch('room/selectTarget', { target: equipment });
        });


        // const graphics = this.scene.add.graphics();
        // graphics.lineStyle(5, 0xFFFFFF, 1.0);
        // graphics.fillStyle(0x00ff08, 0.5);
        // graphics.fillPoints(iso_geom.getCartesianPolygon().points, true);


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
                frequency: 100000/(this.width * this.height ),
                //@ts-ignore
                emitZone: { type: 'random', source: this }
            });
        }
    }


    setHoveringOutline() {
        if (this.equipment.isBroken) {
            this.setPostPipeline('outline');
            const pipeline = this.postPipelines[0];
            //@ts-ignore
            pipeline.resetFromJSON({ thickness: 1, outlineColor: 0xff0000 });
        } else {
            super.setHoveringOutline();
        }
    }

    setSelectedOutline()
    {
        if (this.equipment.isBroken) {
            this.setPostPipeline('outline');
            const pipeline = this.postPipelines[0];
            //@ts-ignore
            pipeline.resetFromJSON({ thickness: 1, outlineColor: 0xff0000 });
        } else {
            super.setSelectedOutline();
        }
    }
}
