<template>
    <div class="homepage">
        <section class="hero">
            <div class="hero-inner">
                <div class="hero-intro">
                    <div class="hero-ship">
                        <img
                            class="daedalus"
                            :src="getImgUrl('daedalus_home.png')"
                            alt=""
                            width="216"
                            height="311"
                            aria-hidden="true" />
                    </div>

                    <h1 class="hero-title">
                        {{ $t('homePage.hero.title') }}
                        <span class="hero-title-twist">{{ $t('homePage.hero.titleTwist') }}</span>
                    </h1>

                    <img
                        class="hero-award"
                        :src="getImgUrl(localeIsFrench() ? 'eigd_fr.png' : 'eigd_en.png')"
                        :alt="$t('homePage.hero.awardAlt')"
                        width="176"
                        height="130" />
                </div>

                <p class="hero-subtitle" v-html="heroSubtitle" />

                <div class="hero-actions">
                    <router-link v-if="loggedIn" class="start" :to="{ name: 'GamePage' }">
                        {{ $t('homePage.play') }}
                    </router-link>
                    <button
v-else
class="start"
type="button"
@click="redirectToLogin">
                        {{ $t('homePage.joinUs') }}
                    </button>
                </div>

                <p class="hero-note" v-html="$t('homePage.hero.note')" />

            </div>
        </section>
        <section v-if="loggedIn && newsAvailable" class="newsgroup">
            <h2 class="section-title">{{ $t('homePage.latestNews') }}</h2>
            <NewsItem class="news" :news="news" @click="$router.push('news')"/>
            <router-link class="more" :to="{ name: 'NewsPage' }">{{ $t('homePage.seeAllNews') }}</router-link>
        </section>
        <section v-if="!loggedIn" id="life-aboard" class="life-aboard">
            <h2 class="section-title">{{ $t('homePage.persistence.title') }}</h2>

            <div class="persistence">
            <ol class="logbook">
                <li v-for="beat in logbook" :key="beat.time">
                    <span class="logbook-time">{{ beat.time }}</span>
                    <span class="logbook-entry">{{ beat.text }}</span>
                </li>
            </ol>

            <div class="prose">
                <p>{{ $t('homePage.persistence.body1') }}</p>
                <p>{{ $t('homePage.persistence.body2') }}</p>
            </div>
        </div>
        <aside class="gameplay-preview" :aria-label="$t('homePage.persistence.galleryLabel')">
            <ul class="gameplay-gallery">
                <li v-for="screenshot in screenshots" :key="screenshot.key">
                    <button
                        type="button"
                        class="screenshot-thumbnail"
                        :aria-label="$t('homePage.persistence.openScreenshot', { screenshot: $t(`homePage.persistence.${screenshot.key}Caption`) })"
                        @click="openScreenshot(screenshot)">
                        <img
                            :src="screenshot.src"
                            :alt="$t(`homePage.persistence.${screenshot.key}Alt`)"
                            width="1080"
                            height="552"
                            loading="lazy" />
                    </button>
                </li>
            </ul>

            <dialog
                ref="screenshotDialog"
                class="screenshot-lightbox"
                :aria-label="activeScreenshot ? $t(`homePage.persistence.${activeScreenshot.key}Caption`) : undefined"
                @click.self="closeScreenshot"
                @close="activeScreenshot = null">
                <button
                    type="button"
                    class="screenshot-close"
                    :aria-label="$t('homePage.persistence.closeScreenshot')"
                    @click="closeScreenshot">
                    <img
                        :src="getImgUrl('comms/close.png')"
                        alt=""
                        width="16"
                        height="16" />
                </button>
                <figure v-if="activeScreenshot">
                    <img
                        :src="activeScreenshot.src"
                        :alt="$t(`homePage.persistence.${activeScreenshot.key}Alt`)"
                        width="1080"
                        height="552" />
                    <figcaption>{{ $t(`homePage.persistence.${activeScreenshot.key}Caption`) }}</figcaption>
                </figure>
            </dialog>
        </aside>
        </section>
        <section v-if="!loggedIn" class="ship">
            <h2 class="section-title">{{ $t('homePage.ship.title') }}</h2>

            <div class="ship-columns">
                <div v-for="pillar in shipPillars" :key="pillar.key" class="ship-pillar">
                    <h3>{{ $t(`homePage.ship.${pillar.key}Title`) }}</h3>
                    <p>{{ $t(`homePage.ship.${pillar.key}Text`) }}</p>
                </div>
            </div>
        </section>
        <section v-if="!loggedIn" class="crew">
            <h2 class="section-title">{{ $t('homePage.crew.title') }}</h2>

            <p class="crew-subtitle">
                {{ $t('homePage.crew.subtitle') }}
                {{ $t('homePage.crew.more') }}
            </p>

            <ul class="crew-strip">
                <li v-for="character in playableCharacters" :key="character.keyName">
                    <button
                        type="button"
                        class="crew-portrait"
                        :class="[character.keyName, { selected: character.keyName === selectedCharacter }]"
                        :aria-pressed="character.keyName === selectedCharacter"
                        :title="character.completeName"
                        @click="selectCharacter(character.keyName)">
                        <img
                            :src="character.portrait"
                            :alt="$t('homePage.crew.selectCharacter', { character: character.completeName })"
                            width="210"
                            height="300"
                            loading="lazy" />
                    </button>
                </li>
            </ul>
            <article class="crew-file">
                <div class="crew-file-portrait">
                    <img
                        :class="selectedCharacter"
                        :src="selectedCharacterInfos.portrait"
                        alt=""
                        aria-hidden="true"
                        width="210"
                        height="300" />
                </div>

                <div class="crew-file-body">
                    <h3>{{ selectedCharacterInfos.completeName }}</h3>
                    <p class="crew-file-tagline">{{ selectedCharacterDescription }}</p>
                    <p class="crew-file-description">{{ selectedCharacterAbstract }}</p>
                    <router-link
                        class="crew-file-link"
                        :to="{ name: 'CharacterBiographyView', params: { characterName: selectedCharacter } }">
                        {{ $t('homePage.crew.readBiography') }}
                    </router-link>
                </div>
            </article>
        </section>
        <section v-if="counters" class="counters">
            <h2 class="section-title">
                <img
                    class="section-title-icon"
                    :src="getImgUrl('ui_icons/action_points/pa_core.png')"
                    alt=""
                    aria-hidden="true" />
                {{ $t('homePage.counters.title') }}
            </h2>

            <dl class="counters-grid">
                <div v-for="counter in counterEntries" :key="counter.key" class="counter">
                    <dt>{{ formatCount(counter.value) }}</dt>
                    <dd>{{ $t(`homePage.counters.${counter.key}`) }}</dd>
                </div>
            </dl>
        </section>
        <section v-if="voices.length" class="voices">
            <h2 class="section-title">{{ $t('homePage.voices.title') }}</h2>
            <div class="voices-grid">
                <figure v-for="voice in voices" :key="voice.author" class="voice">
                    <blockquote>{{ voice.text }}</blockquote>
                    <figcaption>{{ voice.author }}</figcaption>
                </figure>
            </div>
        </section>
        <section v-if="!loggedIn" class="final-cta">
            <h2 class="final-cta-title">{{ $t('homePage.finalCta.title') }}</h2>
            <p class="final-cta-subtitle">{{ $t('homePage.finalCta.subtitle') }}</p>

            <router-link v-if="loggedIn" class="start" :to="{ name: 'GamePage' }">
                {{ $t('homePage.play') }}
            </router-link>
            <button
