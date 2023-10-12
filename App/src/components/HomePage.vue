<template>
    <div class="homepage-container">
        <img class="daedalus-container" src="@/assets/images/daedalus_home.png" alt="Daedalus" />
        <div class="trailer-container">
            <video
                v-if="localeIsFrench()"
                controls
                ref="trailer"
                preload="metadata">
                <source src="@/assets/videos/trailer_fr.mp4#t=1" type="video/mp4"/>
                Désolé, votre navigateur ne supporte pas les vidéos intégrées.
            </video>
            <video
                v-else
                controls
                ref="trailer"
                preload="metadata">
                <source src="@/assets/videos/trailer_en.mp4#t=1" type="video/mp4"/>
                Sorry, your browser doesn't support embedded videos.
            </video>
        </div>
        <img
            class="award-container"
            v-if="localeIsFrench()"
            src="@/assets/images/eigd_fr.png"
            alt="Award" />
        <img
            class="award-container"
            v-else
            src="@/assets/images/eigd_en.png"
            alt="Award" />
        <div class="box-container" id="play-container">
            <p v-html="$t('homePage.synopsis')" />
            <router-link v-if="loggedIn" class="action-button" :to="{ name: 'GamePage' }">
                {{ $t('homePage.play') }}
            </router-link>
            <button v-else class="action-button" @click="redirectToLogin">
                {{ $t('homePage.joinUs') }}
            </button>
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
import { mapActions, mapGetters } from "vuex";

export default defineComponent ({
    name: "HomePage",
    computed: {
        ...mapGetters('auth', [
            'loggedIn'
        ])
    },
    methods: {
        ...mapActions('auth', [
            'redirectToLogin'
        ]),
        localeIsFrench() {
            return this.$i18n.locale.split('-')[0] === 'fr';
        }
    }
});
</script>

<style lang="scss" scoped>

.homepage-container {
    align-items: center;

    & > section {
        flex-direction: row;
        max-width: 80%;
        align-items: center;
    }

    .trailer-container {
        margin-top: 1em;

        video {
            border: 1px solid #26378c;
            box-shadow: 0px 0px 3px 3px rgba(0,0,0,0.5);
        }
    }

    p {
        text-align: center;
        font-size: 1.2em;
        line-height: 1.4;
        margin-top: 0;
    }

    #play-container {
        margin-bottom: 0;
        padding: 1.6em;
        align-items: center;
    }

    &::v-deep(em) {
        color: #01c3df;
        font-size: 1.3em;
        font-style: normal;
        font-weight: bold;
    }
}

.decorative {
    align-items: center;
    justify-content: space-evenly;

    img {
        width: fit-content;
        height: fit-content;
    }

    .daedalus { margin-bottom: -7em; }
}

button.start {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0.3em 0;
    height: 40px;
    color: white;
    font-size: 2.2em;
    font-weight: 700;
    letter-spacing: .03em;
    text-decoration: none;
    font-variant: small-caps;
    text-align: center;
    background: transparent url('~@/assets/images/big-button-center.png') center repeat-x;
    text-shadow: 0 0 5px black, 0 1px 2px black;

    transition: all .15s;

    span { margin-bottom: 5px; }

    &::before, &::after {
        content:"";
        width: 35px;
        height: 100%;
        background: transparent url('~@/assets/images/big-button-side.png') center no-repeat;
    }

    &::before { transform: translateX(-35px) }
    &::after { transform: translateX(35px) scaleX(-1) }

    &:hover, &:focus, &:active { filter: brightness(1.2) saturate(80%); }
}

@media screen and (min-width: 950px) {


    .homepage-container {
        max-width: 1080px;
        margin: 0 auto
    }

    // TODO : display .daedalus-container and .award-container on desktop

}

</style>
