import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import { Player } from "@/entities/Player";
import store from "@/store";
import InteractObject from "@/game/objects/interactObject";
import Tileset = Phaser.Tilemaps.Tileset;
import IsometricGeom from "@/game/scenes/isometricGeom";
import { NavMeshGrid } from "@/game/scenes/navigationGrid";
import EquipmentObject from "@/game/objects/equipmentObject";

export default class CharacterObject extends InteractObject {
    public player : Player;
    protected navMesh: NavMeshGrid;
    public interactedEquipment: InteractObject | null;

    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        isoGeom: IsometricGeom,
        player: Player
    ) {
        super(
            scene,
            cart_coords,
            isoGeom,
            new Tileset('character', 0, 48, 32),
            0,
            player.character.key,
            { x: false, y: false },
            false,
            false
        );

        this.player = player;
        this.navMesh = scene.navMeshGrid;
        this.interactedEquipment = null;

        this.setPositionFromFeet(cart_coords);

        scene.physics.world.enable(this);

        this.createAnimations();

        this.applyEquipmentInteraction();

        //If this is clicked then:
        this.on('pointerdown', (pointer: Phaser.Input.Pointer, localX: number, localY: number, event: any) => {
            store.dispatch('room/selectTarget', { target: this.player });
        });

        this.checkPositionDepth();
    }

    updatePlayer(player: Player | null = null)
    {
        if (player !== null) {this.player = player;}

        this.applyEquipmentInteraction();
    }

    applyEquipmentInteraction(): void
    {
        const targetBed = this.player.isLyingDown();
        const room = this.player.room;

        if (targetBed !== null) {
            const bed = (<DaedalusScene>this.scene).findObjectByNameAndId(targetBed.key, targetBed.id);

            if (bed !== null) {
                this.applyEquipmentInteractionInformation(bed);
            }
        } else if (room && room.type === 'space') {
            this.play('space_giggle');
        } else {
            if (this.interactedEquipment !== null) {
                const interactCoordinates = this.interactedEquipment.getInteractCoordinates(this.navMesh);
                this.setPositionFromFeet(interactCoordinates.toCartesianCoordinates());
                this.interactedEquipment = null;
            }
            //Set the initial sprite randomly such as it faces the screen
            if (Math.random() > 0.5) {
                this.flipX = true;
            }
            this.anims.play('right');
            this.checkPositionDepth();
        }
    }

    updateNavMesh(): void
    {
        this.navMesh = (<DaedalusScene>this.scene).navMeshGrid;
    }


    applyTexture(tileset: Phaser.Tilemaps.Tileset, name: string) {
        this.setTexture('character', this.tiledFrame);
    }

    applyEquipmentInteractionInformation(equipment: InteractObject)
    {
        const interactionInformation = equipment.getInteractionInformation();

        if (interactionInformation !== null) {
            this.flipX = interactionInformation.sitFlip;

            const equiCartCoords = new CartesianCoordinates(equipment.x, equipment.y);
            const charIsoCoords = new IsometricCoordinates(
                equiCartCoords.toIsometricCoordinates().x  + interactionInformation.sitX,
                equiCartCoords.toIsometricCoordinates().y + interactionInformation.sitY
            );
            this.setPositionFromIsometricCoordinates(charIsoCoords);

            this.depth = equipment.depth + interactionInformation.sitDepth;

            this.anims.play(interactionInformation.sitAnimation);

            this.interactedEquipment = equipment;
        }
    }

    createAnimations(): void
    {
        this.anims.create({
            key: 'move_right',
            frames: this.anims.generateFrameNames('character', {
                prefix: this.player.character.key,
                start: 5,
                end: 10
            }),
            frameRate: 10,
            repeat: -1
        });
        this.anims.create({
            key: 'move_left',
            frames: this.anims.generateFrameNames('character', {
                prefix: this.player.character.key,
                start: 11,
                end: 16
            }),
            frameRate: 10,
            repeat: -1
        });

        this.anims.create({
            key: 'right',
            frames: this.anims.generateFrameNames('character', {
                prefix: this.player.character.key,
                start: 1,
                end: 1
            }),
            frameRate: 1
        });
        this.anims.create({
            key: 'left',
            frames: this.anims.generateFrameNames('character', {
                prefix: this.player.character.key,
                start: 2,
                end: 2
            }),
            frameRate: 1
        });

        this.anims.create({
            key: 'sit_front',
            frames: this.anims.generateFrameNames('character', {
                prefix: this.player.character.key,
                start: 3,
                end: 3
            }),
            frameRate: 1
        });
        this.anims.create({
            key: 'sit_back',
            frames: this.anims.generateFrameNames('character', {
                prefix: this.player.character.key,
                start: 4,
                end: 4
            }),
            frameRate: 1
        });
        this.anims.create({
            key: 'lie_down',
            frames: this.anims.generateFrameNames('character', {
                prefix: this.player.character.key,
                start: 18,
                end: 18
            }),
            frameRate: 1
        });

        this.anims.create({
            key: 'space_giggle',
            frames: this.anims.generateFrameNames('character', {
                prefix: 'space_suit',
                start: 1,
                end: 5
            }),
            frameRate: 7,
            repeat: -1
        });
    }

    getFeetCartCoords(): CartesianCoordinates
    {
        const isoWidth = 16;
        return new CartesianCoordinates(this.x, this.y + this.height / 2 - isoWidth/2);
    }

    setPositionFromFeet(feetCoords: CartesianCoordinates): CartesianCoordinates
    {
        const isoWidth = 16;
        this.x = feetCoords.x;
        this.y = feetCoords.y - this.height / 2 + isoWidth/2;

        return new CartesianCoordinates(this.x, this.y);
    }

    checkPositionDepth(): void
    {
        const polygonDepth = (<DaedalusScene>this.scene).sceneGrid.getDepthOfPoint(this.getFeetCartCoords().toIsometricCoordinates());

        if (polygonDepth !== -1) {
            this.setDepth(polygonDepth + this.getFeetCartCoords().y);
        }
    }
}