v-else
class="start"
type="button"
@click="redirectToLogin">
                {{ $t('homePage.joinUs') }}
            </button>

        </section>
        <section v-if="!loggedIn" class="medias">
            <div class="weblinks">
                <h3>{{ $t('homePage.followUs') }}</h3>
                <a href="https://discord.gg/ERc3svy"><img :src="getImgUrl('medias/discord.png')" alt="" aria-hidden="true"> Discord</a>
                <a href="https://eternaltwin.org/"><img :src="getImgUrl('medias/etwin.png')" alt="" aria-hidden="true"> EternalTwin</a>
                <a href="https://gitlab.com/eternaltwin/mush/mush"><img :src="getImgUrl('medias/gitlab.png')" alt="" aria-hidden="true"> GitLab</a>
                <img class="pegi" :src="getImgUrl('medias/pegi.png')" :alt="$t('homePage.pegiAlt')">
            </div>
        </section>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { mapActions, mapGetters } from "vuex";
import NewsItem from "./NewsItem.vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { characterEnum, CharacterEnum, CharacterInfos, getPlayableCharacters } from "@/enums/character";
const DEFAULT_CHARACTER = CharacterEnum.STEPHEN;

const SHIP_PILLARS = [
    { key: 'investigate' },
    { key: 'survive' },
    { key: 'roleplay' }
];

