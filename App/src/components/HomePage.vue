<template>
    <div class="homepage-container">
        <section class="decorative">
            <img class="daedalus" src="@/assets/images/daedalus_home.png" alt="Daedalus" />
            <img
                class="award"
                v-if="localeIsFrench()"
                src="@/assets/images/eigd_fr.png"
                alt="Award" />
            <img
                class="award"
                v-else
                src="@/assets/images/eigd_en.png"
                alt="Award" />
        </section>
        <section class="trailer-container">
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
        <section class="incentive">
            <div class="box-container" id="play-container">
                <p v-html="$t('homePage.synopsis')" />
                <router-link v-if="loggedIn" class="start" :to="{ name: 'GamePage' }">
                    {{ $t('homePage.play') }}
                </router-link>
                <button v-else class="start" @click="redirectToLogin">
                    {{ $t('homePage.joinUs') }}
                </button>
            </div>
            <!-- CARROUSEL TEMPLATE
            <div
                class="box-container"
                id="carrousel-container"
                @mouseenter.stop="toggleTimer = false"
                @mouseleave.stop="toggleTimer = true"
            >
                <h3>18 personnages, prêts à chasser le Mush avec vous !</h3>
                <transition mode="out-in">
                    <div class="slide" :key="slide">
                        <div class="avatar" :class="chars[slide].id">
                            <img :src="chars[slide].portrait" :alt="chars[slide].name" />
                        </div>
                        <div class="character-description">
                            <h4>{{ chars[slide].name }}</h4>
                            <p>{{ chars[slide].descr }}</p>
                        </div>
                    </div>
                </transition>
                <div class="arrows">
                    <img class="next" src="@/assets/images/blue-arrow.png" @click="move(-1)">
                    <img class="previous" src="@/assets/images/blue-arrow.png" @click="move(1)">
                </div>
            </div>
            -->
        </section>
        <section class="newsgroup">
            <h1>{{ $t('homePage.latestNews') }}</h1>
            <NewsItem class="news" :news="news" @click="$router.push('news')"/>
            <router-link class="more" :to="{ name: 'NewsPage' }">{{ $t('homePage.seeAllNews') }}</router-link>
        </section>
        <section class="medias">
            <!-- CARROUSEL TEMPLATE
            <h3>Ils n'ont pas trouvé ça mush :</h3>
            <div class="reviews">
                <div>
                    <a href="#">
                        <img src="@/assets/images/medias/jeuxvideo.png">
                    </a>
                    <p>"Avec un bon groupe, le jeu procure une expérience jouissive où la suspicion, les mensonges et la paranoïa règnent en maîtres. "</p>
                    <span class="score">15/20</span>
                </div>
                <div>
                    <a href="#">
                        <img src="@/assets/images/medias/gamesphere.png">
                    </a>
                    <p>"Un vrai jeu communautaire où l’on partage une partie de son quotidien avec quinze personnes pour quelques jours"</p>
                    <span class="score">91%</span>
                </div>
                <div>
                    <a href="#">
                        <img src="@/assets/images/medias/gaminfo.png">
                    </a>
                    <p>"N’oubliez pas que le Mush est toujours là, à rôder dans les couloirs du Daedalus pour contaminer tout l’équipage !"</p>
                </div>
                <div>
                    <img src="@/assets/images/medias/jeuxcapt.png">
                    <p>[...] même si nous avons terminé une partie, nous aurons toujours envie d'en recommencer une nouvelle, car rien ne se déroulera comme la précédente."</p>
                </div>
                <div>
                    <img src="@/assets/images/medias/logo_cpc.png">
                    <p>"Mush est l’enfant contre-nature qu’aurait pu avoir un Cylon s’il s’était tapé un des loups-garous de Thiercelieux."</p>
                </div>
                <div>
                    <a href="#">
                        <img src="@/assets/images/medias/gamalive.png">
                    </a>
                    <p>"Un mélange subtil de social, stratégie, gestion et bien d'autres encore. Très addictif une fois qu'on est lancé."</p>
                </div>
                <div>
                    <a href="#">
                        <img src="@/assets/images/medias/gamer-news.png">
                    </a>
                    <p>"Si vous êtes adepte de jeux de rôles, si vous aimez jouer en équipe et vous marrer en groupe, Mush est fait pour vous."</p>
                    <span class="score">7.2/10</span>
                </div>
            </div>
            -->
            <div class="weblinks">
                <h3>{{ $t('homePage.followUs') }}</h3>
                <a href="https://discord.gg/ERc3svy"><img src="@/assets/images/medias/discord.png"> Discord</a>
                <a href="https://eternaltwin.org/"><img src="@/assets/images/medias/etwin.png"> EternalTwin</a>
                <a href="https://gitlab.com/eternaltwin/mush/mush"><img src="@/assets/images/medias/gitlab.png"> GitLab</a>
                <img class="pegi" src="@/assets/images/medias/pegi.png">
            </div>
        </section>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { mapActions, mapGetters } from "vuex";
import NewsItem from "./NewsItem.vue";
import NewsService from "@/services/news.service";
import { News } from "@/entities/News";

