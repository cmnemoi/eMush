<template>
    <div class="map-container">
        <div class="map">
            <svg
                width="100%"
                height="100%"
                viewBox="0 0 184 96"
                version="1.1"
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink"
                xml:space="preserve"
                xmlns:serif="http://www.serif.com/"
                style="fill-rule:evenodd;clip-rule:evenodd;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:1.5;"
            >
                <g id="rooms">
                    <rect
                        id="Rear-Bravo-Turret"
                        serif:id="Rear Bravo Turret"
                        x="159"
                        y="73"
                        width="12"
                        height="12"
                        @mouseenter="displayName($t('room.rearBravoTurret'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('rear_bravo_turret')}"
                    />
                    <path
                        id="Engine-Room"
                        serif:id="Engine Room"
                        d="M183,28.005l-2,1.241l0,5.553l2,1.242l0,7.941l-2,1.241l0,5.554l2,1.241l0,7.941l-2,1.242l0,5.553l2,1.241l0,5.005l-34,0l0,-50l34,0l0,5.005Z"
                        @mouseenter="displayName($t('room.engineRoom'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('engine_room')}"
                    />
                    <rect
                        id="Rear-Alpha-Turret"
                        serif:id="Rear Alpha Turret"
                        x="159"
                        y="11"
                        width="12"
                        height="12"
                        @mouseenter="displayName($t('room.rearAlphaTurret'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('rear_alpha_turret')}"
                    />
                    <rect
                        id="Icarus-Bay"
                        serif:id="Icarus Bay"
                        x="125"
                        y="73"
                        width="34"
                        height="22"
                        @mouseenter="displayName($t('room.icarusBay'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('icarus_bay')}"
                    />
                    <rect
                        id="Rear-Bravo-Storage"
                        serif:id="Rear Bravo Storage"
                        x="125"
                        y="61"
                        width="24"
                        height="12"
                        @mouseenter="displayName($t('room.rearBravoStorage'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('rear_bravo_storage')}"
                    />
                    <rect
                        id="Rear-Alpha-Storage"
                        serif:id="Rear Alpha Storage"
                        x="125"
                        y="23"
                        width="24"
                        height="12"
                        @mouseenter="displayName($t('room.rearAlphaStorage'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('rear_alpha_storage')}"
                    />
                    <rect
                        id="Rear-Corridor"
                        serif:id="Rear Corridor"
                        x="119"
                        y="23"
                        width="6"
                        height="60"
                        @mouseenter="displayName($t('room.rearCorridor'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('rear_corridor')}"
                    />
                    <rect
                        id="Alpha-Bay-2"
                        serif:id="Alpha Bay 2"
                        x="117"
                        y="1"
                        width="42"
                        height="22"
                        @mouseenter="displayName($t('room.alphaBay2'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('alpha_bay_2')}"
                    />
                    <path
                        id="Bravo-Dorm"
                        serif:id="Bravo Dorm"
                        d="M119,73l-20,0l0,-14l6,0l0,-4l14,0l0,18Z"
                        @mouseenter="displayName($t('room.bravoDorm'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('bravo_dorm')}"
                    />
                    <rect
                        id="Nexus"
                        x="105"
                        y="41"
                        width="14"
                        height="14"
                        @mouseenter="displayName($t('room.nexus'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('nexus')}"
                    />
                    <path
                        id="Alpha-Dorm"
                        serif:id="Alpha Dorm"
                        d="M119,23l-20,0l0,14l6,0l0,4l14,0l0,-18Z"
                        @mouseenter="displayName($t('room.alphaDorm'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('alpha_dorm')}"
                    />
                    <rect
                        id="Centre-Bravo-Storage"
                        serif:id="Centre Bravo Storage"
                        x="83"
                        y="59"
                        width="16"
                        height="14"
                        @mouseenter="displayName($t('room.centerBravoStorage'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('center_bravo_storage')}"
                    />
                    <rect
                        id="Refectory"
                        x="83"
                        y="37"
                        width="22"
                        height="22"
                        @mouseenter="displayName($t('room.refectory'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('refectory')}"
                    />
                    <rect
                        id="Centre-Alpha-Storage"
                        serif:id="Centre Alpha Storage"
                        x="83"
                        y="23"
                        width="16"
                        height="14"
                        @mouseenter="displayName($t('room.centerAlphaStorage'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('center_alpha_storage')}"
                    />
                    <rect
                        id="Bravo-Bay"
                        serif:id="Bravo Bay"
                        x="75"
                        y="73"
                        width="44"
                        height="22"
                        @mouseenter="displayName($t('room.bravoBay'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('bravo_bay')}"
                    />
                    <rect
                        id="Centre-Corridor"
                        serif:id="Centre Corridor"
                        x="77"
                        y="23"
                        width="6"
                        height="50"
                        @mouseenter="displayName($t('room.centralCorridor'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('central_corridor')}"
                    />
                    <rect
                        id="Alpha-Bay"
                        serif:id="Alpha Bay"
                        x="75"
                        y="1"
                        width="42"
                        height="22"
                        @mouseenter="displayName($t('room.alphaBay'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('alpha_bay')}"
                    />
                    <rect
                        id="Centre-Bravo-Turret"
                        serif:id="Centre Bravo Turret"
                        x="63"
                        y="73"
                        width="12"
                        height="12"
                        @mouseenter="displayName($t('room.centreBravoTurret'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('centre_bravo_turret')}"
                    />
                    <rect
                        id="Medlab"
                        x="54.983"
                        y="51"
                        width="22"
                        height="22"
                        @mouseenter="displayName($t('room.medlab'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('medlab')}"
                    />
                    <rect
                        id="Front-Storage"
                        serif:id="Front Storage"
                        x="55"
                        y="23"
                        width="22"
                        height="22"
                        @mouseenter="displayName($t('room.frontStorage'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('front_storage')}"
                    />
                    <rect
                        id="Centre-Alpha-Turret"
                        serif:id="Centre Alpha Turret"
                        x="63"
                        y="11"
                        width="12"
                        height="12"
                        @mouseenter="displayName($t('room.centreAlphaTurret'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('centre_alpha_turret')}"
                    />
                    <rect
                        id="Laboratory"
                        x="33"
                        y="51"
                        width="22"
                        height="22"
                        @mouseenter="displayName($t('room.laboratory'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('laboratory')}"
                    />
                    <rect
                        id="Hydroponic-Garden"
                        serif:id="Hydroponic Garden"
                        x="33"
                        y="23"
                        width="22"
                        height="22"
                        @mouseenter="displayName($t('room.hydroponicGarden'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('hydroponic_garden')}"
                    />
                    <path
                        id="Front-Corridor"
                        serif:id="Front Corridor"
                        d="M33,71l-6,0l0,-46l6,0l0,20l44,0l0,6l-44,0l0,20Z"
                        @mouseenter="displayName($t('room.frontCorridor'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('front_corridor')}"
                    />
                    <rect
                        id="Front-Bravo-Turret"
                        serif:id="Front Bravo Turret"
                        x="15"
                        y="61"
                        width="12"
                        height="12"
                        @mouseenter="displayName($t('room.frontBravoTurret'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('front_bravo_turret')}"
                    />
                    <path
                        id="Bridge"
                        d="M6,42.121l0,-7.121l21,0l0,26l-21,0l0,-7.121l-5,-3.102l0,-5.554l5,-3.102Z"
                        @mouseenter="displayName($t('room.bridge'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('bridge')}"
                    />
                    <rect
                        id="Front-Alpha-Turret"
                        serif:id="Front Alpha Turret"
                        x="15"
                        y="23"
                        width="12"
                        height="12"
                        @mouseenter="displayName($t('room.frontAlphaTurret'))"
                        @mouseleave="displayName('')"
                        :class="{fire: isFire('front_alpha_turret')}"
                    />
                </g>
                <g id="doors" v-for="door in doorsList" :key="door.name">
                    <path :id="door.name" :d="door.svg" :class="{broken : isBroken('door', `${door.room1} ${door.room2}`, door.name)}"/>
                </g>
                <g id="broken-equipment">
                    <g id="Rear-Bravo-Turret1" serif:id="Rear Bravo Turret">
                        <g id="Turret-Command" serif:id="Turret Command" :class="{visible : isBroken('equipment', 'rear_bravo_turret' , 'turret_command')}">
                            <rect
                                class="floor"
                                x="164"
                                y="78"
                                width="6"
                                height="6"
                            />
                            <path class="wall" d="M171,78l-0,7l-7,0" />
                        </g>
                    </g>
                    <g id="Engine-Room1" serif:id="Engine Room">
                        <g id="Reactor-3" serif:id="Reactor 3" :class="{visible : isBroken('equipment', 'engine_room' , 'reactor_lateral_bravo')}">
                            <path class="floor" serif:id="floor" d="M179.958,67l0,-6l-6,-0l0,6l6,0Z" />
                            <path class="wall" serif:id="wall" d="M180.958,61l0,6" />
                        </g>
                        <g id="Reactor-2" serif:id="Reactor 2" :class="{visible : isBroken('equipment', 'engine_room' , 'emergency_reactor')}">
                            <path class="floor" serif:id="floor" d="M179.958,51l0,-6l-6,0l0,6l6,0Z" />
                            <path class="wall" serif:id="wall" d="M180.958,45l0,6" />
                        </g>
                        <g id="Reactor-1" serif:id="Reactor 1" :class="{visible : isBroken('equipment', 'engine_room' , 'reactor_lateral_alpha')}">
                            <path class="floor" serif:id="floor" d="M179.958,35l0,-6l-6,0l0,6l6,0Z" />
                            <path class="wall" serif:id="wall" d="M180.958,29l0,6" />
                        </g>
                        <g id="Planet-Scanner" serif:id="Planet Scanner" :class="{visible : isBroken('equipment', 'engine_room' , 'planet_scanner')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="170"
                                y="68"
                                width="6"
                                height="4"
                            />
                            <path class="wall" serif:id="wall" d="M176,73l-6,0" />
                        </g>
                        <g id="Antenna" :class="{visible : isBroken('equipment', 'engine_room' , 'antenna')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="170"
                                y="24"
                                width="6"
                                height="4"
                            />
                            <path class="wall" serif:id="wall" d="M170,23l6,0" />
                        </g>
                        <g id="Combustion-Chamber" serif:id="Combustion Chamber" :class="{visible : isBroken('equipment', 'engine_room' , 'combustion_chamber')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="158"
                                y="58"
                                width="8"
                                height="8"
                            />
                        </g>
                        <g id="Auxiliary-NERON-Terminal" serif:id="Auxiliary NERON Terminal" :class="{visible : isBroken('equipment', 'engine_room' , 'auxiliary_terminal')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="157"
                                y="24"
                                width="4"
                                height="4"
                            />
                            <path class="wall" serif:id="wall" d="M157,23l4,0" />
                        </g>
                    </g>
                    <g id="Rear-Alpha-Turret1" serif:id="Rear Alpha Turret">
                        <g id="Turret-Command1" serif:id="Turret Command" :class="{visible : isBroken('equipment', 'rear_alpha_turret' , 'turret_command')}">
                            <path class="floor" serif:id="floor" d="M170,18l-0,-6l-6,-0l-0,6l6,-0Z" />
                            <path class="wall" serif:id="wall" d="M164,11l7,0l0,7" />
                        </g>
                    </g>
                    <g id="Icarus-Bay1" serif:id="Icarus Bay">
                        <g id="Icarus" :class="{visible : isBroken('equipment', 'icarus_bay' , 'icarus')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="130.958"
                                y="81"
                                width="22"
                                height="13"
                            />
                            <path class="wall" serif:id="wall" d="M152.958,95l-22,0" />
                        </g>
                    </g>
                    <g id="Rear-Bravo-Storage1" serif:id="Rear Bravo Storage">
                        <g id="Fuel-Tank" serif:id="Fuel Tank" :class="{visible : isBroken('equipment', 'rear_bravo_storage' , 'fuel_tank')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="128"
                                y="68"
                                width="6"
                                height="4"
                            />
                            <path class="wall" serif:id="wall" d="M134,73l-6,-0" />
                        </g>
                    </g>
                    <g id="Rear-Alpha-Storage1" serif:id="Rear Alpha Storage">
                        <g id="Fuel-Tank1" serif:id="Fuel Tank" :class="{visible : isBroken('equipment', 'rear_alpha_storage' , 'fuel_tank')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="128"
                                y="24"
                                width="6"
                                height="4"
                            />
                            <path class="wall" serif:id="wall" d="M128,23l6,0" />
                        </g>
                    </g>
                    <g id="Alpha-Bay-21" serif:id="Alpha Bay 2">
                        <g id="Pasiphae" :class="{visible : isBroken('equipment', 'alpha_bay_2' , 'pasiphae')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="146"
                                y="2"
                                width="10"
                                height="14"
                            />
                            <path class="wall" serif:id="wall" d="M146,1l10,0" />
                        </g>
                        <g id="Patrol-Ship-8" serif:id="Patrol Ship 8" :class="{visible : isBroken('equipment', 'alpha_bay_2' , 'patrol_ship_alpha_2_wallis')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="134"
                                y="2"
                                width="10"
                                height="12"
                            />
                            <path class="wall" serif:id="wall" d="M134,1l10,0" />
                        </g>
                        <g id="Dynarcade" serif:id="Dynarcade" :class="{visible : isBroken('equipment', 'alpha_bay_2', 'dynarcade')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="121.958"
                                y="2"
                                width="10"
                                height="12"
                            />
                            <path class="wall" serif:id="wall" d="M121.958,1l10,0" />
                        </g>
                        <g id="Jukebox" :class="{visible : isBroken('equipment', 'alpha_bay_2' , 'jukebox')}">
                            <path class="floor" serif:id="floor" d="M117.958,9l0,4l4,0l0,-4l-4,0Z" />
                            <path class="wall" serif:id="wall" d="M116.958,13l0,-4" />
                        </g>
                    </g>
                    <g id="Bravo-Dorm1" serif:id="Bravo Dorm">
                        <g id="Shower" :class="{visible : isBroken('equipment', 'bravo_dorm' , 'shower')}">
                            <path class="floor" serif:id="floor" d="M100,64l0,4l4,0l0,-4l-4,0Z" />
                            <path class="wall" serif:id="wall" d="M99,68l0,-4" />
                        </g>
                        <g id="Thalasso" serif:id="Thalasso" :class="{visible : isBroken('equipment', 'bravo_dorm' , 'thalasso')}">
                            <path class="floor" serif:id="floor" d="M100,64l0,4l4,0l0,-4l-4,0Z" />
                            <path class="wall" serif:id="wall" d="M99,68l0,-4" />
                        </g>
                    </g>
                    <g id="Nexus1" serif:id="Nexus">
                        <g id="BIOS-Terminal" serif:id="BIOS Terminal" :class="{visible : isBroken('equipment', 'nexus' , 'bios_terminal')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="111.983"
                                y="42"
                                width="4"
                                height="4"
                            />
                            <path class="wall" serif:id="wall" d="M111.983,41l4,0" />
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="112"
                                y="50"
                                width="4"
                                height="4"
                            />
                            <path class="wall" serif:id="wall" d="M116,55l-4,0" />
                        </g>
                        <g id="NERON-Core" serif:id="NERON Core" :class="{visible : isBroken('equipment', 'nexus' , 'neron_core')}">
                            <path class="floor" serif:id="floor" d="M106,44l0,8l4,0l0,-8l-4,-0Z" />
                            <path class="wall" serif:id="wall" d="M105,52l0,-8" />
                        </g>
                        <g id="Calculator" serif:id="Calculator" :class="{visible : isBroken('equipment', 'nexus' , 'calculator')}">
                            <path class="floor" serif:id="floor" d="M113,40l0,8l4,0l0,-8l-4,-0Z" />
                        </g>
                    </g>
                    <g id="Alpha-Dorm1" serif:id="Alpha Dorm">
                        <g id="Shower1" serif:id="Shower" :class="{visible : isBroken('equipment', 'alpha_dorm' , 'shower')}">
                            <path class="floor" serif:id="floor" d="M100,28l0,4l4,0l0,-4l-4,0Z" />
                            <path class="wall" serif:id="wall" d="M99,32l0,-4" />
                        </g>
                        <g id="Thalasso1" serif:id="Thalasso" :class="{visible : isBroken('equipment', 'alpha_dorm' , 'thalasso')}">
                            <path class="floor" serif:id="floor" d="M100,28l0,4l4,0l0,-4l-4,0Z" />
                            <path class="wall" serif:id="wall" d="M99,32l0,-4" />
                        </g>
                    </g>
                    <g id="Bravo-Bay1" serif:id="Bravo Bay">
                        <g id="Patrol-Ship-6" serif:id="Patrol Ship 6" :class="{visible : isBroken('equipment', 'bravo_bay' , 'patrol_ship_bravo_planton')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="103.958"
                                y="82"
                                width="10"
                                height="12"
                            />
                            <path class="wall" serif:id="wall" d="M113.958,95l-10,0" />
                        </g>
                        <g id="Patrol-Ship-5" serif:id="Patrol Ship 5" :class="{visible : isBroken('equipment', 'bravo_bay' , 'patrol_ship_bravo_epicure')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="91.958"
                                y="82"
                                width="10"
                                height="12"
                            />
                            <path class="wall" serif:id="wall" d="M101.958,95l-10,0" />
                        </g>
                        <g id="Patrol-Ship-4" serif:id="Patrol Ship 4" :class="{visible : isBroken('equipment', 'bravo_bay' , 'patrol_ship_bravo_socrate')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="80"
                                y="82"
                                width="10"
                                height="12"
                            />
                            <path class="wall" serif:id="wall" d="M90,95l-10,0" />
                        </g>
                    </g>
                    <g id="Centre-Bravo-Storage1" serif:id="Centre Bravo Storage">
                        <g id="Oxygen-Tank" serif:id="Oxygen Tank" :class="{visible : isBroken('equipment', 'center_bravo_storage' , 'oxygen_tank')}">
                            <path class="floor" serif:id="floor" d="M98,68l-0,-6l-4,0l-0,6l4,0Z" />
                            <path class="wall" serif:id="wall" d="M99,62l0,6" />
                        </g>
                    </g>
                    <g id="Refectory1" serif:id="Refectory">
                        <g id="Coffee-Machine" serif:id="Coffee Machine" :class="{visible : isBroken('equipment', 'refectory' , 'coffee_machine')}">
                            <path class="floor" serif:id="floor" d="M84,52l0,4l4,0l0,-4l-4,0Z" />
                            <path class="wall" serif:id="wall" d="M83,56l0,-4" />
                        </g>
                        <g id="Kitchen" :class="{visible : isBroken('equipment', 'refectory' , 'kitchen')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="84"
                                y="38"
                                width="20"
                                height="4"
                            />
                            <path class="wall" serif:id="wall" d="M84,37l20,-0" />
                        </g>
                    </g>
                    <g id="Centre-Alpha-Storage1" serif:id="Centre Alpha Storage" >
                        <g id="Oxygen-Tank1" serif:id="Oxygen Tank" :class="{visible : isBroken('equipment', 'center_alpha_storage' , 'oxygen_tank')}">
                            <path class="floor" serif:id="floor" d="M84,28l0,6l4,0l0,-6l-4,0Z" />
                            <path class="wall" serif:id="wall" d="M83,34l0,-6" />
                        </g>
                    </g>
                    <g id="Alpha-Bay1" serif:id="Alpha Bay">
                        <g id="Patrol-Ship-3" serif:id="Patrol Ship 3" :class="{visible : isBroken('equipment', 'alpha_bay' , 'patrol_ship_alpha_tamarin')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="102.958"
                                y="2"
                                width="10"
                                height="12"
                            />
                            <path class="wall" serif:id="wall" d="M102.958,1l10,0" />
                        </g>
                        <g id="Patrol-Ship-2" serif:id="Patrol Ship 2" :class="{visible : isBroken('equipment', 'alpha_bay' , 'patrol_ship_alpha_jujube')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="91"
                                y="2"
                                width="10"
                                height="12"
                            />
                            <path class="wall" serif:id="wall" d="M91,1l10,0" />
                        </g>
                        <g id="Patrol-Ship-1" serif:id="Patrol Ship 1" :class="{visible : isBroken('equipment', 'alpha_bay' , 'patrol_ship_alpha_longane')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="79"
                                y="2"
                                width="10"
                                height="12"
                            />
                            <path class="wall" serif:id="wall" d="M79,1l10,0" />
                        </g>
                    </g>
                    <g id="Centre-Bravo-Turret1" serif:id="Centre Bravo Turret">
                        <g id="Turret-Command2" serif:id="Turret Command" :class="{visible : isBroken('equipment', 'centre_bravo_turret' , 'turret_command')}">
                            <path class="floor" serif:id="floor" d="M64,78l0,6l6,0l0,-6l-6,0Z" />
                            <path class="wall" serif:id="wall" d="M70,85l-7,-0l0,-7" />
                        </g>
                    </g>
                    <g id="Medlab1" serif:id="Medlab">
                        <g id="Auxiliary-NERON-Terminal1" serif:id="Auxiliary NERON Terminal" :class="{visible : isBroken('equipment', 'medlab' , 'auxiliary_terminal')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="69.983"
                                y="52"
                                width="4"
                                height="4"
                            />
                            <path class="wall" serif:id="wall" d="M69.983,51l4,-0" />
                        </g>
                        <g id="Surgery-Pod" serif:id="Surgery Pod" :class="{visible : isBroken('equipment', 'medlab' , 'surgery_plot')}">
                            <path class="floor" serif:id="floor" d="M56,64l0,8l4,0l0,-8l-4,0Z" />
                            <path class="wall" serif:id="wall" d="M55,72l0,-8" />
                        </g>
                    </g>
                    <g id="Centre-Alpha-Turret1" serif:id="Centre Alpha Turret">
                        <g id="Turret-Command3" serif:id="Turret Command" :class="{visible : isBroken('equipment', 'centre_alpha_turret' , 'turret_command')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="64"
                                y="12"
                                width="6"
                                height="6"
                            />
                            <path class="wall" serif:id="wall" d="M63,18l0,-7l7,0" />
                        </g>
                    </g>
                    <g id="Laboratory1" serif:id="Laboratory">
                        <g id="Mycoscan" :class="{visible : isBroken('equipment', 'laboratory' , 'mycoscan')}">
                            <path class="floor" serif:id="floor" d="M46,60l0,8l6,0l0,-8l-6,0Z" />
                        </g>
                        <g id="Gravity-Simulator" serif:id="Gravity Simulator" :class="{visible : isBroken('equipment', 'laboratory' , 'gravity_simulator')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="42"
                                y="52"
                                width="4"
                                height="4"
                            />
                            <path class="wall" serif:id="wall" d="M41.983,51l4,-0" />
                        </g>
                        <g id="Research-Lab" serif:id="Research Lab" :class="{visible : isBroken('equipment', 'laboratory' , 'research_laboratory')}">
                            <path class="floor" serif:id="floor" d="M34,64l0,8l4,0l0,-8l-4,0Z" />
                            <path class="wall" serif:id="wall" d="M33,72l-0,-8" />
                        </g>
                    </g>
                    <g id="Bridge1" serif:id="Bridge">
                        <g id="AstroTerminal" :class="{visible : isBroken('equipment', 'bridge' , 'astro_terminal')}">
                            <path class="floor" serif:id="floor" d="M7,54l0,6l9,-0l-0,-6l-9,-0Z" />
                            <path class="wall" serif:id="wall" d="M16,61l-10,-0l0,-7" />
                        </g>
                        <g id="Command-Terminal" serif:id="Command Terminal" :class="{visible : isBroken('equipment', 'bridge' , 'command_terminal')}">
                            <path class="floor" serif:id="floor" d="M2,44l0,8l12,0l0,-8l-12,0Z" />
                            <path class="wall" serif:id="wall" d="M1,52l-0,-8" />
                        </g>
                        <g id="Comms-Center" serif:id="Comms Center" :class="{visible : isBroken('equipment', 'bridge' , 'communication_center')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="7"
                                y="36"
                                width="9"
                                height="6"
                            />
                            <path class="wall" serif:id="wall" d="M6,42l0,-7l10,-0" />
                        </g>
                        <g id="Front-BravoTurret" serif:id="Front BravoTurret">
                            <g id="Turret-Command4" serif:id="Turret Command">
                                <path class="floor" serif:id="floor" d="M16,66l-0,6l6,0l-0,-6l-6,0Z" />
                                <path class="wall" serif:id="wall" d="M22,73l-7,0l-0,-7" />
                            </g>
                        </g>
                    </g>
                    <g id="Front-Alpha-Turret1" serif:id="Front Alpha Turret">
                        <g id="Turret-Command5" serif:id="Turret Command" :class="{visible : isBroken('equipment', 'front_alpha_turret' , 'turret_command')}">
                            <rect
                                class="floor"
                                serif:id="floor"
                                x="16"
                                y="24"
                                width="6"
                                height="6"
                            />
                            <path class="wall" serif:id="wall" d="M15,30l0,-7l7,-0" />
                        </g>
                    </g>
                </g>
                <g id="broken-cameras">
                    <g id="Rear-Bravo-Turret2" serif:id="Rear Bravo Turret" :class="{visible : isBroken('equipment', 'rear_bravo_turret', 'camera_equipment')}">
                        <g class="cam">
                            <path d="M162,76l4,4" />
                            <path d="M162,80l4,-4" />
                        </g>
                    </g>
                    <g id="Engine-Room2" serif:id="Engine Room" :class="{visible : isBroken('equipment', 'engine_room', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M162,46l4,4" />
                            <path d="M162,50l4,-4" />
                        </g>
                    </g>
                    <g id="Rear-Alpha-Turret2" serif:id="Rear Alpha Turret" :class="{visible : isBroken('equipment', 'rear_alpha_turret', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M162,16l4,4" />
                            <path d="M162,20l4,-4" />
                        </g>
                    </g>
                    <g id="Icarus-Bay2" serif:id="Icarus Bay" :class="{visible : isBroken('equipment', 'icarus_bay', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M140,77l4,4" />
                            <path d="M140,81l4,-4" />
                        </g>
                    </g>
                    <g id="Rear-Bravo-Storage2" serif:id="Rear Bravo Storage" :class="{visible : isBroken('equipment', 'rear_bravo_storage', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M137,65l4,4" />
                            <path d="M137,69l4,-4" />
                        </g>
                    </g>
                    <g id="Rear-Alpha-Storage2" serif:id="Rear Alpha Storage" :class="{visible : isBroken('equipment', 'rear_alpha_storage', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M137,27l4,4" />
                            <path d="M137,31l4,-4" />
                        </g>
                    </g>
                    <g id="Rear-Corridor1" serif:id="Rear Corridor" :class="{visible : isBroken('equipment', 'rear_corridor', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M120,39l4,4" />
                            <path d="M120,43l4,-4" />
                        </g>
                    </g>
                    <g id="Alpha-Bay-22" serif:id="Alpha Bay 2" :class="{visible : isBroken('equipment', 'alpha_bay_2', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M137,14l4,4" />
                            <path d="M137,18l4,-4" />
                        </g>
                    </g>
                    <g id="Bravo-Dorm2" serif:id="Bravo Dorm" :class="{visible : isBroken('equipment', 'bravo_dorm', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M108,62l4,4" />
                            <path d="M108,66l4,-4" />
                        </g>
                    </g>
                    <g id="Nexus2" serif:id="Nexus" :class="{visible : isBroken('equipment', 'nexus', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M109.962,46l4,4" />
                            <path d="M109.962,50l4,-4" />
                        </g>
                    </g>
                    <g id="Alpha-Dorm2" serif:id="Alpha Dorm" :class="{visible : isBroken('equipment', 'alpha_dorm', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M109,29l4,4" />
                            <path d="M109,33l4,-4" />
                        </g>
                    </g>
                    <g id="Centre-Bravo-Storage2" serif:id="Centre Bravo Storage" :class="{visible : isBroken('equipment', 'center_bravo_storage', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M89,64l4,4" />
                            <path d="M89,68l4,-4" />
                        </g>
                    </g>
                    <g id="Refectory2" serif:id="Refectory" :class="{visible : isBroken('equipment', 'refectory', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M92,46l4,4" />
                            <path d="M92,50l4,-4" />
                        </g>
                    </g>
                    <g id="Centre-Alpha-Storage2" serif:id="Centre Alpha Storage" :class="{visible : isBroken('equipment', 'center_alpha_storage', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M89,28l4,4" />
                            <path d="M89,32l4,-4" />
                        </g>
                    </g>
                    <g id="Bravo-Bay2" serif:id="Bravo Bay" :class="{visible : isBroken('equipment', 'bravo_bay', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M95,78l4,4" />
                            <path d="M95,82l4,-4" />
                        </g>
                    </g>
                    <g id="Centre-Corridor1" serif:id="Centre Corridor" :class="{visible : isBroken('equipment', 'central_corridor', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M78,39l4,4" />
                            <path d="M78,43l4,-4" />
                        </g>
                    </g>
                    <g id="Alpha-Bay2" serif:id="Alpha Bay" :class="{visible : isBroken('equipment', 'alpha_bay', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M94,14l4,4" />
                            <path d="M94,18l4,-4" />
                        </g>
                    </g>
                    <g id="Centre-Bravo-Turret2" serif:id="Centre Bravo Turret" :class="{visible : isBroken('equipment', 'centre_bravo_turret', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M68,76l4,4" />
                            <path d="M68,80l4,-4" />
                        </g>
                    </g>
                    <g id="Medlab2" serif:id="Medlab" :class="{visible : isBroken('equipment', 'medlab', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M63.983,60l4,4" />
                            <path d="M63.983,64l4,-4" />
                        </g>
                    </g>
                    <g id="Front-Storage1" serif:id="Front Storage" :class="{visible : isBroken('equipment', 'front_storage', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M64,32l4,4" />
                            <path d="M64,36l4,-4" />
                        </g>
                    </g>
                    <g id="Centre-Alpha-Turret2" serif:id="Centre Alpha Turret" :class="{visible : isBroken('equipment', 'centre_alpha_turret', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M68,16l4,4" />
                            <path d="M68,20l4,-4" />
                        </g>
                    </g>
                    <g id="Laboratory2" serif:id="Laboratory" :class="{visible : isBroken('equipment', 'laboratory', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M40,59l4,4" />
                            <path d="M40,63l4,-4" />
                        </g>
                    </g>
                    <g id="Hydroponic-Garden1" serif:id="Hydroponic Garden" :class="{visible : isBroken('equipment', 'hydroponic_garden', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M42,32l4,4" />
                            <path d="M42,36l4,-4" />
                        </g>
                    </g>
                    <g id="Front-Corridor1" serif:id="Front Corridor" :class="{visible : isBroken('equipment', 'front_corridor', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M30,46l4,4" />
                            <path d="M30,50l4,-4" />
                        </g>
                    </g>
                    <g id="Front-Bravo-Turret1" serif:id="Front Bravo Turret" :class="{visible : isBroken('equipment', 'front_bravo_turret', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M20,64l4,4" />
                            <path d="M20,68l4,-4" />
                        </g>
                    </g>
                    <g id="Bridge2" serif:id="Bridge" :class="{visible : isBroken('equipment', 'bridge', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M17,46l4,4" />
                            <path d="M17,50l4,-4" />
                        </g>
                    </g>
                    <g id="Front-Alpha-Turret2" serif:id="Front Alpha Turret" :class="{visible : isBroken('equipment', 'front_alpha_turret', 'camera_equipment')}">
                        <g class="cam" serif:id="cam">
                            <path d="M20,28l4,4" />
                            <path d="M20,32l4,-4" />
                        </g>
                    </g>
                </g>
            </svg>
            <ul class="crew-position">
                <li
                    class="self"
                    :style="{
                        left: `${me.left}px`,
                        top: `${me.top}px`
                    }"
                />
                <li
                    v-for="player in playersPoints"
                    :key="player.name"
                    :style="{
                        left: `${player.left}px`,
                        top: `${player.top}px`
                    }"
                    :class="`${player.name}`"
                />
            </ul>
        </div>
        <h3 class="room">
            {{roomName}}
        </h3>
    </div>