interface GameplayScreenshot {
    key: string;
    src: string;
}

const SCREENSHOTS: GameplayScreenshot[] = [
    { key: 'participate', src: getImgUrl('homepage/participate.avif') },
    { key: 'storage', src: getImgUrl('homepage/storage.avif') },
    { key: 'channels', src: getImgUrl('homepage/channels.avif') }
];

export default defineComponent ({
    name: "HomePage",
    components: {
        NewsItem
    },
    computed: {
        ...mapGetters({
            'loggedIn': 'auth/loggedIn',
            'locale': 'locale/currentLocale',
            'characterDetails': 'homePage/characterDetails',
            'counters': 'homePage/counters',
            'news': 'homePage/news'
        }),
        newsAvailable(): boolean {
            return this.news !== null;
        },
        heroSubtitle(): string {
            const icon = `<img class="inline-icon" src="${getImgUrl('status/berzerk.png')}" alt="" aria-hidden="true">`;

            return this.$t('homePage.hero.subtitle', { mushIcon: icon });
        },
        selectedCharacterDescription(): string {
            return this.characterDetails?.description ?? '';
        },
        selectedCharacterAbstract(): string {
            return this.characterDetails?.abstract?.replace(/^\s*\*\*\*[^*]*\*\*\*\s*/, '').trim() ?? '';
        },
        logbook(): Array<{ time: string, text: string }> {
            return [1, 2, 3, 4].map((slot) => ({
                time: this.$t(`homePage.persistence.time${slot}`),
                text: this.$t(`homePage.persistence.beat${slot}`)
            }));
        },
        playableCharacters(): CharacterInfos[] {
            return getPlayableCharacters();
        },
        selectedCharacterInfos(): CharacterInfos {
            return characterEnum[this.selectedCharacter];
        },
        counterEntries(): Array<{ key: string, value: number }> {
            if (!this.counters) {
                return [];
            }
            return [
                { key: 'daedaluses', value: this.counters.daedalusesInGame },
                { key: 'messages', value: this.counters.messagesSent },
                { key: 'mush', value: this.counters.mushKilled },
                { key: 'expeditions', value: this.counters.expeditionsStarted }
            ];
        },
        voices(): Array<{ text: string, author: string }> {
            return ['one', 'two', 'three']
                .map((slot) => ({
                    text: this.$t(`homePage.voices.${slot}.text`),
                    author: this.$t(`homePage.voices.${slot}.author`)
                }))
                .filter((voice) => voice.text.length > 0);
        }
    },
    watch: {
        async locale(language: string) {
            await this.loadCharacterDetails({ characterName: this.selectedCharacter, language });
        }
    },
    methods: {
        ...mapActions('auth', [
            'redirectToLogin'
        ]),
        ...mapActions('homePage', {
            loadHomePage: 'load',
            loadCharacterDetails: 'loadCharacterDetails'
        }),
        getImgUrl,
        localeIsFrench() {
            return this.locale === 'fr';
        },
        formatCount(value: number): string {
            return value.toLocaleString(this.locale);
        },
        async selectCharacter(characterName: string) {
            this.selectedCharacter = characterName;
            await this.loadCharacterDetails({ characterName, language: this.locale });
        },
        openScreenshot(screenshot: GameplayScreenshot) {
            this.activeScreenshot = screenshot;
            this.$nextTick(() => (this.$refs.screenshotDialog as HTMLDialogElement).showModal());
        },
        closeScreenshot() {
            (this.$refs.screenshotDialog as HTMLDialogElement).close();
        }
    },
    data: function() {
        return {
            activeScreenshot: null as GameplayScreenshot | null,
            screenshots: SCREENSHOTS,
            selectedCharacter: DEFAULT_CHARACTER as string,
            shipPillars: SHIP_PILLARS
        };
    },
    mounted: function() {
        this.loadHomePage({ characterName: this.selectedCharacter, language: this.locale });
    }
});
</script>

