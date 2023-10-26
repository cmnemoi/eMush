import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { IsometricCoordinates, CartesianCoordinates } from "@/game/types";
import { Equipment } from "@/entities/Equipment";
import store from "@/store";
import InteractObject, { InteractionInformation } from "@/game/objects/interactObject";
import IsometricGeom from "@/game/scenes/isometricGeom";


/*eslint no-unused-vars: "off"*/
export default class EquipmentObject extends InteractObject {
    public equipment: Equipment;
    private particles: Phaser.GameObjects.Particles.ParticleEmitterManager | null = null;
    private initCoordinates: CartesianCoordinates;
    private isShaking: boolean;

    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        tileset: Phaser.Tilemaps.Tileset,
        frame: number,
        isFlipped: { x: boolean, y: boolean},
        equipment: Equipment,
        collides: boolean,
        isAnimationYoyo: boolean,
        group: Phaser.GameObjects.Group | null = null,
        interactionInformation: InteractionInformation | null = null
    )
    {
        super(scene, cart_coords, iso_geom, tileset, frame, equipment.key, isFlipped, collides, isAnimationYoyo, group, interactionInformation);

        this.initCoordinates = new CartesianCoordinates(this.x, this.y);
        this.isShaking = false;
        this.equipment = equipment;

        //If this is clicked then:
        this.on('pointerdown', (pointer: Phaser.Input.Pointer, localX: number, localY: number, event: any) => {
            store.dispatch('room/selectTarget', { target: this.equipment });
        });

        this.handleBroken();


        // const graphics = this.scene.add.graphics();
        // graphics.lineStyle(5, 0xFFFFFF, 1.0);
        // graphics.fillStyle(0x00ff08, 0.5);
        // graphics.fillPoints(iso_geom.getCartesianPolygon().points, true);
    }

    updateEquipment(equipment: Equipment | null = null) {
        if (equipment !== null) {
            this.equipment = equipment;
        }

        this.handleBroken();
    }

    handleBroken(): void
    {
        if (this.equipment.isBroken && this.particles === null) {
            this.particles = this.scene.add.particles('smoke_particle');

            this.particles.createEmitter({
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
                emitZone: { type: 'random', source: this.isoGeom }
            });
        } else if (this.particles !== null && !this.equipment.isBroken) {
            this.particles.destroy();
            this.particles = null;
        }

        if (this.particles !== null) {
            this.particles.setDepth(this.depth + 1);
        }
    }

    update(time: number, delta: number):void
    {
        this.flyAnimation();
    }

    flyAnimation(): void
    {
        var displacement =  Math.sin(Math.random() * 2 * Math.PI);

        if (this.isShaking) {
            displacement =  Math.round(Math.sin(Math.random() * 2 * Math.PI) * 2);
        }

        if (
            Math.random() > 0.97 && !this.isShaking ||
            Math.random() > 0.95 && this.isShaking
        ) {
            this.isShaking =  !(this.isShaking);
        }

        const orientation = Math.random();
        if (orientation > 0.3) {
            this.x = (this.x + displacement);

            if (this.x > this.initCoordinates.x + 10) {
                this.x = this.initCoordinates.x + 10
            }
            if (this.x < this.initCoordinates.x - 10) {
                this.x = this.initCoordinates.x - 10
            }

        } else {
            this.y = (this.y + displacement);

            if (this.y > this.initCoordinates.y + 40) {
                this.y = this.initCoordinates.y + 40
            }
            if (this.y < this.initCoordinates.y - 5) {
                this.y = this.initCoordinates.y - 5
            }
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

    delete()
    {
        this.particles?.destroy();
        super.delete();
    }
}