export default defineComponent ({
    name: "HomePage",
    components: {
        NewsItem
    },
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
        },
        async getMostRecentNews() {
            const allNews = await NewsService.getLastPinnedNews().then((news: News[]) => {
                return news;
            });

            return allNews[allNews.length - 1];
        },
        displayNews(news: News) {
            news.hidden = false;
            return news;
        },
        fillEmptyNews(news: News) {
            news.englishTitle = news.englishTitle || news.frenchTitle;
            news.englishContent = news.englishContent || news.frenchContent;
            news.spanishContent = news.spanishContent || news.frenchContent;
            news.spanishTitle = news.spanishTitle || news.frenchTitle;

            return news;
        }
    },
    data: function() {
        return {
            news: new News(),
            slide: 0,
            timerDelay: 3000,
            toggleTimer: true,
            chars: [
                {
                    id: 'andie',
                    name: 'Andie Graham',
                    descr: 'Fayot de la fédération.',
                    portrait: 'src/assets/images/char/portrait/andie_graham_portrait.png'
                },
                {
                    id: 'chao',
                    name: 'Wang Chao',
                    descr: 'Chef de la sécurité du Daedalus.',
                    portrait: 'src/assets/images/char/portrait/Wang_chao_portrait.png'
                },
                {
                    id: 'chun',
                    name: 'Zhong Chun',
                    descr: 'Dernier espoir de l\'Humanité',
                    portrait: 'src/assets/images/char/portrait/Zhong_chun_portrait.png'
                },
                {
                    id: 'derek',
                    name: 'Derek Hogan',
                    descr: 'Héros malgré lui.',
                    portrait: 'src/assets/images/char/portrait/derek_hogan_portrait.png'
                },
                {
                    id: 'eleesha',
                    name: 'Eleesha Williams',
                    descr: 'Investigatrice déchue de premier plan.',
                    portrait: 'src/assets/images/char/portrait/Eleesha_williams_portrait.png'
                }
            ]
        };
    },
    mounted: function() {
        this.getMostRecentNews().then((news: News) => {
            this.news = news;
            this.news = this.displayNews(this.news);
            this.news = this.fillEmptyNews(this.news);
        });
        this.autoPlay();
    }
});
</script>

<style lang="scss" scoped>

.homepage-container {
    align-items: center;
    max-width: $breakpoint-desktop-l;
    margin: 0 auto;

    & > section {
        gap: 1.6em;
        max-width: 80%;
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
            width: 100%;
        }
    }

    p {
        text-align: center;
        font-size: 1.15em;
        line-height: 1.5em;
        margin-top: 0;
    }

    #play-container {
        align-items: center;
    }

    :deep(em) {
        color: #01c3df;
        font-size: 1.3em;
        line-height: 0.8em;
        font-style: normal;
        font-weight: bold;
    }
}

.decorative {
    flex-direction: row;
    align-items: center;
    justify-content: space-evenly;
    gap: 30%;

    img {
        width: fit-content;
        height: fit-content;
    }

    .daedalus { margin-bottom: -7em; }
}

.incentive { flex-direction: row; }

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
        flex: 1;
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

.newsgroup {
    margin-top: 2.8em;

    a.more {
        align-self: flex-end;
        padding: 0 0.3em;
        color: $green;
        font-size: 1.2em;
    }
}

.medias {
    margin-top: 4em;

    .reviews {
        columns: 4 auto;
        columns: 180px auto;
        display: block;
        gap: 1.5em 2.2em;

        div {
            display: inline-block;
            margin-bottom: 1.6em;
            padding: 1.1em;
            flex-direction: column;
            background-color: rgba(23,68,142,0.4);
            border-radius: 5px;
        }

        a, img {
            display: block;
            width: fit-content;
            max-width: 100%;
            margin: 0 auto;
        }

        p, span {
            font-size: 0.92em;
            letter-spacing: 0.02em;
            color: #b1c5f9;
            text-align: left;
            margin-top: 1.2em;
        }

        span {
            float: right;
            font-weight: bold;
            margin-top: 0.25em;
        }
    }

    h3 {
        font-weight: normal;
        font-style: italic;
        color:#88a6fe;
        text-shadow: 0 0 4px $deepBlue;
        margin: 0.2em 0;
    }

    p { margin: 0; }
}

.weblinks {
    align-self: center;
    flex-direction: row;
    align-items: center;
    gap: 0.5em;
    font-size: 1.1em;

    a {
        padding: 0.1em 0.3em;
        color: $deepBlue;
        text-decoration: none;
        background-color: #eeeeee;
        border: 1px solid #eeeeee;
        border-radius: 4px;

        transition: background-color 0.3s;

        img { vertical-align: middle; }

        &:hover, &:focus { background-color: lighten($greyBlue, 35%); }
    }

    .pegi { margin: -0.2em 0.6em 0; }
}

.v-enter-active, .v-leave-active { transition: opacity 0.3s ease; }


.v-enter-from, .v-leave-to { opacity: 0; }




@media screen and (max-width: $breakpoint-desktop-m) {


    .homepage-container > section { max-width: 92%; }

    .decorative { gap: 8%; }

    .incentive {
        flex-direction: column;
        gap: 0;
    }

    .medias .reviews {
        gap: 1.2em 1.4em;
    }

    .weblinks {
        flex-direction: column;
        gap: 0.8em;

        .pegi { margin-top: 0.8em; }
    }

    // TODO : adjust pagination on mobile
}

@media screen and (max-width: $breakpoint-mobile-l) {

    .decorative { flex-direction: column-reverse; }

    .medias .reviews { column-count: 1; }
}

</style>