<style lang="scss" scoped>
@use "sass:color";
.homepage {
    width: 100%;
    align-items: center;

    ::selection {
        background: $cyan;
        color: $deepBlue;
    }

    section {
        width: 100%;
        max-width: $breakpoint-desktop-l;
        margin: 0 auto;
        padding-left: 20px;
        padding-right: 20px;
        align-items: center;
    }

    :deep(em) {
        color: $cyan;
        font-style: normal;
        font-weight: 700;
    }

    :deep(.inline-icon) {
        width: 1.1em;
        height: 1.1em;
        vertical-align: -0.2em;
        image-rendering: pixelated;
    }

    a:focus-visible,
    button:focus-visible {
        outline: 2px solid $lightOrange;
        outline-offset: 3px;
    }
}
.section-title {
    position: relative;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 0.4em;
    margin: 0 0 1.4em;
    padding-top: 0.6em;
    font-size: clamp(1.35em, 3.4vw, 1.9em);
    font-weight: 700;
    letter-spacing: 0.01em;
    text-align: center;
    text-wrap: balance;
    color: #f0f6ff;
    text-shadow: 0 0 6px rgba($deepBlue, 0.9), 0 0 6px rgba($deepBlue, 0.9);

    .section-title-icon {
        flex-shrink: 0;
        width: 1em;
        height: 1em;
        image-rendering: pixelated;
    }

    &::before {
        content: "";
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 46px;
        height: 2px;
        background: $cyan;
        box-shadow: 0 0 8px 1px rgba($cyan, 0.8);
    }
}
.prose {
    max-width: 62ch;
    align-self: center;

    p {
        margin: 0 0 1em;
        font-size: 1.05em;
        line-height: 1.65;
        color: #ccd6ff;
        text-shadow: 0 0 6px rgba($deepBlue, 0.8);

        &:last-child { margin-bottom: 0; }
    }
}
.hero {
    padding-top: 0.5em;
    padding-bottom: 4em;

    .hero-inner {
        align-items: center;
        width: 100%;
        max-width: 960px;
        text-align: center;
    }

    .hero-intro {
        display: grid;
        grid-template-columns: 1fr 170px 150px 1fr;
        grid-template-areas:
            ". ship award ."
            "title title title title";
        align-items: center;
        gap: 0 3.5em;
        width: 100%;
        margin-bottom: 1.2em;

        @media screen and (max-width: $breakpoint-desktop-s) {
            grid-template-columns: 1fr 1fr;
            grid-template-areas:
                "ship award"
                "title title";
            gap: 0 1em;
        }
    }

    .hero-ship {
        position: relative;
        grid-area: ship;
        align-items: center;
        justify-content: center;
        &::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 280px;
            height: 280px;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background: radial-gradient(
                circle,
                rgba(57, 101, 251, 0.35) 0%,
                rgba(15, 89, 171, 0.18) 42%,
                transparent 70%
            );
            pointer-events: none;
        }

        @media screen and (max-width: $breakpoint-mobile-l) {
            &::before { width: 240px; height: 240px; }
        }
    }

    .daedalus {
        position: relative;
        width: 170px;
        max-width: 52vw;
        height: auto;
        animation: daedalus-drift 9s cubic-bezier(0.25, 0, 0.15, 1) infinite both;
    }

    .hero-award {
        grid-area: award;
        justify-self: center;
        width: 150px;
        height: auto;
        filter: drop-shadow(0 0 10px rgba($deepBlue, 0.9));

        @media screen and (max-width: $breakpoint-mobile-l) {
            width: 108px;
        }
    }

    .hero-title {
        grid-area: title;
        margin: 0;
        font-family: $font-days-one;
        font-size: clamp(1.7rem, 4.6vw, 2.9rem);
        line-height: 1.15;
        letter-spacing: -0.01em;
        color: #ffffff;
        text-shadow: 0 0 12px rgba($deepBlue, 0.95), 0 2px 3px rgba(black, 0.6);
        text-wrap: balance;
    }
    .hero-title-twist {
        display: block;
        margin-top: 0.15em;
        color: $mushRed;
        text-shadow: 0 0 14px rgba($mushRed, 0.45), 0 2px 3px rgba(black, 0.6);
    }

    .hero-subtitle {
        max-width: 60ch;
        margin: 0 auto 1.8em;
        font-size: 1.1em;
        line-height: 1.65;
        color: #ccd6ff;
        text-shadow: 0 0 6px rgba($deepBlue, 0.8);
    }

    .hero-actions {
        flex-direction: row;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 1.4em;
    }

    .hero-note {
        max-width: 74ch;
        margin: 0 auto 1.8em;
        font-size: 0.98em;
        line-height: 1.6;
        color: #adb9ee;
        text-wrap: pretty;
    }

}
.start {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0.3em 36px;
    height: 40px;
    color: white;
    font-size: 2.2em;
    font-weight: 700;
    letter-spacing: .03em;
    line-height: 1.05;
    text-decoration: none;
    font-variant: small-caps;
    text-align: center;
    white-space: nowrap;
    background: transparent url('/src/assets/images/big-button-center.png') center repeat-x;
    text-shadow: 0 0 5px black, 0 1px 2px black;
    transition: filter .15s;

    @media screen and (max-width: $breakpoint-desktop-s) {
        font-size: 1.8em;
    }

    @media screen and (max-width: $breakpoint-mobile-l) {
        font-size: 1.5em;
        margin-left: 40px;
        margin-right: 40px;
    }

    &::before, &::after {
        content: "";
        width: 36px;
        height: 100%;
        background: transparent url('/src/assets/images/big-button-side.png') center no-repeat;
    }

    &::before { transform: translateX(-35px); }
    &::after { transform: translateX(35px) scaleX(-1); }

    &:hover, &:focus-visible, &:active {
        filter: brightness(1.2) saturate(80%);
    }
}
.life-aboard {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(260px, 280px);
    align-items: start;
    column-gap: clamp(1.5em, 2vw, 2.5em);
    row-gap: 0;
    width: 100%;
    max-width: $breakpoint-desktop-l;
    margin: 0 auto;
    padding: 0 20px 4em;

    > .section-title {
        grid-column: 1 / -1;
        width: 100%;
        margin-bottom: 0.8em;
    }

    > .persistence {
        grid-column: 1;
        justify-self: center;
        width: 100%;
        max-width: 68ch;
        margin: 0;
        padding: 0;
    }

    > .gameplay-preview {
        grid-column: 2;
        transform: translateX(-1.5em);
    }

    @media screen and (max-width: $breakpoint-desktop-s) {
        grid-template-columns: minmax(0, 1fr);

        > .persistence,
        > .gameplay-preview {
            grid-column: 1;
            min-width: 0;
        }

        > .gameplay-preview {
            transform: none;
        }
    }
}

