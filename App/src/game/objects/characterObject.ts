import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { characterEnum, CharacterInfos } from "@/enums/character";
import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import { Player } from "@/entities/Player";
import store from "@/store";
import { PhaserNavMesh } from "phaser-navmesh/src";
import InteractObject from "@/game/objects/interactObject";
import Tileset = Phaser.Tilemaps.Tileset;
import IsometricGeom from "@/game/objects/isometricGeom";

export default class CharacterObject extends InteractObject {
    protected player : Player;
    protected navMesh: PhaserNavMesh;

    constructor(scene: DaedalusScene, cart_coords: CartesianCoordinates, isoGeom: IsometricGeom, sceneAspectRatio: IsometricCoordinates, player: Player) {
        super(
            scene,
            cart_coords,
            isoGeom,
            new Tileset('character', 0, 48, 32),
            (<number>(<CharacterInfos>characterEnum[player.character.key]).rightFrame),
            player.character.key,
            sceneAspectRatio
        );

        this.player = player;
        this.navMesh = scene.navMesh;

        scene.physics.world.enable(this);

        //const iso_coords = toIsometricCoords({ x: this.x, y: this.getFeetY() });
        //the first sprite to be displayed are the ones on the last row of either x or y isometric coordinates
        //a second order sorting is applied using the y axis of cartesian coordinates
        //              4
        //            3   3
        //          2   3    2
        //       1    2   2    1           / \
        //          1   2   1             y   x
        //              1
        //
        this.setDepth(Math.max(this.isoGeom.getIsoCoords().x + this.sceneAspectRatio.x, this.isoGeom.getIsoCoords().y + this.sceneAspectRatio.y) * 1000 + this.getFeetCartCoords().y);

        const characterFrames: CharacterInfos = characterEnum[this.player.character.key];
        this.createAnimations(characterFrames);

        //Set the initial sprite randomly such as it faces the screen
        if (Math.random() > 0.5) {
            this.flipX = true;
        }
        this.anims.play('right');

        /*const graphics = this.scene.add.graphics();
        graphics.lineStyle(1, 0x000000, 1.0);
        graphics.fillStyle(0xff0000, 1);
        graphics.strokePoints(this.isoGeom.getCartesianPolygon().points, true);
        graphics.fillPointShape(new Phaser.Geom.Point(this.getFeetCartCoords().x, this.getFeetCartCoords().y), 5);
        */

        //If this is clicked then:
        this.on('pointerdown', function (pointer: Phaser.Input.Pointer, localX: number, localY: number, event: any) {
            store.dispatch('room/selectTarget', { target: player });
        });
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
        const tileHeight = 16;
        return new CartesianCoordinates(this.x, this.y + this.height / 2 - tileHeight/2);
    }
}