</template>


<script lang="ts">
import { defineComponent } from "vue";
import { RoomsEnum } from '@/enums/room.enum';
import { DoorsEnum } from '@/enums/doors.enum';
import { Minimap } from "@/entities/Minimap";
import { mapGetters } from "vuex";

export default defineComponent ({
    name: "MiniMap",
    props: {
        myPosition: Object
    },
    computed: {
        ...mapGetters('daedalus', [
            'minimap',
            'loadingMinimap'
        ])
    },
    data() {
        return {
            roomName : '' as string,
            actopi: false as boolean,
            me : {
                left: 0 as number,
                top: 0 as number
            },
            playersPoints: [] as Array<PlayersPoints>,
            doorsList: DoorsEnum as Array<Doors>
        };
    },
    methods: {
        displayName(room: string): void {
            this.roomName = room;
        },
        displayMe(): void {
            const myCoord = RoomsEnum[this.myPosition?.key];
            this.me.left = myCoord.A.x + Math.round(Math.random() * (myCoord.B.x - 6 - myCoord.A.x) );
            this.me.top = myCoord.A.y + Math.round(Math.random() * (myCoord.C.y - 6 - myCoord.B.y));
        },
        displayOther(): void
        {
            if (this.minimap){
                this.playersPoints = [];
                const roomsWithPlayers = this.minimap.filter((room: Minimap) => room.players_count > 0);
                roomsWithPlayers.forEach((room: Minimap) => {
                    const roomCoord = RoomsEnum[room.name];
                    let i = 0;
                    if (room.name === this.myPosition?.key) {
                        i++;
                    }
                    while ( i < room.players_count ){
                        const left = roomCoord.A.x + Math.round(Math.random() * (roomCoord.B.x - 4 - roomCoord.A.x));
                        const top = roomCoord.A.y + Math.round(Math.random() * (roomCoord.C.y - 4 - roomCoord.B.y));
                        const name = '';
                        this.playersPoints.push( { left, top, name } );
                        i++;
                    }
                });
            }
        },
        isFire(room: string): boolean {
            return this.minimap.find((rooms: Minimap) => rooms.name === room)!.fire;
        },
        isBroken(type: string, room: string, object: string): boolean {
            switch (type) {
            case 'door':
                return this.minimap.find((rooms: Minimap) => rooms.name === room.split(' ')[0])!.broken_doors.includes(object) || this.minimap.find((rooms: Minimap) => rooms.name === room.split(' ')[1])!.broken_doors.includes(object);
            case 'equipment':
                return this.minimap.find((rooms: Minimap) => rooms.name === room)!.broken_equipments.includes(object);
            default:
                return false;
            }
        }
    },
    mounted(): void {
        this.displayMe();
        this.displayOther();
    },
    watch: {
        minimap(): void {
            this.displayMe();
            this.displayOther();
        }
    }
});