.persistence {
    .logbook {
        position: relative;
        flex-direction: column;
        width: 100%;
        max-width: 68ch;
        margin: 0 0 2.4em;
        padding: 0 0 0 1.6em;
        list-style: none;
        border-left: 1px solid rgba($cyan, 0.35);

        li {
            position: relative;
            padding-bottom: 1.5em;

            &:last-child { padding-bottom: 0; }

            &::before {
                content: "";
                position: absolute;
                top: 0.45em;
                left: calc(-1.6em - 4px);
                width: 7px;
                height: 7px;
                background: $cyan;
                box-shadow: 0 0 7px 1px rgba($cyan, 0.7);
            }
        }
    }
    .logbook-time {
        display: block;
        margin-bottom: 0.2em;
        font-family: $font-days-one;
        font-size: 0.95em;
        letter-spacing: 0.08em;
        color: $brightCyan;
    }

    .prose {
        width: 100%;
        max-width: 68ch;
    }

    .logbook-entry {
        display: block;
        font-size: 1.05em;
        line-height: 1.55;
        color: #d5eaff;
        text-shadow: 0 0 6px rgba($deepBlue, 0.8);
    }

}
.gameplay-preview {
    align-self: center;
    width: 100%;
    max-width: 300px;

    @media screen and (max-width: $breakpoint-desktop-s) {
        max-width: 960px;
        justify-self: center;
    }

    .gameplay-gallery {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1em;
        width: 100%;
        margin: 0;
        padding: 0;
        list-style: none;
        &:has(li:nth-child(4)) {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        @media screen and (max-width: $breakpoint-desktop-s) {
            &,
            &:has(li:nth-child(4)) {
                display: flex;
                flex-direction: row;
                grid-template-columns: none;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
            }

            li {
                flex: 0 0 78vw;
                max-width: 420px;
                scroll-snap-align: center;
            }
        }
    }

    .screenshot-thumbnail {
        display: block;
        width: 100%;
        padding: 3px;
        overflow: hidden;
        background: rgba(34, 38, 102, 0.55);
        border: 1px solid rgba($greyBlue, 0.8);
        cursor: zoom-in;
        transition: border-color 0.2s ease, transform 0.2s ease;

        &:hover,
        &:focus-visible {
            border-color: $cyan;
            transform: translateY(-2px);
        }

        img {
            display: block;
            width: 100%;
            height: auto;
            aspect-ratio: 1080 / 552;
            object-fit: cover;
        }
    }

    .screenshot-lightbox {
        width: min(94vw, 1120px);
        max-width: none;
        padding: 0;
        overflow: visible;
        color: #d5eaff;
        background: #11164b;
        border: 1px solid $cyan;
        box-shadow: 0 0 36px rgba($cyan, 0.3);

        &::backdrop {
            background: rgba(3, 5, 29, 0.88);
            backdrop-filter: blur(3px);
        }

        figure {
            display: block;
            margin: 0;
        }

        img {
            display: block;
            width: 100%;
            height: auto;
        }

        figcaption {
            padding: 0.8em 1em;
            font-size: 0.95em;
            font-variant: small-caps;
            letter-spacing: 0.04em;
        }
    }

    .screenshot-close {
        position: absolute;
        z-index: 1;
        top: -14px;
        right: -14px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        padding: 0;
        background: $deepBlue;
        border: 1px solid $cyan;
        border-radius: 50%;
        cursor: pointer;

        img {
            width: 16px;
            height: 16px;
            image-rendering: pixelated;
        }
    }
}
.crew {
    padding-bottom: 4em;

    .crew-subtitle {
        max-width: 66ch;
        margin: 0 auto 2em;
        font-size: 1.05em;
        line-height: 1.65;
        text-align: center;
        text-wrap: pretty;
        color: #ccd6ff;
        text-shadow: 0 0 6px rgba($deepBlue, 0.8);
    }
    .crew-strip {
        display: grid;
        grid-template-columns: repeat(9, 54px);
        justify-content: center;
        gap: 8px;
        width: 100%;
        margin: 0 0 1.6em;
        padding: 0;
        list-style: none;

        @media screen and (max-width: $breakpoint-desktop-s) {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            justify-content: flex-start;
            overflow-x: auto;
            padding-bottom: 6px;
            scroll-snap-type: x proximity;
        }
    }

    .crew-portrait {
        flex: 0 0 auto;
        width: 54px;
        height: 54px;
        padding: 0;
        overflow: hidden;
        background: rgba($deepBlue, 0.6);
        border: 1px solid $greyBlue;
        border-radius: 3px;
        cursor: pointer;
        scroll-snap-align: start;
        transition: border-color 0.2s ease, filter 0.2s ease, transform 0.2s ease;

        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        &:hover { transform: translateY(-2px); }

        &.selected {
            border-color: $cyan;
            box-shadow: 0 0 9px 1px rgba($cyan, 0.55);
        }

        &:not(.selected) {
            filter: grayscale(0.55) brightness(0.85);
        }
    }

    .crew-file {
        flex-direction: row;
        align-items: flex-start;
        gap: 1.5em;
        width: 100%;
        max-width: 700px;
        min-height: 210px;
        padding: 1.6em;
        background-color: rgba(34, 38, 102, 0.5);
        box-shadow: inset 0 0 30px 14px rgba(15, 89, 171, 0.28);

        @include corner-bezel(14px, 0);

        @media screen and (max-width: $breakpoint-desktop-s) {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
    }

    .crew-file-portrait {
        flex: 0 0 auto;
        width: 116px;
        height: 152px;
        overflow: hidden;
        border: 1px solid $greyBlue;
        border-radius: 3px;

        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    }

    .crew-file-body { flex: 1; }

    .crew-file h3 {
        margin: 0 0 0.25em;
        font-family: $font-days-one;
        font-size: 1.4em;
        color: #ffffff;
    }

    .crew-file-tagline {
        margin: 0 0 0.7em;
        font-variant: small-caps;
        letter-spacing: 0.05em;
        font-size: 1em;
        color: $brightCyan;
        min-height: 1.2em;
    }

    .crew-file-description {
        margin: 0 0 1em;
        font-size: 1.08em;
        line-height: 1.55;
        color: #d5eaff;
        min-height: 1.5em;
    }

    .crew-file-link {
        color: $green;
        font-size: 1.02em;
        text-decoration: none;

        &:hover, &:focus-visible { text-decoration: underline; }
    }
}
@each $crewmate, $face-position-x, $face-position-y in $face-position {
    .crew-portrait.#{$crewmate} img,
    .crew-file-portrait img.#{$crewmate} {
        object-position: $face-position-x $face-position-y;
    }
}
.ship {
    padding-bottom: 4em;
    .ship-columns {
        flex-direction: row;
        align-items: flex-start;
        gap: 2.2em;
        width: 100%;
        padding: 1.8em 2em;
        background-color: rgba(34, 38, 102, 0.45);
        box-shadow: inset 0 0 30px 14px rgba(15, 89, 171, 0.25);

        @include corner-bezel(16px, 0);

        @media screen and (max-width: $breakpoint-desktop-s) {
            flex-direction: column;
            gap: 1.6em;
            padding: 1.5em 1.4em;
        }
    }

    .ship-pillar {
        flex: 1;

        h3 {
            margin: 0 0 0.4em;
            font-size: 1.15em;
            font-weight: 700;
            font-variant: small-caps;
            letter-spacing: 0.05em;
            color: $brightCyan;
        }

        p {
            margin: 0;
            font-size: 1em;
            line-height: 1.55;
            color: #ccd6ff;
        }
    }

}
.counters {
    padding-bottom: 4em;

    .counters-grid {
        display: grid;
        width: 100%;
        gap: 14px;
        margin: 0;
        grid-template-columns: repeat(4, minmax(0, 1fr));

        @media screen and (max-width: $breakpoint-desktop-s) {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .counter {
        padding: 1.2em 1em;
        text-align: center;
        background-color: rgba($deepBlue, 0.45);
        box-shadow: inset 0 0 24px 10px rgba(15, 89, 171, 0.22);

        @include corner-bezel(12px, 0);

        dt {
            font-family: $font-days-one;
            font-size: clamp(1.4em, 4vw, 1.9em);
            letter-spacing: 0.02em;
            color: $brightCyan;
            text-shadow: 0 0 10px rgba($cyan, 0.5);
        }

        dd {
            margin: 0.5em 0 0;
            font-size: 0.9em;
            font-variant: small-caps;
            letter-spacing: 0.05em;
            line-height: 1.3;
            color: #adb9ee;
        }
    }
}
.voices {
    padding-bottom: 4em;

    .voices-grid {
        display: grid;
        width: 100%;
        gap: 14px;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    }

    .voice {
        margin: 0;
        padding: 1.4em 1.5em;
        background-color: rgba(34, 38, 102, 0.5);
        box-shadow: inset 0 0 26px 12px rgba(15, 89, 171, 0.25);

        @include corner-bezel(12px, 0);

        blockquote {
            margin: 0 0 0.7em;
            font-size: 1.02em;
            line-height: 1.55;
            color: #d5eaff;

            &::before { content: "«\00a0"; color: $cyan; }
            &::after { content: "\00a0»"; color: $cyan; }
        }

        figcaption {
            font-size: 0.92em;
            font-variant: small-caps;
            letter-spacing: 0.05em;
            color: #adb9ee;
        }
    }
}
.newsgroup {
    padding-bottom: 4em;
    width: 100%;

    .news { width: 100%; }

    a.more {
        align-self: flex-end;
        padding: 0.4em 0.3em 0;
        color: $green;
        font-size: 1.15em;
        text-decoration: none;

        &:hover, &:focus-visible { text-decoration: underline; }
    }
}
.final-cta {
    padding-top: 1em;
    padding-bottom: 4em;
    text-align: center;

    .final-cta-title {
        margin: 0 0 0.2em;
        font-family: $font-days-one;
        font-size: clamp(1.5rem, 4vw, 2.3rem);
        line-height: 1.2;
        color: #ffffff;
        text-shadow: 0 0 12px rgba($deepBlue, 0.95), 0 2px 3px rgba(black, 0.6);
        text-wrap: balance;
    }

    .final-cta-subtitle {
        margin: 0 0 1.4em;
        font-size: 1.1em;
        color: #ccd6ff;
    }

}
.medias {
    padding-bottom: 3.5em;

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

            img { vertical-align: middle; }

            &:hover, &:focus-visible {
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

@keyframes daedalus-drift {
    0%   { transform: translateY(0) rotate(0deg); }
    50%  { transform: translateY(-14px) rotate(-1.1deg); }
    100% { transform: translateY(0) rotate(0deg); }
}

@media (prefers-reduced-motion: reduce) {
    .hero .daedalus { animation: none; }

    .homepage :deep(*),
    .homepage * {
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
    }
}
</style>
