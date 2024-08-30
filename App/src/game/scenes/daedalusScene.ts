import Phaser from 'phaser';
import { Room } from "@/entities/Room";

import CharacterObject from "@/game/objects/characterObject";
import InteractObject from "@/game/objects/interactObject";

import OutlinePostFx from 'phaser3-rex-plugins/plugins/outlinepipeline.js';

import { Player } from "@/entities/Player";
import PlayableCharacterObject from "@/game/objects/playableCharacterObject";
import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import IsometricGeom from "@/game/scenes/isometricGeom";
import { SceneGrid } from "@/game/scenes/sceneGrid";
import { NavMeshGrid } from "@/game/scenes/navigationGrid";
import store from "@/store";
import MushTiledMap from "@/game/tiled/mushTiledMap";
import EquipmentObject from "@/game/objects/equipmentObject";
import { Equipment } from "@/entities/Equipment";
import DecorationObject from "@/game/objects/decorationObject";
import DoorObject from "@/game/objects/doorObject";
import DoorGroundObject from "@/game/objects/doorGroundObject";
import { Door } from "@/entities/Door";
import { Planet } from "@/entities/Planet";
import PatrolShipObject from "@/game/objects/patrolShipObject";
import DeathZone = Phaser.GameObjects.Particles.Zones.DeathZone;

export default class DaedalusScene extends Phaser.Scene
{
    private characterSize = 6;
    private readonly isoTileSize: number;
    private sceneIsoSize: IsometricCoordinates;
    private readonly playerIsoSize: IsometricCoordinates;

    public playerSprite! : PlayableCharacterObject;

    private player : Player;
    public room : Room;
    private equipments : Array<EquipmentObject>;
    private map: MushTiledMap | null;
    private targetHighlightObject?: Phaser.GameObjects.Sprite;

    public sceneGrid: SceneGrid;
    public navMeshGrid: NavMeshGrid;
    private roomBasicSceneGrid: SceneGrid;

    private isScreenSliding = { x: false, y: false };
    private cameraTarget: CartesianCoordinates = new CartesianCoordinates(0,0);
    private cameraDirection: CartesianCoordinates = new CartesianCoordinates(0,0);
    private previousRoom: string | undefined = undefined;

    public selectedGameObject : Phaser.GameObjects.GameObject | null;
    private fireParticles: Array<Phaser.GameObjects.Particles.ParticleEmitter> = [];
    private starParticles: Array<Phaser.GameObjects.Particles.ParticleEmitter> = [];
    private hunterParticle: Phaser.GameObjects.Particles.ParticleEmitter | null = null;
    private background: Phaser.GameObjects.TileSprite | undefined;
    private isTravelling= false;
    private attackingHunters = 0;

    constructor(player: Player) {
        super('game-scene');

        this.isoTileSize = 16;
        this.sceneIsoSize= new IsometricCoordinates(0, 0);
        this.playerIsoSize = new IsometricCoordinates(this.characterSize, this.characterSize);

        if (player.room === null){
            throw new Error('player should have a room');
        }

        this.room = player.room;
        this.map = null;
        this.player = player;
        this.equipments = [];

        this.sceneGrid = new SceneGrid(this, this.characterSize);
        this.roomBasicSceneGrid = new SceneGrid(this, this.characterSize);
        this.navMeshGrid = new NavMeshGrid(this);

        this.selectedGameObject = null;
    }

