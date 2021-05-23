<template>
    <div v-show="loading" class="spinner_overlay">
        <div class="spinner-container">
            <span>Loading...</span>
            <div class="spinner">
                <div class="top">
                    <img src="@/assets/images/floor-tiles.gif" />
                </div>
                <div class="topleft">
                    <img src="@/assets/images/floor-tiles.gif" />
                </div>
                <div class="topright">
                    <img src="@/assets/images/floor-tiles.gif" />
                </div>
                <div class="left">
                    <img src="@/assets/images/floor-tiles.gif" />
                </div>
                <div class="right">
                    <img src="@/assets/images/floor-tiles.gif" />
                </div>
                <div class="bottomleft">
                    <img src="@/assets/images/floor-tiles.gif" />
                </div>
                <div class="bottomright">
                    <img class="cat" src="@/assets/images/char/body/cat.png" />
                    <img src="@/assets/images/floor-tiles.gif" />
                </div>
                <div class="bottom">
                    <img src="@/assets/images/floor-tiles.gif" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Spinner',
    props: {
        loading: {
            type: Boolean,
            default: true
        }
    }
};
</script>

<style  lang="scss" scoped>

.spinner_overlay { //dark background
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
    background-color: rgba(15, 15, 67, .75);
    z-index: 100;
}

.spinner-container {
    align-items: center;
    width: 96px;

    span {
        text-align: center;
        margin-bottom: 2em;
        font-variant: small-caps;
        font-weight: bold;
        letter-spacing: .02em;
    }
}

.spinner { //moving tiles
    position: relative;
    z-index: 100;
    width: 32px;

    div {
        position: absolute;
        overflow: hidden;
        width: 32px;
        padding-top: 7px;
        animation: movingtile 4s cubic-bezier(0.25, 0, 0.15, 1) infinite;
        animation-fill-mode: both;
    }

    img { width: 192px; }

    img.cat {
        position: absolute;
        z-index: 1;
        top: 0;
        left: 4px;
        width: 22px;
    }

    .top {
        top: -16px;
        animation-delay: 0;
        img { transform: translateX(calc(5*-32px)); }
    }
    
    .topleft {
        left: -16px; top: -8px;
        animation-delay: 0.5s;
        img { transform: translateX(calc(0*-32px)); }
    }

    .topright {
        left:  16px; top: -8px;
        animation-delay: 3.5s;
        img { transform: translateX(calc(1*-32px)); }
    }

    .left {
        left: -32px;
        animation-delay: 1s;
        img { transform: translateX(calc(2*-32px)); }
    }

    .right {
        left:  32px;
        animation-delay: 3s;
        img { transform: translateX(calc(4*-32px)); }
    }

    .bottomleft {
        left: -16px; top:  8px;
        animation-delay: 1.5s;
        img { transform: translateX(calc(1*-32px)); }
    }

    .bottomright {
        left:  16px; top:  8px;
        animation-delay: 2.5s;
        img { transform: translateX(calc(0*-32px)); }
    }

    .bottom {
        top:  16px;
        animation-delay: 2s;
        img { transform: translateX(calc(3*-32px)); }
    }
}

@keyframes movingtile {
    0%, 5% {
        opacity: 0;
        transform: translateY(28px);
    }
    45%, 60% {
        opacity: 1;
        transform: translateY(0);
    }
    85%, 100% {
        opacity: 0;
        transform: translateY(-8px);
    }
}

</style>
