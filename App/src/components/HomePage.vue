<template>
    <div class="homepage-container">
        <div class="daedalus-container">
            <img src="@/assets/images/daedalus_home.png" alt="Daedalus" />
        </div>
        <div class="trailer-container">
            <video v-if="localeIsFrench()"
                   controls
                   ref="trailer"
                   preload="metadata">
                <source src="@/assets/videos/trailer_fr.mp4#t=1" type="video/mp4"/>
                Désolé, votre navigateur ne supporte pas les vidéos intégrées.
            </video>
            <video v-else
                   controls
                   ref="trailer"
                   preload="metadata">
                <source src="@/assets/videos/trailer_en.mp4#t=1" type="video/mp4"/>
                Sorry, your browser doesn't support embedded videos.
            </video>
        </div>
        <div class="award-container">
            <img v-if="localeIsFrench()" src="@/assets/images/eigd_fr.png" alt="Award" />
            <img v-else src="@/assets/images/eigd_en.png" alt="Award" />
        </div>
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
            'loggedIn',
        ])
    },
    methods: {
        ...mapActions('auth', [
            'redirectToLogin',
        ]),
        localeIsFrench() {
            return this.$i18n.locale.split('-')[0] === 'fr';
        },
    }
});
</script>

<style lang="scss" scoped>

.homepage-container {
    display: grid;
    grid-template-columns: 33% 1fr 33%;
    max-width: 1080px;
    margin: -6% 10% 0 10%;
    
    .daedalus-container {
        grid-column: 1;
        width: 33%;
        margin-top: 20%;

        img {
            background: none;
        }
    }
    .trailer-container {
        grid-column: 2 / 4;
        margin-top: 20%;

        video {
            border: 1px solid #26378C;
            box-shadow: 0px 0px 2px 2px rgba(0,0,0,0.5);
        }
    }

    .award-container {
        grid-column: 4;
        margin-top: 70%;
        padding-left: 25%;
        
        img {
            background: none;
        }
    }

    #play-container {
        grid-column: 2 / 4;
        grid-row: 2;
        margin: -10% auto 0 auto;
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