interface PlayersPoints {
    left: number;
    top: number;
    name: string;
}
interface Doors {
    name: string,
    svg: string,
    room1: string,
    room2: string
}
</script>
<style lang="scss" scoped>

.map-container {
    position:absolute;
    align-self: flex-end;
    z-index: 5;
    // bottom: 200px;
    width: 240px;
    height: 200px;
    background: #070724;

    @include corner-bezel(0, 18px, 0);

    transform: scale(0.5);
    transform-origin: bottom left;
    transition: transform 0.5s;

    &:hover, &:focus, &:focus-within, &:active {
        transform: scale(1);
        transition: transform 0.4s 0.2s;

        .room {
            display: initial;
        }
    }
}

.map {
    width: 184px;
    height: 96px;
    margin: auto;
    filter: drop-shadow(0 0 8px rgba(18,215,255,.6));
    transform: translate(-0.4em, -0.4em) rotate(30deg);
}

svg {
    #rooms {
        fill: #133951;
        stroke: #1d5f80;
        stroke-width: 2px;

        .fire {
            fill: #a6181e;
        }

        & > * {
            &:hover, &:focus, :active {
                fill: #3981a8;
            }
        }
    }
}

#doors {
    fill: none;
    pointer-events: none;
    stroke: #12d7ff;
    stroke-width: 2px;

    .broken {
        stroke: #f8de05;
    }
}

