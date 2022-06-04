interface coordininate {
    x: number;
    y: number;
};

interface Rooms {
    A: coordininate;
    B: coordininate;
    C: coordininate;
    D: coordininate;
    id: number
}


export const RoomsEnum: {[name: string]: Rooms} = {
    bridge: {
        A : { x: 6, y: 35 },
        B : { x: 27, y: 35 },
        C : { x: 27, y: 61 },
        D : { x: 6, y: 61 },
        id: 1
    },
    alpha_bay: {
        A : { x: 75, y: 1 },
        B : { x: 117, y: 1 },
        C : { x: 117, y: 23 },
        D : { x: 75, y: 23 },
        id: 2
    },
    bravo_bay: {
        A : { x: 75, y: 73 },
        B : { x: 119, y: 73 },
        C : { x: 119, y: 95 },
        D : { x: 75, y: 95 },
        id: 3
    },
    alpha_bay_2: {
        A : { x: 117, y: 1 },
        B : { x: 159, y: 1 },
        C : { x: 159, y: 23 },
        D : { x: 117, y: 23 },
        id: 4
    },
    nexus: {
        A : { x: 105, y: 41 },
        B : { x: 119, y: 41 },
        C : { x: 119, y: 55 },
        D : { x: 105, y: 55 },
        id: 5
    },
    medlab: {
        A : { x: 55, y: 51 },
        B : { x: 77, y: 51 },
        C : { x: 77, y: 73 },
        D : { x: 55, y: 73 },
        id: 6
    },
    laboratory: {
        A : { x: 33, y: 51 },
        B : { x: 55, y: 51 },
        C : { x: 55, y: 73 },
        D : { x: 33, y: 73 },
        id: 7
    },
    refectory: {
        A : { x: 83, y: 37 },
        B : { x: 105, y: 37 },
        C : { x: 105, y: 59 },
        D : { x: 83, y: 59 },
        id: 8
    },
    hydroponic_garden: {
        A : { x: 33, y: 23 },
        B : { x: 55, y: 23 },
        C : { x: 55, y: 45 },
        D : { x: 33, y: 45 },
        id: 9
    },
    engine_room: {
        A : { x: 63, y: 73 },
        B : { x: 75, y: 73 },
        C : { x: 75, y: 85 },
        D : { x: 63, y: 85 },
        id: 10
    },
    front_alpha_turret: {
        A : { x: 15, y: 23 },
        B : { x: 27, y: 23 },
        C : { x: 27, y: 35 },
        D : { x: 15, y: 35 },
        id: 11
    },
    centre_alpha_turret: {
        A : { x: 63, y: 11 },
        B : { x: 75, y: 11 },
        C : { x: 75, y: 23 },
        D : { x: 63, y: 23 },
        id: 12
    },
    rear_alpha_turret: {
        A : { x: 159, y: 11 },
        B : { x: 171, y: 11 },
        C : { x: 171, y: 23 },
        D : { x: 159, y: 23 },
        id: 13
    },
    front_bravo_turret: {
        A : { x: 15, y: 61 },
        B : { x: 27, y: 61 },
        C : { x: 27, y: 73 },
        D : { x: 15, y: 73 },
        id: 14
    },
    centre_bravo_turret: {
        A : { x: 63, y: 73 },
        B : { x: 75, y: 73 },
        C : { x: 75, y: 85 },
        D : { x: 63, y: 85 },
        id: 15
    },
    rear_bravo_turret: {
        A : { x: 159, y: 73 },
        B : { x: 171, y: 73 },
        C : { x: 171, y: 85 },
        D : { x: 159, y: 85 },
        id: 16
    },
    front_corridor: {
        A : { x: 63, y: 73 },
        B : { x: 75, y: 73 },
        C : { x: 75, y: 85 },
        D : { x: 63, y: 85 },
        id: 17
    },
    central_corridor: {
        A : { x: 77, y: 23 },
        B : { x: 83, y: 23 },
        C : { x: 83, y: 73 },
        D : { x: 77, y: 73 },
        id: 18
    },
    rear_corridor: {
        A : { x: 119, y: 23 },
        B : { x: 125, y: 23 },
        C : { x: 125, y: 83 },
        D : { x: 119, y: 83 },
        id: 19
    },
    icarus_bay: {
        A : { x: 125, y: 73 },
        B : { x: 159, y: 73 },
        C : { x: 159, y: 95 },
        D : { x: 125, y: 95 },
        id: 20
    },
    alpha_dorm: {
        A : { x: 63, y: 73 },
        B : { x: 75, y: 73 },
        C : { x: 75, y: 85 },
        D : { x: 63, y: 85 },
        id: 21
    },
    bravo_dorm: {
        A : { x: 63, y: 73 },
        B : { x: 75, y: 73 },
        C : { x: 75, y: 85 },
        D : { x: 63, y: 85 },
        id: 22
    },
    front_storage: {
        A : { x: 55, y: 23 },
        B : { x: 77, y: 23 },
        C : { x: 77, y: 45 },
        D : { x: 55, y: 45 },
        id: 23
    },
    center_alpha_storage: {
        A : { x: 83, y: 23 },
        B : { x: 99, y: 23 },
        C : { x: 99, y: 37 },
        D : { x: 83, y: 37 },
        id: 24
    },
    center_bravo_storage: {
        A : { x: 83, y: 59 },
        B : { x: 99, y: 59 },
        C : { x: 99, y: 73 },
        D : { x: 83, y: 73 },
        id: 25
    },
    rear_alpha_storage: {
        A : { x: 125, y: 23 },
        B : { x: 149, y: 23 },
        C : { x: 149, y: 35 },
        D : { x: 125, y: 35 },
        id: 26
    },
    rear_bravo_storage: {
        A : { x: 125, y: 61 },
        B : { x: 149, y: 61 },
        C : { x: 149, y: 73 },
        D : { x: 125, y: 73 },
        id: 27
    }
}
;
