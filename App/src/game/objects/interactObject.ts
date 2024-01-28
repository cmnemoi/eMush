import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import DecorationObject from "@/game/objects/decorationObject";
import IsometricGeom from "@/game/scenes/isometricGeom";
import EquipmentObject from "@/game/objects/equipmentObject";
import { NavMeshGrid } from "@/game/scenes/navigationGrid";

export default class InteractObject extends DecorationObject {
    protected interactionInformation: InteractionInformation | null;

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
        group: Phaser.GameObjects.Group | null = null,
        interactionInformation: InteractionInformation | null = null
    )
    {
        super(scene, cart_coords, iso_geom, tileset, frame, name, isFlipped, collides, isAnimationYoyo, group);

        this.createInteractionArea();

        this.interactionInformation = interactionInformation;


        this.on('pointerover', () => {
            this.onHovering();

        }, this);

        this.on('pointerout', () => { this.onPointerOut(); }, this);
    }

    createInteractionArea():void
    {
        this.setInteractive({ pixelPerfect: true });
    }

    onHovering(): void
    {
        if (!this.isSelected()) {
            this.setHoveringOutline();
            if (this.group !== null) {
                this.group.getChildren().forEach((object: Phaser.GameObjects.GameObject) => {
                    if (object instanceof InteractObject) {
                        object.setHoveringOutline();
                    }
                });
            }
        }
    }

    onSelected(): void
    {
        this.setSelectedOutline();
        if (this.group !== null) {
            this.group.getChildren().forEach((object: Phaser.GameObjects.GameObject) => {
                if (object instanceof InteractObject) {
                    object.setSelectedOutline();
                }
            });
        }
    }

    onPointerOut(): void
    {
        if (!this.isSelected()) {
            this.removeOutline();
            if (this.group !== null) {
                this.group.getChildren().forEach((object: Phaser.GameObjects.GameObject) => {
                    if (object instanceof InteractObject && object !== this) {
                        object.removeOutline();
                    }
                });
            }
        }
    }

    onClickedOut(): void
    {
        this.removeOutline();
        if (this.group !== null) {
            this.group.getChildren().forEach((object: Phaser.GameObjects.GameObject) => {
                if (object instanceof InteractObject && object !== this) {
                    object.removeOutline();
                }
            });
        }
    }

    setHoveringOutline(): void
    {
        this.setPostPipeline('outline');
        const pipeline = this.postPipelines[0];
        //@ts-ignore
        pipeline.resetFromJSON({ thickness: 1, outlineColor: 0xffffff });
    }

    setSelectedOutline(): void
    {
        this.setPostPipeline('outline');
        const pipeline = this.postPipelines[0];
        //@ts-ignore
        pipeline.resetFromJSON({ thickness: 1, outlineColor: 0x00CC00 });
    }

    removeOutline(): void
    {
        this.resetPostPipeline();
    }

    isSelected(): boolean
    {
        const selectedObject = (<DaedalusScene>this.scene).selectedGameObject;

        return selectedObject !== null && selectedObject.name === this.name;
    }

    getRandomPoint(point: Phaser.Geom.Point): Phaser.Geom.Point {

        const x = Phaser.Math.Between(0, this.width - 1);
        const y = Phaser.Math.Between(0, this.height - 1);

        return point.setTo(x + this.x, - y + this.y);
    }

    getInteractionInformation(): InteractionInformation | null
    {
        return this.interactionInformation;
    }

    getInteractibleObject(): InteractObject | null
    {
        if (this.group !== null) {
            for (let i = 0; i < this.group.getChildren().length; i++) {
                const object = this.group.getChildren()[i];
                if (object instanceof InteractObject && object.getInteractionInformation() !== null) {
                    return object;
                }
            }
        } else if (this.getInteractionInformation() !== null) {
            return this;
        }

        return null;
    }

    getInteractCoordinates(navMesh: NavMeshGrid): IsometricCoordinates
    {
        const interactEquipment = this.getInteractibleObject();

        if (interactEquipment !== null) {
            return navMesh.getClosestPoint(interactEquipment.isoGeom.getIsoCoords());
        } else {
            return navMesh.getClosestPoint(this.isoGeom.getIsoCoords());
        }
    }

}


export type InteractionInformation = {
    sitAnimation: string,
    sitDepth: number,
    sitFlip: boolean,
    sitX: number,
    sitY: number,
    sitAutoTrigger: boolean
}
