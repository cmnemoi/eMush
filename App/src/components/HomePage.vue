<template>
    <div class="homepage-container">
        <div class="daedalus-container">
            <img src="@/assets/images/daedalus_home.png" alt="Daedalus" />
        </div>
        <div class="trailer-container">
            <video controls ref="trailer">
                <source src="@/assets/videos/trailer_fr.mp4" type="video/mp4" />
                Sorry, your browser doesn't support embedded videos.
            </video>
        </div>
        <div class="award-container">
            <img src="@/assets/images/eigd_fr.png" alt="Award" />
        </div>
        <div class="box-container" id="play-container">
            <p>
                Vous allez vous réveiller avec 15 autres joueurs dans le Daedalus. 
                Comme vous, ils fuient le <em>Mush</em>, 
                un champignon qui ravage la planète Terre et menace l'Humanité.
            </p>
            <router-link class="action-button" :to="{ name: 'GamePage' }">
                Jouer !
            </router-link>
        </div>
        <div class="box-container" id="character-animation-container" style="display:none">
            <h2>18 personnages, prêts à chasser le Mush avec vous !</h2>
            <div class="character-image">
                <img src="@/assets/images/char/portrait/Eleesha_williams_portrait.jpg" alt="Eleesha Williams" />
            </div>
            <div class="character-description">
                <h3>Eleesha Williams</h3>
                <p>Investigatrice déchûe de premier plan.</p>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent ({
    name: "HomePage"
});
</script>

<style lang="scss" scoped>

.homepage-container {
    display: grid;
    grid-template-columns: 33% 1fr 33%;
    max-width: 1080px;
    margin: -3% 10% 0 10%;
    
    .daedalus-container {
        grid-column: 1;
        width: 33%;

        img {
            background: none;
        }
    }
    .trailer-container {
        grid-column: 2 / 4;
        margin-top: 12.5%;
    }

    .award-container {
        grid-column: 4;
        margin-top: 40%;
        padding-left: 20%;
        
        img {
            background: none;
        }
    }

    #play-container {
        grid-column: 2 / 4;
        grid-row: 2;
        margin: 0;
    }

    p {
        text-align: center;
        font-size: 1.1em;
        line-height: 1.4;
    }

    img {
        width: auto;
        height: auto;
        margin: 0.6em auto .1em;
        padding: .6em;
        border-radius: .4em;
        background-color: rgba(15, 15, 67, .5);
    }

    &::v-deep a {
        color: $green;
        &:hover { color: #e9ebf3; }
    }

    &::v-deep em {
        color: #01c3df;
        font-size: 1.2em;
        font-style: normal;
        font-weight: bold;

        &.red {
            color: inherit;
            text-decoration: underline;
            text-decoration-color:#ff4e64;
        }
    }

    .action-button {
    @include button-style();
    padding: 2px 15px 4px;
}

@each $character, $face-position-x, $face-position-y in $face-position { // adjust the image position in the character avatar div
    $translate-x : (50% - $face-position-x);
    $translate-y : (50% - $face-position-y);
    .character-image img {
        transform: translate($translate-x, $translate-y);
    }
}
}

</style>
