import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates } from "@/game/types";
import { Equipment } from "@/entities/Equipment";
import { InteractionInformation } from "@/game/objects/interactObject";
import IsometricGeom from "@/game/scenes/isometricGeom";
import EquipmentObject from "@/game/objects/equipmentObject";


/*eslint no-unused-vars: "off"*/
export default class PatrolShipObject extends EquipmentObject {
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
        super(scene, cart_coords, iso_geom, tileset, frame, isFlipped, equipment, collides, isAnimationYoyo, group, interactionInformation);

        console.log('coucou');
        this.isShaking = false;
        this.initCoordinates = new CartesianCoordinates(this.x, this.y);
    }

    update(time: number, delta: number):void
    {
        this.flyAnimation();
    }

    flyAnimation(): void
    {
        let displacement =  Math.sin(Math.random() * 2 * Math.PI);

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
                this.x = this.initCoordinates.x + 10;
            }
            if (this.x < this.initCoordinates.x - 10) {
                this.x = this.initCoordinates.x - 10;
            }

        } else {
            this.y = (this.y + displacement);

            if (this.y > this.initCoordinates.y + 40) {
                this.y = this.initCoordinates.y + 40;
            }
            if (this.y < this.initCoordinates.y - 5) {
                this.y = this.initCoordinates.y - 5;
            }
        }
    }

    setHoveringOutline() {}

    setSelectedOutline() {}
}