    preload(): void
    {
        this.load.setPath("/phaser/");

        this.load.tilemapTiledJSON('medlab', 'tilemaps/mush_medlab.json');
        this.load.tilemapTiledJSON('laboratory', 'tilemaps/mush_lab.json');
        this.load.tilemapTiledJSON('central_corridor', 'tilemaps/center_corridor.json');
        this.load.tilemapTiledJSON('front_storage', 'tilemaps/front_storage.json');
        this.load.tilemapTiledJSON('front_corridor', 'tilemaps/front_corridor.json');
        this.load.tilemapTiledJSON('bravo_dorm', 'tilemaps/bravo_dorm.json');
        this.load.tilemapTiledJSON('alpha_dorm', 'tilemaps/alpha_dorm.json');
        this.load.tilemapTiledJSON('hydroponic_garden', 'tilemaps/garden.json');
        this.load.tilemapTiledJSON('refectory', 'tilemaps/refectory.json');
        this.load.tilemapTiledJSON('center_alpha_storage', 'tilemaps/center_alpha_storage.json');
        this.load.tilemapTiledJSON('center_bravo_storage', 'tilemaps/center_bravo_storage.json');
        this.load.tilemapTiledJSON('rear_corridor', 'tilemaps/rear_corridor.json');
        this.load.tilemapTiledJSON('nexus', 'tilemaps/nexus.json');
        this.load.tilemapTiledJSON('rear_bravo_storage', 'tilemaps/rear_bravo_storage.json');
        this.load.tilemapTiledJSON('rear_alpha_storage', 'tilemaps/rear_alpha_storage.json');
        this.load.tilemapTiledJSON('alpha_bay_2', 'tilemaps/bay_alpha_2.json');
        this.load.tilemapTiledJSON('alpha_bay', 'tilemaps/bay_alpha.json');
        this.load.tilemapTiledJSON('bravo_bay', 'tilemaps/bay_bravo.json');
        this.load.tilemapTiledJSON('icarus_bay', 'tilemaps/bay_icarus.json');
        this.load.tilemapTiledJSON('front_bravo_turret', 'tilemaps/front_bravo_turret.json');
        this.load.tilemapTiledJSON('centre_bravo_turret', 'tilemaps/center_bravo_turret.json');
        this.load.tilemapTiledJSON('rear_bravo_turret', 'tilemaps/rear_bravo_turret.json');
        this.load.tilemapTiledJSON('front_alpha_turret', 'tilemaps/front_alpha_turret.json');
        this.load.tilemapTiledJSON('centre_alpha_turret', 'tilemaps/center_alpha_turret.json');
        this.load.tilemapTiledJSON('rear_alpha_turret', 'tilemaps/rear_alpha_turret.json');
        this.load.tilemapTiledJSON('bridge', 'tilemaps/bridge.json');
        this.load.tilemapTiledJSON('engine_room', 'tilemaps/engine_room.json');
        this.load.tilemapTiledJSON('patrol_ship_bravo_epicure', 'tilemaps/patrol_ship_bravo_epicure.json');
        this.load.tilemapTiledJSON('patrol_ship_bravo_planton', 'tilemaps/patrol_ship_bravo_planton.json');
        this.load.tilemapTiledJSON('patrol_ship_bravo_socrate', 'tilemaps/patrol_ship_bravo_socrate.json');
        this.load.tilemapTiledJSON('patrol_ship_alpha_jujube', 'tilemaps/patrol_ship_alpha_jujube.json');
        this.load.tilemapTiledJSON('patrol_ship_alpha_tamarin', 'tilemaps/patrol_ship_alpha_tamarin.json');
        this.load.tilemapTiledJSON('patrol_ship_alpha_longane', 'tilemaps/patrol_ship_alpha_longane.json');
        this.load.tilemapTiledJSON('patrol_ship_alpha_2_wallis', 'tilemaps/patrol_ship_alpha_2_wallis.json');
        this.load.tilemapTiledJSON('pasiphae', 'tilemaps/pasiphae.json');

        this.load.image('tileset', 'floor_wall_tileset.png');
        this.load.image('background', 'background.png');
        this.load.atlas('characters', 'characters.png', 'characters.json');
        this.load.atlas('base_textures', 'baseTextures.png', 'baseTextures.json');
        this.load.atlas('planets', 'planets.png', 'planets.json');

        this.load.multiatlas('equipments', 'equipments.json');
    }

    create(): void
    {
        (<Phaser.Renderer.WebGL.WebGLRenderer>this.game.renderer).pipelines.addPostPipeline('outline', OutlinePostFx);

        this.map = this.createRoom();

        this.createEquipments(this.map);
        this.updateStatuses();
        this.updateEquipments();

        store.subscribeAction({
            before: (action) => {
                if (action.type === 'player/reloadPlayer') {
                    this.input.enabled = false;
                }
            },
            after: (action) => {
                if (action.type === 'player/reloadPlayer' && this.player.isAlive()) {
                    this.reloadScene();
                    this.input.enabled = true;
                }
            }
        });


        this.createBackground();

        if (this.player?.room?.type !== 'room') {
            return;
        }

        this.enableEventListeners();
        this.input.setTopOnly(true);
        this.input.setGlobalTopOnly(true);

        this.createPlayers();
    }

