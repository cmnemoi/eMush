import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { CartesianCoordinates } from "@/game/types";
import { Equipment } from "@/entities/Equipment";
import { InteractionInformation } from "@/game/objects/interactObject";
import IsometricGeom from "@/game/scenes/isometricGeom";
import EquipmentObject from "@/game/objects/equipmentObject";
import mushTextureProperties from "@/game/tiled/mushTextureProperties";
import DecorationObject from "@/game/objects/decorationObject";

export default class DroneObject extends EquipmentObject {
    private static readonly BOBBING_BASELINE_OFFSET: number = 2;
    private static readonly BOBBING_TIME_SCALE: number = 0.001;
    private static readonly TWEEN_DURATION_MS: number = 575;
    private static readonly MOVE_TIMER_VARIATION_MS: number = 500;
    private static readonly TILE_CENTER_OFFSET_FACTOR: number = 0.5;
    private static readonly TICK_LENGTH: number = 2; // seconds
    private static readonly BOBBING_AMPLITUDE: number = 0.3;
    private static readonly BOBBING_SPEED: number = 3;
    private static readonly TILE_SIZE: number = 16;
    private static readonly SHADOW_DISTANCE: number = 23;

    private initCoordinates: CartesianCoordinates;
    private nextMoveTimer: number = DroneObject.TICK_LENGTH * 1000; // ms
    private bobbingPhase: number = 0;
    private gridX: number = 0;
    private gridY: number = 0;
    private isTweening: boolean = false;
    private shadow: Phaser.GameObjects.Sprite;

    constructor(
        scene: DaedalusScene,
        cart_coords: CartesianCoordinates,
        iso_geom: IsometricGeom,
        equipment: Equipment
    )
    {
        const textureProperties = new mushTextureProperties();
        textureProperties.setCharacterTexture('drone');

        const droneSpriteCoords = new CartesianCoordinates(cart_coords.x, cart_coords.y - DroneObject.SHADOW_DISTANCE);

        super(scene, 'drone', textureProperties, droneSpriteCoords, iso_geom, equipment, false);

        this.shadow = new Phaser.GameObjects.Sprite(
            scene,
            cart_coords.x,
            cart_coords.y,
            'characters',
            'drone_shadow-0'
        );
        scene.add.existing(this.shadow);

        this.initCoordinates = new CartesianCoordinates(this.x, this.y);
        // Initialize grid position from initial coordinates (assuming 1:1 grid)
        this.gridX = Math.round(this.shadow.x / DroneObject.TILE_SIZE);
        this.gridY = Math.round(this.shadow.y / DroneObject.TILE_SIZE);

        //Set the initial sprite randomly
        if (Math.random() > 0.5) {
            this.flipX = true;
            this.shadow.flipX = true;
        }

        this.checkPositionDepth();
    }

    update(time: number, delta: number):void
    {
        if (this.isTweening) return;
        this.applyIdleBobbing(time);
        this.applyTweenMovement(delta, time);
    }

    private applyIdleBobbing(time: number): void {
        this.y = this.computeBobbingY(time);
    }

    private computeBobbingY(time: number): number {
        const amplitude = DroneObject.BOBBING_AMPLITUDE * DroneObject.TILE_SIZE;
        return this.initCoordinates.y
            + Math.sin(time * DroneObject.BOBBING_TIME_SCALE * DroneObject.BOBBING_SPEED + this.bobbingPhase) * amplitude
            + DroneObject.BOBBING_BASELINE_OFFSET;
    }

    private applyTweenMovement(delta: number, time: number): void {
        this.nextMoveTimer -= delta;
        if (this.nextMoveTimer <= 0) {
            this.moveToRandomAdjacentTile(time);
            this.resetMoveTimer();
        }
    }

    private resetMoveTimer(): void {
        this.nextMoveTimer = DroneObject.TICK_LENGTH * 1000
            + Phaser.Math.FloatBetween(-DroneObject.MOVE_TIMER_VARIATION_MS, DroneObject.MOVE_TIMER_VARIATION_MS);
    }

    private moveToRandomAdjacentTile(time: number): void {
        const target = this.getRandomPathableNeighbor();
        if (!target) return;
        this.startTweenToTile(target, time);
    }

    private getRandomPathableNeighbor(): { x: number; y: number } | null {
        const directions = [
            { x: 0, y: -1 }, // up
            { x: 0, y: 1 },  // down
            { x: -1, y: 0 }, // left
            { x: 1, y: 0 }   // right
        ];
        const sceneGrid = (this.scene as DaedalusScene).sceneGrid;
        const pathable: { x: number; y: number }[] = [];
        for (const dir of directions) {
            const nx = this.gridX + dir.x;
            const ny = this.gridY + dir.y;
            const px = nx * DroneObject.TILE_SIZE + DroneObject.TILE_SIZE * DroneObject.TILE_CENTER_OFFSET_FACTOR;
            const py = ny * DroneObject.TILE_SIZE + DroneObject.TILE_SIZE * DroneObject.TILE_CENTER_OFFSET_FACTOR;
            const isoFromPixel = new CartesianCoordinates(px, py).toIsometricCoordinates();
            const polyIdx = sceneGrid.getPolygonFromPoint(isoFromPixel);
            if (polyIdx !== -1 && sceneGrid.depthSortingArray[polyIdx].isNavigable) {
                pathable.push({ x: nx, y: ny });
            }
        }
        return pathable.length > 0 ? Phaser.Utils.Array.GetRandom(pathable) : null;
    }

    private startTweenToTile(target: { x: number; y: number }, time: number): void {
        const targetX = target.x * DroneObject.TILE_SIZE;
        const targetY = target.y * DroneObject.TILE_SIZE;
        this.isTweening = true;

        (this.scene as Phaser.Scene).tweens.add({
            targets: this,
            x: targetX,
            y: targetY - DroneObject.SHADOW_DISTANCE,
            duration: DroneObject.TWEEN_DURATION_MS,
            ease: 'Linear',
            onComplete: () => {
                this.onTweenComplete(target, time);
            }
        });
        (this.scene as Phaser.Scene).tweens.add({
            targets: this.shadow,
            x: targetX,
            y: targetY,
            duration: DroneObject.TWEEN_DURATION_MS,
            ease: 'Linear'
        });
    }

    private onTweenComplete(target: { x: number; y: number }, time: number): void {
        this.gridX = target.x;
        this.gridY = target.y;
        this.initCoordinates.x = this.x;
        this.initCoordinates.y = this.y - DroneObject.BOBBING_BASELINE_OFFSET;
        this.bobbingPhase = -performance.now() * DroneObject.BOBBING_TIME_SCALE * DroneObject.BOBBING_SPEED;
        this.isTweening = false;
        this.checkPositionDepth();
    }

    checkPositionDepth(): void
    {
        const coord = new CartesianCoordinates(this.shadow.x, this.shadow.y);
        const polygonDepth = (<DaedalusScene>this.scene).sceneGrid.getDepthOfPoint(coord.toIsometricCoordinates());

        if (polygonDepth !== -1) {
            const depth = polygonDepth + this.shadow.y;
            this.setDepth(depth);
            this.shadow.setDepth(depth);
        }
    }
}
