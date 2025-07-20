import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import { Player } from "@/entities/Player";
import store from "@/store";
import IsometricGeom from "@/game/scenes/isometricGeom";
import { NavMeshGrid } from "@/game/scenes/navigationGrid";
import Tileset = Phaser.Tilemaps.Tileset;
import { CharacterEnum } from "@/enums/character";
import mushTextureProperties from "@/game/tiled/mushTextureProperties";
import InteractObject from "@/game/objects/interactObject";

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
        const textureProperties = new mushTextureProperties();
        textureProperties.setCharacterTexture(player.character.key);

        super(
            scene,
            player.character.key,
            textureProperties,
            cart_coords,
            isoGeom,
            false
        );

        this.player = player;
        this.navMesh = scene.navMeshGrid;
        this.interactedEquipment = null;

        this.setPositionFromFeet(cart_coords);

        scene.physics.world.enable(this);

        this.applyTextures();

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

    applyTextures(): void
    {
        const characterBody = this.player.hasStatusByKey('berzerk') ? CharacterEnum.MUSH : this.player.character.key;

        this.anims.create({
            key: 'move_right',
            frames: this.anims.generateFrameNames('characters', {
                prefix: characterBody + '-',
                start: 4,
                end: 9
            }),
            frameRate: 10,
            repeat: -1
        });
        this.anims.create({
            key: 'move_left',
            frames: this.anims.generateFrameNames('characters', {
                prefix: characterBody+ '-',
                start: 10,
                end: 15
            }),
            frameRate: 10,
            repeat: -1
        });

        this.anims.create({
            key: 'right',
            frames: this.anims.generateFrameNames('characters', {
                prefix: characterBody+ '-',
                start: 0,
                end: 0
            }),
            frameRate: 1
        });
        this.anims.create({
            key: 'left',
            frames: this.anims.generateFrameNames('characters', {
                prefix: characterBody+ '-',
                start: 1,
                end: 1
            }),
            frameRate: 1
        });

        this.anims.create({
            key: 'sit_front',
            frames: this.anims.generateFrameNames('characters', {
                prefix: characterBody+ '-',
                start: 2,
                end: 2
            }),
            frameRate: 1
        });
        this.anims.create({
            key: 'sit_back',
            frames: this.anims.generateFrameNames('characters', {
                prefix: characterBody+ '-',
                start: 3,
                end: 3
            }),
            frameRate: 1
        });
        this.anims.create({
            key: 'lie_down',
            frames: this.anims.generateFrameNames('characters', {
                prefix: characterBody+ '-',
                start: 17,
                end: 17
            }),
            frameRate: 1
        });

        this.anims.create({
            key: 'space_giggle',
            frames: this.anims.generateFrameNames('characters', {
                prefix: 'space_suit-',
                start: 0,
                end: 4
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