    reloadScene(): void
    {
        this.player = store.getters["player/player"];

        const newRoom = this.player.room;
        if (newRoom === null) { throw new Error("player room should be defined");}

        if (this.room.key !== newRoom.key) {
            this.selectedGameObject = null;
            store.dispatch('room/closeInventory');

            this.deleteWallAndFloor();
            this.deleteCharacters();
            this.deleteEquipmentsAndDecoration();
            this.removeFire();

            // update background
            this.updateBackground(newRoom);
            this.room = newRoom;

            this.map = this.createRoom();
            this.createEquipments(this.map);
            this.updateEquipments();
            if (this.room.type !== 'room') {
                return;
            }
            this.updateStatuses();
            this.createPlayers();

        } else if (this.areEquipmentsModified()) {
            this.navMeshGrid = new NavMeshGrid(this);
            this.deleteEquipmentsAndDecoration();
            this.selectedGameObject = null;
            store.dispatch('room/closeInventory');

            if (this.map === null) { throw new Error("player room should be defined");}

            this.deleteCharacters();

            // update background
            this.updateBackground(newRoom);

            this.room = newRoom;
            this.map = this.createRoom();
            this.createEquipments(this.map);
            this.updateStatuses();
            this.createPlayers();
        } else{
            // update background
            this.updateBackground(newRoom);

            this.room = newRoom;

            this.updatePlayers();
            this.updateEquipments();
            this.updateStatuses();
        }
    }

    updateStatuses(): void
    {
        if (this.room.isOnFire && this.fireParticles.length === 0) {
            this.displayFire();
        } else if (!this.room.isOnFire && this.fireParticles.length > 0) {
            this.removeFire();
        }
    }

