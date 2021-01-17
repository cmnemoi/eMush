<template>
    <div class="map-container">
        <div class="map">
            <img src="@/assets/images/shipmap.svg">
            <ul class="crew-position">
                <li class="self" />
                <li />
                <li />
                <li />
                <li />
                <li />
                <li />
                <li />
                <li />
                <li />
                <li />
                <li />
                <li />
                <li />
                <li />
                <li />
            </ul>
        </div>
    </div>
</template>

<style lang="scss" scoped>

.map-container {
    align-self: flex-end;
    z-index: 5;
    bottom: 200px;
    width: 240px;
    height: 200px;
    background: #070724;

    @include corner-bezel(0, 18px, 0);

    transform: scale(0.5);
    transform-origin: bottom left;
    transition: transform 0.5s;

    &:hover {
        transform: scale(1);
        transition: transform 0.6s 0.2s;
    }

    & .map {
        width: 184px;
        height: 96px;
        margin: auto;
        transform: rotate(30deg);
    }

    & .crew-position {
        position: absolute;

        li {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #f88;

            @for $i from 1 through 16 { // randomize crew position, for testing only
                &:nth-child(#{$i}) {
                    left: random(156) + 10px;
                    top: random(52) + 18px;
                }
            }

            &.self {
                z-index: 2;
                width: 6px;
                height: 6px;
                background: #889c28;
                animation: self-position-color 1.1s infinite;
            }
        }
    }
}

@keyframes self-position-color {
    0% { background: #ff0; }
    39% { background: #ff0; }
    40% { background: #889c28; }
    100% { background: #889c28; }
}

</style>
