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
        </section>
        <section>
            <div class="box-container" id="play-container">
                <p v-html="$t('homePage.synopsis')" />
                <router-link v-if="loggedIn" class="start" :to="{ name: 'GamePage' }">
                    {{ $t('homePage.play') }}
                </router-link>
                <button v-else class="start" @click="redirectToLogin">
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
        </section>
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
        },
        move(amount: any) {
            let newActive = this.slide + amount;
            if (newActive >= this.chars.length) newActive = 0;
            if (newActive < 0) newActive = this.chars.length-1;
            this.slide = newActive;
        },
        autoPlay() {
            setInterval(() => {
                if(this.toggleTimer) this.move(1);
            }, this.timerDelay);
        }
    },
    data: function() {
        return {
            slide: 0,
            timerDelay: 3000,
            toggleTimer: true,
            chars: [
                {
                    id: 'andie',
                    name: 'Andie Graham',
                    descr: 'Fayot de la fédération.',
                    portrait: require('@/assets/images/char/portrait/andie_graham_portrait.jpg')
                },
                {
                    id: 'chao',
                    name: 'Wang Chao',
                    descr: 'Chef de la sécurité du Daedalus.',
                    portrait: require('@/assets/images/char/portrait/Wang_chao_portrait.jpg')
                },
                {
                    id: 'chun',
                    name: 'Zhong Chun',
                    descr: 'Dernier espoir de l\'Humanité',
                    portrait: require('@/assets/images/char/portrait/Zhong_chun_portrait.jpg')
                },
                {
                    id: 'derek',
                    name: 'Derek Hogan',
                    descr: 'Héros malgré lui.',
                    portrait: require('@/assets/images/char/portrait/derek_hogan_portrait.jpg')
                },
                {
                    id: 'eleesha',
                    name: 'Eleesha Williams',
                    descr: 'Investigatrice déchue de premier plan.',
                    portrait: require('@/assets/images/char/portrait/Eleesha_williams_portrait.jpg')
                }
            ]
        };
    },
    mounted: function() {
        console.log(`the component is now mounted.`);
        this.autoPlay();
    }
});
</script>

<style lang="scss" scoped>

.homepage-container {
    align-items: center;

    & > section {
        flex-direction: row;
        gap: 1.6em;
    }

    .box-container {
        margin-bottom: 0;
        padding: 1.6em;
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
        font-size: 1.15em;
        line-height: 1.4;
        margin-top: 0;
    }

    #play-container {
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
    align-self: stretch;
    align-items: center;
    justify-content: space-evenly;

    img {
        width: fit-content;
        height: fit-content;
    }

    .daedalus { margin-bottom: -7em; }
}

.start {
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

@each $crewmate, $face-position-x, $face-position-y in $face-position { // adjust the image position in the crewmate avatar div
    $translate-x : (50% - $face-position-x);
    $translate-y : (50% - $face-position-y);
    .#{$crewmate} img {
        transform: translate($translate-x, $translate-y);
    }
}

#carrousel-container {

    h3 {
        font-size: 1.15em;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    img  {
        width: fit-content;
        height: fit-content;
    }

    .carrousel {
        flex-direction: row;
        justify-content: center;
        align-items: center;
        overflow: hidden;
}

    .slide {
        flex-direction: row;
    }

    .avatar {
        align-items: center;
        justify-content: center;
        overflow: hidden;
        width: 160px;
        height: 100px;
        margin-right: 1.2em;
        border: 1px solid #26378c;
        box-shadow: 0px 0px 3px 3px rgba(0,0,0,0.5);
    }

    .character-description {
        width: 40%;
        min-width: 180px;
    }

    h4 {
        width: 100%;
        font-size: 1.6em;
        font-style: italic;
        border-bottom: 2px solid #26378C;
        padding-bottom: 2px;
        margin: 0 0 0.3em;
    }

    p {
        margin-left: 1em;
        text-align: left;
    }

    .arrows {
        flex-direction: row;
        justify-content: space-between;
        margin: 1.2em 0 0;

        img {
            width: fit-content;
            cursor: pointer;

            &.previous { transform: scaleX(-1); }

            &:hover, &:focus, &:active { filter: brightness(10) contrast(10) grayscale(1); }
        }
    }
}


.v-enter-active,
.v-leave-active {
  transition: opacity 0.3s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}




@media screen and (min-width: 950px) {


  .homepage-container {
      max-width: 1080px;
      margin: 0 auto
  }

  // TODO : display .daedalus-container and .award-container on desktop
}

</style>