    updateEquipments(): void
    {
        const sceneGameObjects = this.children.list;

        const room = this.player.room;

        if (room === null) { throw new Error("player room should be defined");}
        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            if (gameObject instanceof EquipmentObject) {
                const updatedEquipment = room.equipments.filter((equipment: Equipment) => (equipment.id === gameObject.equipment.id))[0];

                gameObject.updateEquipment(updatedEquipment);

            } else if (gameObject instanceof DoorObject || gameObject instanceof DoorGroundObject) {
                const updatedDoor = room.doors.filter((door: Door) => (door.key === gameObject.door.key))[0];

                gameObject.updateDoor(updatedDoor);
            }
        }
    }

    updatePlayers(): void
    {
        const sceneGameObjects = this.children.list;
        const addedPlayer: Array<string> = [];

        const room = this.player.room;
        if (room === null) { throw new Error("player room should be defined");}

        // update player (that get up for instance) and remove player that moved or died
        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            if (gameObject instanceof CharacterObject) {
                addedPlayer.push(gameObject.name);

                if (room.players.filter((player: Player) => {return player.character.key === gameObject.name;}).length == 0 &&
                    this.player.character.key !== gameObject.name
                ) {
                    gameObject.delete();
                    i = i-1;
                } else {
                    if (this.player.character.key === gameObject.name) {
                        const playerEntity = this.player;

                        gameObject.updatePlayer(playerEntity);
                        if (gameObject.name === this.selectedGameObject?.name) {
                            store.dispatch('room/selectTarget', { target: gameObject.player });
                        }
                    } else {
                        const playerEntity = room.players.filter((player: Player) => {return player.character.key === gameObject.name;})[0];

                        gameObject.updatePlayer(playerEntity);
                        if (gameObject.name === this.selectedGameObject?.name) {
                            store.dispatch('room/selectTarget', { target: gameObject.player });
                        }
                    }
                }
            }
        }

        //add players
        for (let i=0; i < room.players.length; i++) {
            const player = room.players[i];

            if (!addedPlayer.includes(player.character.key)) {
                const otherPlayerCoordinates = this.navMeshGrid.getRandomPoint();
                new CharacterObject(
                    this,
                    otherPlayerCoordinates,
                    new IsometricGeom(otherPlayerCoordinates.toIsometricCoordinates(), this.playerIsoSize),
                    player
                );
            }
        }
    }

    areEquipmentsModified(): boolean
    {
        const sceneGameObjects = this.children.list;

        const room = this.player.room;

        if (room === null) { throw new Error("player room should be defined");}
        const equipmentsToUpdate = room.equipments;

        const updatedEquipment = new Array<number>();

        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            if (gameObject instanceof EquipmentObject) {
                if (equipmentsToUpdate.filter((equipment: Equipment) => {
                    return equipment.id === gameObject.equipment.id;
                }).length === 0) {
                    return true;
                }
                if (!(updatedEquipment.includes(gameObject.equipment.id))) {
                    updatedEquipment.push(gameObject.equipment.id);
                }
            }
        }

        return equipmentsToUpdate.length !== updatedEquipment.length;
    }

    createRoom(): MushTiledMap
    {
        this.sceneGrid = new SceneGrid(this, this.characterSize);
        this.navMeshGrid = new NavMeshGrid(this);

        const map = new MushTiledMap(this, this.room.key);
        this.roomBasicSceneGrid = map.createInitialSceneGrid(this.sceneGrid);
        this.sceneIsoSize = map.getMapSize();
        this.cameraDirection = new CartesianCoordinates(0,0);
        map.createLayers(this.room, this.sceneGrid);

        this.playerSprite = new PlayableCharacterObject(
            this,
            new CartesianCoordinates(0,0),
            new IsometricGeom(new IsometricCoordinates(0,0), this.playerIsoSize),
            this.player
        );

        if (this.room.type === 'patrol_ship') {
            this.playerSprite.setVisible(false);
        } else if (this.room.type === 'space') {
            this.playerSprite.play('space_giggle');
            this.playerSprite.setDepth(15);
        }

        //place the starting camera.
        //If the scene size is larger than the camera, the camera is centered on the player
        //else it is centered on the scene
        const sceneCartesianSize = new CartesianCoordinates(this.sceneIsoSize.x + this.sceneIsoSize.y, (this.sceneIsoSize.x + this.sceneIsoSize.y)/2);
        //this.cameras.main.setBounds(-this.sceneIsoSize.y, 0, sceneCartesianSize.x, sceneCartesianSize.y);

        this.cameras.main.setBounds(-this.game.scale.gameSize.width/2, -this.game.scale.gameSize.height/2 +72, sceneCartesianSize.x, sceneCartesianSize.y);

        if (sceneCartesianSize.x - 80 > this.game.scale.gameSize.width) {
            this.isScreenSliding.x = true;
            this.cameras.main.setBounds(-this.sceneIsoSize.y, -72, sceneCartesianSize.x, sceneCartesianSize.y + 72);
        }
        if (sceneCartesianSize.y - 80 > this.game.scale.gameSize.height) {
            this.isScreenSliding.y = true;
            this.cameras.main.setBounds(-this.sceneIsoSize.y, -72, sceneCartesianSize.x, sceneCartesianSize.y + 72);
        }

        // add target tile highlight
        if (this.room.type === 'room') {
            this.targetHighlightObject = new Phaser.GameObjects.Sprite(this, 0, 0, 'base_textures','tile_highlight');
            this.add.existing(this.targetHighlightObject);
            this.targetHighlightObject.setDepth(500);
        } else {
            this.targetHighlightObject?.setDepth(-1);
        }

        return map;
    }

    createBackground(): void
    {
        this.background = this.add.tileSprite(this.game.scale.gameSize.width/2, this.game.scale.gameSize.height/2, 425, 470, 'background');
        this.background.setScrollFactor(0, 0);
        this.background.setDepth(0);

        const daedalus = this.player.daedalus;
        if (daedalus === null) {
            return;
        }

        // add a planet in background if necessary
        const planet = daedalus.inOrbitPlanet;
        if (planet !== null) {
            this.displayPlanet(planet);
        }

        // add stars in the background with a particle emitter
        this.isTravelling = daedalus.isDaedalusTravelling;
        this.createStarParticles();

        this.attackingHunters = daedalus.attackingHunters;
        this.createHunterParticles();
    }

    updateBackground(newRoom: Room): void
    {
        const daedalus = this.player.daedalus;

        if (daedalus === null) {
            return;
        }

        // check if daedalus is traveling
        if (this.isTravelling !== daedalus.isDaedalusTravelling) {
            this.isTravelling = !this.isTravelling;
            this.createStarParticles();
        }

        // check if player took-off or land
        if (newRoom.type !== this.room?.type) {
            this.createStarParticles();
        }

        // check if hunter are attacking
        if (this.attackingHunters !== daedalus.attackingHunters) {
            this.attackingHunters = daedalus.attackingHunters;
            this.createHunterParticles();
        }

        //check if there is a planet in orbit
        const planet = daedalus.inOrbitPlanet;
        if (planet !== null) {
            this.displayPlanet(planet);
        }
    }

    displayPlanet(inOrbitPlanet: Planet): void
    {
        const planetSprite = this.add.tileSprite(
            this.game.scale.gameSize.width-(268/2),
            this.game.scale.gameSize.height-(191/2),
            268, 191,
            `planet_${inOrbitPlanet.imageId}`
        );
        planetSprite.setScrollFactor(0, 0);
        planetSprite.setDepth(3);
    }

    createHunterParticles(): void
    {
        let displayedHunter = 0;
        let hunterFrequency = 10000;

        this.hunterParticle?.destroy();

        if (this.attackingHunters === 0) {
            return;
        } else if (this.attackingHunters <= 1) {
            displayedHunter = 1;
        } else if (this.attackingHunters <= 5) {
            displayedHunter = 2;
        } else if (this.attackingHunters <= 10) {
            displayedHunter = 3;
        } else {
            displayedHunter = 3;
            hunterFrequency = 5000;
        }

        const gameSize = this.game.scale.gameSize;
        const hunterAngle = 145;
        const maxSpawnY = gameSize.height * 2/3 - Math.tan(180 - hunterAngle);
        const minSpawnY = - Math.tan(180 - hunterAngle) * gameSize.width/2;

        const gameLimits = new Phaser.Geom.Rectangle(
            -10, minSpawnY - 5,
            gameSize.width + 130, gameSize.height - minSpawnY + 10
        );


        const grpY: any[] = [];
        const getNextY = () => {
            if(!grpY.length){
                const center = minSpawnY + Math.random() * (maxSpawnY- minSpawnY);
                grpY.push(center - 30, center, center + 30);
            }
            return grpY.pop();
        };

        const grpX: any[] = [];
        const getNextX = () => {
            if(!grpX.length){
                const formation = Math.random();
                if (formation < 0.4) {
                    grpX.push(gameSize.width + 10, gameSize.width + 40, gameSize.width + 70);
                } else if (formation < 0.8) {
                    grpX.push(gameSize.width + 60, gameSize.width + 110, gameSize.width + 10);
                } else {
                    grpX.push(gameSize.width + 10, gameSize.width + 10, gameSize.width + 10);
                }
            }
            return grpX.pop();
        };

        const hunterEmitter = this.add.particles(0,0, 'base_textures', {
            frame: 'hunter',
            x: getNextX,
            y: getNextY,
            lifespan: 2000,
            speed: 700,
            angle: hunterAngle,
            quantity: { min: 1, max: displayedHunter },
            frequency: hunterFrequency,
            accelerationY: 2,
            accelerationX: 2
        });
        hunterEmitter.setDepth(3);
        hunterEmitter.addDeathZone(new DeathZone(gameLimits, false));
        hunterEmitter.setScrollFactor(0,0);

        this.hunterParticle = hunterEmitter;
    }

    createStarParticles(): void
    {
        this.removeStarEmitter();

        const gameSize = this.game.scale.gameSize;

        let starSpeed = 10;
        let starFrequency = 2000;
        let starAngle = 30;
        let horizontalEmitArea = new Phaser.Geom.Line(0,0,gameSize.width, 0);
        const verticalEmitArea = new Phaser.Geom.Line(0,0,0, gameSize.height);

        if (this.player.room?.type === 'patrol_ship') {
            starAngle = -30;
            starSpeed = 300;
            starFrequency = 1000;
            horizontalEmitArea = new Phaser.Geom.Line(0,gameSize.height,gameSize.width, gameSize.height);
        }


        if (this.isTravelling) {
            starSpeed = 1000;
            starFrequency = 50;
        }
        this.textures.generate('star_particles', { data: ['2'] });

        const gameLimits = new Phaser.Geom.Rectangle(
            -10, -10,
            gameSize.width + 20, gameSize.height + 20
        );

        const topStarEmitter = this.add.particles(0,0, 'star_particles', {
            lifespan: 200000,
            speed: starSpeed,
            angle: starAngle,
            scale: { min: 1, max: 3 },
            quantity: 1,
            frequency: starFrequency,
            //@ts-ignore
            emitZone: { type: 'random', source: verticalEmitArea }
        });
        topStarEmitter.setDepth(1);
        topStarEmitter.setScrollFactor(0, 0);
        topStarEmitter.addDeathZone(new DeathZone(gameLimits, false));

        const leftStarEmitter = this.add.particles(0,0, 'star_particles', {
            lifespan: 200000,
            speed: starSpeed,
            angle: starAngle,
            scale: { min: 1, max: 3 },
            quantity: 1,
            frequency: starFrequency,
            //@ts-ignore
            emitZone: { type: 'random', source: horizontalEmitArea }
        });
        leftStarEmitter.setDepth(1);
        leftStarEmitter.setScrollFactor(0,0);
        leftStarEmitter.addDeathZone(new DeathZone(gameLimits, false));

        this.starParticles = [topStarEmitter, leftStarEmitter];
    }

    removeStarEmitter(): void
    {
        for (let i=0; i< this.starParticles.length; i++) {
            const particleEmitter = this.starParticles[i];
            particleEmitter.destroy();
        }
        this.starParticles = [];
    }

    createEquipments(map: MushTiledMap): void
    {
        this.equipments = map.createEquipmentLayers(this.room, this.roomBasicSceneGrid);

        this.sceneGrid.updateDepth();
        this.navMeshGrid = this.sceneGrid.buildNavMeshGrid();
    }

    deleteEquipmentsAndDecoration(): void
    {
        const sceneGameObjects = this.children.list;
        const room = this.player.room;
        if (room === null) { throw new Error("player room should be defined");}

        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            if (gameObject instanceof DecorationObject &&
                !(gameObject instanceof CharacterObject))
            {
                gameObject.delete();
                i = i-1;
            }
        }
    }

    deleteWallAndFloor(): void
    {
        const sceneGameObjects = this.children.list;
        const room = this.player.room;
        if (room === null) { throw new Error("player room should be defined");}

        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            // do not remove backgroud and star particles
            if (!(gameObject instanceof DecorationObject) &&
                gameObject !== this.background &&
                !(gameObject instanceof  Phaser.GameObjects.Particles.ParticleEmitter)
            ){
                gameObject.destroy();
                i = i-1;
            }
        }
    }

    deleteCharacters(): void
    {
        const sceneGameObjects = this.children.list;
        const room = this.player.room;
        if (room === null) { throw new Error("player room should be defined");}

        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            if ((gameObject instanceof CharacterObject))
            {
                gameObject.delete();
                i = i-1;
            }
        }
    }

    removeFire(): void
    {
        for (let i=0; i< this.fireParticles.length; i++) {
            const particleEmitter = this.fireParticles[i];
            particleEmitter.destroy();
            this.fireParticles.splice(i, 1);
            i= i-1;
        }
    }

    displayFire(): void
    {
        const totalNumberOfTiles = this.sceneIsoSize.x * this.sceneIsoSize.y/(this.isoTileSize * this.isoTileSize);

        const numberOfFireCells = (Math.random()*0.2) * totalNumberOfTiles + 3;

        for (let i = 0; i < numberOfFireCells; i++) {
            //get random coordinates for the fire cell
            const rand_iso_coords = this.navMeshGrid.getRandomPoint().toIsometricCoordinates();
            const cell_coords = this.getGridIsoCoordinate(rand_iso_coords);

            if (this.sceneGrid.getPolygonFromPoint(cell_coords) !== -1) {
                //intensity of fire
                if (Math.random() > 0.2) {
                    this.createFireCell(cell_coords, 1);
                } else {
                    this.createFireCell(cell_coords, 2);
                }
            }
        }
    }

    createFireCell(isoCoords: IsometricCoordinates, intensity: number): void
    {
        const tile = new IsometricGeom(isoCoords, new IsometricCoordinates(16, 16));

        const yellowFlames = this.add.particles(0,0, 'base_textures', {
            frame: ['flame-0', 'flame-1'],
            x: 0, y: 0,
            lifespan: 200,
            speed: { min: 30, max: 50 },
            angle: { min: 260, max: 280 },
            gravityY: 50,
            scale: { start: 1, end: 1 },
            alpha: { start: 0, end: 0.8 },
            quantity: 5,
            //@ts-ignore
            emitZone: { type: 'random', source: tile }
        });
        yellowFlames.setDepth(this.sceneGrid.getDepthOfPoint(isoCoords));
        this.fireParticles.push(yellowFlames);


        if (intensity > 1) {
            const redFlames = this.add.particles(0,0, 'base_textures', {
                frame: ['flame-1','flame-3'],
                x: 0, y: 0,
                lifespan: 600,
                speed: { min: 40, max: 60 },
                angle: { min: 260, max: 280 },
                gravityY: 40,
                scale: { start: 0, end: 1 },
                alpha: { start: 0, end: 0.8 },
                quantity: 2,
                //@ts-ignore
                emitZone: { type: 'random', source: tile }
            });
            redFlames.setDepth(this.sceneGrid.getDepthOfPoint(isoCoords));
            this.fireParticles.push(redFlames);
        }

        const smoke = this.add.particles(0,0, 'base_textures', {
            frame: ['flame-3','flame-4','flame-5'],
            x: 0, y: -8,
            lifespan: 800,
            speed: { min: 20, max: 40 },
            angle: { min: 260, max: 280 },
            gravityY: 20,
            scale: { start: 0, end: 1 },
            alpha: { start: 0, end: 0.5 },
            quantity: 2,
            //@ts-ignore
            emitZone: { type: 'random', source: tile }
        });
        smoke.setDepth(this.sceneGrid.getDepthOfPoint(isoCoords));
        this.fireParticles.push(smoke);
    }

    handleSpaceBattle(time: number, delta: number): void
    {
        if (this.room?.type === 'patrol_ship') {
            const sceneGameObjects = this.children.list;

            for (let i=0; i < sceneGameObjects.length; i++) {
                const gameObject = sceneGameObjects[i];

                if (gameObject instanceof PatrolShipObject) {
                    gameObject.update(time, delta);
                }
            }
        }
    }
    update(time: number, delta: number): void
    {
        this.playerSprite.update();

        this.handleSpaceBattle(time, delta);

        if (this.targetHighlightObject !== undefined) {
            const worldPointer = this.input.mousePointer.updateWorldPoint(this.cameras.main);
            const pointerCoords = new CartesianCoordinates(worldPointer.worldX, worldPointer.worldY);
            const cellCoords = this.getGridIsoCoordinate(pointerCoords.toIsometricCoordinates()).toCartesianCoordinates();

            const sceneGridIndex = this.sceneGrid.getPolygonFromPoint(cellCoords.toIsometricCoordinates());

            if (sceneGridIndex !== -1) {
                this.targetHighlightObject.setPosition(cellCoords.x, cellCoords.y);
                this.targetHighlightObject.setDepth(this.sceneGrid.getDepthOfPoint(cellCoords.toIsometricCoordinates()));
            } else {
                this.targetHighlightObject.setDepth(0);
            }
        }

        // camera
        //this.cameras.main.centerOn(this.cameraTarget.x, this.cameraTarget.y);
        if (this.cameraDirection.x !== 0 || this.cameraDirection.y !== 0) {
            this.cameras.main.scrollX += this.cameraDirection.x;
            this.cameras.main.scrollY += this.cameraDirection.y;

            if (((this.cameraDirection.x >= 0 && this.cameras.main.scrollX >= this.cameraTarget.x) ||
                (this.cameraDirection.x <= 0 && this.cameras.main.scrollX <= this.cameraTarget.x)) &&
                ((this.cameraDirection.y >= 0 && this.cameras.main.scrollY >= this.cameraTarget.y) ||
                (this.cameraDirection.y <= 0 && this.cameras.main.scrollY <= this.cameraTarget.y))
            ) {
                this.cameraDirection.setTo(0,0);
            }
        }
    }

    // return the center of the currently pointed tile
    getGridIsoCoordinate(isoCoord: IsometricCoordinates): IsometricCoordinates
    {
        return new IsometricCoordinates(
            Math.floor(((isoCoord.x + 4)/this.isoTileSize)) * this.isoTileSize,
            Math.floor(((isoCoord.y + 4)/this.isoTileSize)) * this.isoTileSize
        );
    }

    createPlayers(): void
    {
        let playerCoordinates = this.navMeshGrid.getRandomPoint();
        if (this.previousRoom !== undefined && this.previousRoom !== this.room.key) {
            playerCoordinates = this.findRoomEntryPoint();
            this.playerSprite.interactedEquipment = null;
        }

        this.previousRoom = this.room.key;
        this.cameras.main.centerOn(playerCoordinates.x, playerCoordinates.y);

        this.playerSprite.setPositionFromFeet(playerCoordinates);
        this.playerSprite.updateNavMesh();
        this.playerSprite.checkPositionDepth();
        this.playerSprite.applyEquipmentInteraction();
        this.playerSprite.resetMove();

        this.room.players.forEach((roomPlayer: Player) => {
            if (roomPlayer.id !== this.player.id) {
                const otherPlayerCoordinates = this.navMeshGrid.getRandomPoint();
                new CharacterObject(
                    this,
                    otherPlayerCoordinates,
                    new IsometricGeom(otherPlayerCoordinates.toIsometricCoordinates(), this.playerIsoSize),
                    roomPlayer
                );
            }
        });
    }

    findRoomEntryPoint(): CartesianCoordinates
    {
        const sceneGameObjects = this.children.list;

        for (let i=0; i < sceneGameObjects.length; i++) {
            const gameObject = sceneGameObjects[i];

            if (gameObject instanceof DoorGroundObject &&
                gameObject.door.direction === this.previousRoom)
            {
                return this.navMeshGrid.getClosestPoint(gameObject.isoGeom.getIsoCoords()).toCartesianCoordinates();
            }
        }
        return this.navMeshGrid.getRandomPoint();
    }

    enableEventListeners(): void
    {
        this.input.on('pointerdown', (pointer: Phaser.Input.Pointer, gameObjects: Array<Phaser.GameObjects.GameObject>) => {
            let gameObject = null;
            if (gameObjects.length > 0) {
                gameObject = gameObjects[0];
            }

            this.playerSprite.updateMovement(pointer, gameObject);
            if (this.selectedGameObject !== null &&
                this.selectedGameObject instanceof InteractObject &&
                this.selectedGameObject !== gameObject
            ) {
                this.selectedGameObject.onClickedOut();
                this.selectedGameObject = gameObject;

                if (gameObject === null) {
                    store.dispatch('room/selectTarget', { target: null });
                    store.dispatch('room/closeInventory');
                }
            }
            if (gameObject instanceof InteractObject){
                gameObject.onSelected();
                this.selectedGameObject = gameObject;
            }

            // screen sliding
            const playerTargetCoordinates = this.playerSprite.getMovementTarget();

            if (playerTargetCoordinates !== null) {
                const requiredScroll = this.cameras.main.getScroll(playerTargetCoordinates.x, playerTargetCoordinates.y);

                if (!this.isScreenSliding.x) {requiredScroll.x = this.cameras.main.scrollX;}
                if (!this.isScreenSliding.y) {requiredScroll.y = this.cameras.main.scrollY;}
                if (requiredScroll.x !== this.cameras.main.scrollX || requiredScroll.y !== this.cameras.main.scrollY) {
                    this.cameraTarget.setTo(requiredScroll.x, requiredScroll.y);

                    const norm = Math.pow(
                        Math.pow((requiredScroll.x - this.cameras.main.scrollX), 2)+
                        Math.pow((requiredScroll.y - this.cameras.main.scrollY), 2),
                        1/2
                    );
                    this.cameraDirection.setTo(
                        (requiredScroll.x  - this.cameras.main.scrollX)/norm,
                        (requiredScroll.y  - this.cameras.main.scrollY)/norm
                    );
                }
            }
        });

        this.input.on('gameobjectout', () => {
            if (this.targetHighlightObject !== undefined) {
                this.targetHighlightObject.setAlpha(1);
            }
        });
        this.input.on('gameobjectover', () => {
            if (this.targetHighlightObject !== undefined) {
                this.targetHighlightObject.setAlpha(0);
            }
        });
    }

    findObjectByNameAndId(name: string, id: number) : EquipmentObject | null
    {
        for (let i = 0; i< this.equipments.length; i++) {
            const equipment = this.equipments[i];
            if (equipment.equipment.key === name && equipment.equipment.id === id) {
                return equipment;
            }
        }

        return null;
    }

    enableDebugView(): void
    {
        // navMesh Debug
        //const navMeshPolygons = this.navMeshGrid.geomArray;
        const navMeshPolygons = this.sceneGrid.depthSortingArray;

        const debugGraphics = this.add.graphics().setAlpha(1);
        debugGraphics.setDepth(1000000000);
        for (let i = 0; i < navMeshPolygons.length; i++) {
        // for (let i = 4; i < 5; i++) {
            const polygon = navMeshPolygons[i].geom.getIsoArray();
            //const polygon = navMeshPolygons[i].getIsoArray();

            //console.log(navMeshPolygons[i].object?.name);
            //console.log(navMeshPolygons[i].object?.depth);

            let maxX = polygon[0].x;
            let minX = polygon[0].x;
            let maxY = polygon[0].y;
            let minY = polygon[0].y;

            polygon.forEach((point: {x: number, y: number}) => {
                if (point.x >maxX) { maxX = point.x; }
                if (point.y >maxY) { maxY = point.y; }
                if (point.x <minX) { minX = point.x; }
                if (point.y <minY) { minY = point.y; }
            });

            const cartPoly = new IsometricGeom(new IsometricCoordinates((maxX+minX)/2, (maxY+minY)/2), new IsometricCoordinates(maxX-minX, maxY-minY));

            if (navMeshPolygons[i].isNavigable) {
                debugGraphics.fillStyle(0x00FF00, 0.1);
            } else {
                debugGraphics.fillStyle(0xFF0000, 0.1);
            }
            //debugGraphics.fillStyle(0xF0FFFF, 0.2);

            debugGraphics.lineStyle(1, 0xff0000, 1.0);
            debugGraphics.fillPoints(cartPoly.getCartesianPolygon().points, true);
            debugGraphics.strokePoints(cartPoly.getCartesianPolygon().points, true);
        }

        // const debugGraphics2 = this.add.graphics().setAlpha(1);
        // debugGraphics2.setDepth(100000000);
        // this.navMeshGrid.phaserNavMesh.enableDebug(debugGraphics2);
        // this.navMeshGrid.phaserNavMesh.debugDrawClear(); // Clears the overlay
        // // Visualize the underlying navmesh
        // this.navMeshGrid.phaserNavMesh.debugDrawMesh({
        //     drawCentroid: false,
        //     drawBounds: false,
        //     drawNeighbors: true,
        //     drawPortals: false
        // });
    }
}
