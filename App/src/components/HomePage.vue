<template>
    <div class="homepage-container">
        <section class="decorative">
            <img class="daedalus" :src="getImgUrl('daedalus_home.png')" alt="Daedalus" />
            <img
                class="award"
                v-if="localeIsFrench()"
                :src="getImgUrl('eigd_fr.png')"
                alt="Award" />
            <img
                class="award"
                v-else
                :src="getImgUrl('eigd_en.png')"
                alt="Award" />
        </section>
        <section class="trailer-container">
            <RuffleContent
                v-if="ruffleSupported"
                :swf-url="getSwfUrl(`trailer_${locale}.swf`)"
                :width="ruffleWidth"
                :height="ruffleHeight"
            />
        </section>
        <section class="incentive">
            <div class="box-container play-container">
                <p v-html="$t('homePage.synopsis')" />
                <router-link v-if="loggedIn" class="start" :to="{ name: 'GamePage' }">
                    {{ $t('homePage.play') }}
                </router-link>
                <button v-else class="start" @click="redirectToLogin">
                    {{ $t('homePage.joinUs') }}
                </button>
            </div>
        </section>
        <section v-if="newsAvailable" class="newsgroup">
            <h1>{{ $t('homePage.latestNews') }}</h1>
            <NewsItem class="news" :news="news" @click="$router.push('news')"/>
            <router-link class="more" :to="{ name: 'NewsPage' }">{{ $t('homePage.seeAllNews') }}</router-link>
        </section>
        <section class="medias">
            <div class="weblinks">
                <h3>{{ $t('homePage.followUs') }}</h3>
                <a href="https://discord.gg/ERc3svy"><img :src="getImgUrl('medias/discord.png')"> Discord</a>
                <a href="https://eternaltwin.org/"><img :src="getImgUrl('medias/etwin.png')"> EternalTwin</a>
                <a href="https://gitlab.com/eternaltwin/mush/mush"><img :src="getImgUrl('medias/gitlab.png')"> GitLab</a>
                <img class="pegi" :src="getImgUrl('medias/pegi.png')">
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
import { getImgUrl } from "@/utils/getImgUrl";
import { getSwfUrl } from "@/utils/getSwfUrl";
import RuffleContent from "./RuffleContent.vue";

export default defineComponent ({
    name: "HomePage",
    components: {
        NewsItem,
        RuffleContent
    },
    computed: {
        ...mapGetters({
            'loggedIn': 'auth/loggedIn',
            'locale': 'locale/currentLocale'
        }),
        ruffleSupported() {
            if (typeof WebAssembly !== 'object') {
                return false;
            }
            return typeof window.RufflePlayer !== 'undefined';
        },
        ruffleWidth() {
            if (window.innerWidth < 425) {  // $breakpoint-mobile-l
                return 350;
            } else if (window.innerWidth < 660) {  // $breakpoint-desktop-s
                return 425;
            }
            return 555;
        },
        ruffleHeight() {
            if (window.innerWidth < 425) {  // $breakpoint-mobile-l
                return 195;
            } else if (window.innerWidth < 660) {  // $breakpoint-desktop-s
                return 237;
            }
            return 310;
        }
    },
    methods: {
        ...mapActions('auth', [
            'redirectToLogin'
        ]),
        getImgUrl,
        getSwfUrl,
        localeIsFrench() {
            return this.locale === 'fr';
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
            newsAvailable: false
        };
    },
    mounted: function() {
        this.getMostRecentNews().then((news: News) => {
            if (news) {
                this.newsAvailable = true;
                this.news = news;
                this.news = this.displayNews(this.news);
                this.news = this.fillEmptyNews(this.news);
            }
        });
    }
});
</script>

<style lang="scss" scoped>
@use "sass:color";

.homepage-container {
    align-items: center;
    max-width: $breakpoint-desktop-m;
    margin: 0 auto;

    p {
        text-align: center;
        font-size: 1.15em;
        line-height: 1.5em;
        margin-top: 0;
    }

    :deep(em) {
        color: #01c3df;
        font-size: 1.3em;
        line-height: 0.8em;
        font-style: normal;
        font-weight: bold;
    }

    .trailer-container {
        margin-top: 1em;
        border: 1px solid #26378c;
        box-shadow: 0 0 3px 3px rgba(0, 0, 0, 0.5);
    }

    .decorative {
        flex-direction: row;
        align-items: center;
        justify-content: space-evenly;
        gap: 1.6em;
        max-width: 80%;

        @media screen and (max-width: $breakpoint-desktop-m) {
            max-width: 92%;
        }

        @media screen and (max-width: $breakpoint-mobile-l) {
            flex-direction: column-reverse;
        }

        img {
            width: fit-content;
            height: fit-content;
        }

        .daedalus {
            margin-bottom: -7em;
        }
    }

    .incentive {
        flex-direction: row;
        gap: 1.6em;
        max-width: 80%;

        @media screen and (max-width: $breakpoint-desktop-m) {
            flex-direction: column;
            gap: 0;
            max-width: 92%;
        }

        .play-container {
            margin-bottom: 0;
            padding: 1.6em;
            align-items: center;

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
                background: transparent url('/src/assets/images/big-button-center.png') center repeat-x;
                text-shadow: 0 0 5px black, 0 1px 2px black;
                transition: all .15s;

                &::before, &::after {
                    content:"";
                    width: 36px;
                    height: 100%;
                    background: transparent url('/src/assets/images/big-button-side.png') center no-repeat;
                }

                &::before {
                    transform: translateX(-35px);
                }

                &::after {
                    transform: translateX(35px) scaleX(-1);
                }

                &:hover, &:focus, &:active {
                    filter: brightness(1.2) saturate(80%);
                }
            }
        }
    }

    .newsgroup {
        margin-top: 2.8em;
        width: 100%;

        a.more {
            align-self: flex-end;
            padding: 0 0.3em;
            color: $green;
            font-size: 1.2em;
        }
    }

    .medias {
        margin-top: 4em;

        h3 {
            font-weight: normal;
            font-style: italic;
            color: #88a6fe;
            text-shadow: 0 0 4px $deepBlue;
            margin: 0.2em 0;
        }

        .weblinks {
            align-self: center;
            flex-direction: row;
            align-items: center;
            gap: 0.5em;
            font-size: 1.1em;

            @media screen and (max-width: $breakpoint-desktop-m) {
                flex-direction: column;
                gap: 0.8em;
            }

            a {
                padding: 0.1em 0.3em;
                color: $deepBlue;
                text-decoration: none;
                background-color: #eeeeee;
                border: 1px solid #eeeeee;
                border-radius: 4px;
                transition: background-color 0.3s;

                img {
                    vertical-align: middle;
                }

                &:hover, &:focus {
                    background-color: color.adjust($greyBlue, $lightness: 35%);
                }
            }

            .pegi {
                margin: -0.2em 0.6em 0;

                @media screen and (max-width: $breakpoint-desktop-m) {
                        margin-top: 0.8em;
                }
            }
        }
    }
}
</style>
