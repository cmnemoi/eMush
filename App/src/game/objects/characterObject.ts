import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { characterEnum, CharacterInfos } from "@/enums/character";
import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import { Player } from "@/entities/Player";
import store from "@/store";
import { PhaserNavMesh } from "phaser-navmesh/src";
import InteractObject from "@/game/objects/interactObject";
import Tileset = Phaser.Tilemaps.Tileset;
import IsometricGeom from "@/game/scenes/isometricGeom";

export default class CharacterObject extends InteractObject {
    protected player : Player;
    protected navMesh: PhaserNavMesh;

    constructor(scene: DaedalusScene, cart_coords: CartesianCoordinates, isoGeom: IsometricGeom, player: Player) {
        super(
            scene,
            cart_coords,
            isoGeom,
            new Tileset('character', 0, 48, 32),
            (<number>(<CharacterInfos>characterEnum[player.character.key]).rightFrame),
            player.character.key,
            false
        );

        this.player = player;
        this.navMesh = scene.navMesh;

        scene.physics.world.enable(this);


        const characterFrames: CharacterInfos = characterEnum[this.player.character.key];
        this.createAnimations(characterFrames);

        //Set the initial sprite randomly such as it faces the screen
        if (Math.random() > 0.5) {
            this.flipX = true;
        }
        this.anims.play('right');

        // const graphics = this.scene.add.graphics();
        // graphics.lineStyle(1, 0x000000, 0.5);
        // graphics.fillStyle(0xff0000, 1);
        // graphics.fillPoints(this.isoGeom.getCartesianPolygon().points, true);
        //graphics.fillPointShape(new Phaser.Geom.Point(this.getFeetCartCoords().x, this.getFeetCartCoords().y), 5);


        //If this is clicked then:
        this.on('pointerdown', function (pointer: Phaser.Input.Pointer, localX: number, localY: number, event: any) {
            store.dispatch('room/selectTarget', { target: player });
        });

        this.checkPositionDepth();
    }


    applyTexture(tileset: Phaser.Tilemaps.Tileset, name: string) {
        this.setTexture('character', this.tiledFrame);
    }


    createAnimations(characterFrames: any): void
    {
        this.anims.create({
            key: 'move_left',
            frames: this.anims.generateFrameNames('character', { start: characterFrames.moveLeftFirstFrame, end: characterFrames.moveLeftLastFrame }),
            frameRate: 10,
            repeat: -1
        });
        this.anims.create({
            key: 'up',
            frames: [{ key: 'character', frame: characterFrames.leftFrame }],
            frameRate: 1
        });
        this.anims.create({
            key: 'down',
            frames: [{ key: 'character', frame: characterFrames.rightFrame }],
            frameRate: 1
        });
        this.anims.create({
            key: 'left',
            frames: [{ key: 'character', frame: characterFrames.leftFrame }],
            frameRate: 1
        });
        this.anims.create({
            key: 'right',
            frames: [{ key: 'character', frame: characterFrames.rightFrame }],
            frameRate: 1
        });
        this.anims.create({
            key: 'move_right',
            frames: this.anims.generateFrameNames('character', { start: characterFrames.moveRightFirstFrame, end: characterFrames.moveRightLastFrame }),
            frameRate: 10,
            repeat: -1
        });
    }

    getFeetCartCoords(): CartesianCoordinates
    {
        const isoWidth = 16;
        return new CartesianCoordinates(this.x, this.y + this.height / 2 - isoWidth/2);
    }

    checkPositionDepth(): void
    {
        const polygonDepth = (<DaedalusScene>this.scene).sceneGrid.getDepthOfPoint(this.getFeetCartCoords().toIsometricCoordinates());

        this.setDepth(polygonDepth + this.getFeetCartCoords().x);
    }
}