#broken-equipment {
    visibility: hidden;
    pointer-events: none;

    .floor {
        fill: #ff9c00;
        fill-opacity: 0.3;
        stroke: none;
    }

    .wall {
        fill: none;
        stroke: #ff9c00;
        stroke-width: 2px;
        stroke-linecap: butt;
    }

    .visible {
        visibility: visible;
    }
}

#broken-cameras {
    visibility: hidden;
    pointer-events: none;
    fill: none;
    stroke: #ffce80;
    stroke-width: 1.8px;
    .visible {
        visibility: visible;
    }
}

.crew-position {
    position: absolute;
    pointer-events: none;

    li {
        position: absolute;
        width: 4px;
        height: 4px;
        background: #f88;
        box-shadow: 0 0 2px 1px #133951;
        &.hidden {
            background: none;
            box-shadow: none;
        }

        // @for $i from 1 through 19 { // randomize crew position, for testing only
        //     &:nth-child(#{$i}) {
        //         left: random(156) + 10px;
        //         top: random(52) + 18px;
        //     }
        // }

        &.self {
            z-index: 2;
            width: 6px;
            height: 6px;
            background: #889c28;
            animation: self-position-color 1.1s infinite;
        }

        $actopi-color: //defines the color of each crewmate in the minimap when actopi project is active
            "Andie"    rgb(135, 206, 250),
            "Chao"     rgb(88 , 99 , 177),
            "Chun"     rgb(221, 211, 202),
            "Derek"    rgb(255, 69 , 0  ), //rgb(136, 156, 40) ?
            "Eleesha"  rgb(208, 133, 0  ),
            "Finola"   rgb(78 , 169, 182),
            "Frieda"   rgb(32 , 103, 134),
            "Gioele"   rgb(221, 112, 42 ),
            "Hua"      rgb(128, 84 , 65 ),
            "Ian"      rgb(35 , 124, 104),
            "Janice"   rgb(180, 71 , 83 ),
            "Jin"      rgb(192, 48 , 76 ),
            "Kuan"     rgb(243, 155, 1  ),
            "Paola"    rgb(255, 154, 250),
            "Raluca"   rgb(134, 134, 129),
            "Roland"   rgb(255, 163, 109),
            "Stephen"  rgb(255, 255, 255),
            "Terrence" rgb(211, 40 , 55 ),
        ;

        @each $crewmate, $color in $actopi-color {
            &.#{$crewmate} {
                background: $color;
            }
        }
    }
}

.room {
    display: none;
    position: absolute;
    bottom: 0;
    z-index: 5;
    margin: 8px 16px;
    font-family: $font-pixel-square;
    font-size: 10px;
    font-weight: 700;
}


@keyframes self-position-color {
    0% { background: #ff0; }
    39% { background: #ff0; }
    40% { background: #889c28; }
    100% { background: #889c28; }
}

</style>
