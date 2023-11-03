<template>
    <div class="box-container">
        <Spinner :loading="loading"></Spinner>
        <form class="daedalus-selection" onsubmit="return false">
            <div>
                <label>{{ $t('charSelection.selectLanguage') }}</label>
                <ol class="flag-list">
                    <img
                        v-for="(lang, i) in languages"
                        :key="`Lang${i}`"
                        :value="lang.caption"
                        @click="resetValues(); loadAvailableCharacters(i);"
                        :src="lang.icon"
                        class="flag"
                    />
                </ol>
            </div>
            <span v-if="error" class="error">{{ $t(['errors', error].join('.')) }}</span>
        </form>
        <div class="char-selection" v-if="!error">
            <section
                v-for="(character, key) in characters"
                :key="key"
                class="char"
                @click="selectedCharacter = character; characterSelected = true;"
                @mouseenter="hoveredCharacter = character; characterHovered = true; "
                @mouseleave="characterHovered = false"
            >
                <div class="header">
                    <p class="level" />
                    <h2 class="name">
                        {{ character.name }}
                    </h2>
                </div>
                <div class="portrait">
                    <img :src="characterPortrait(character)">
                </div>
                <div class="body">
                    <img :src="characterBody(character)">
                </div>
            </section>
        </div>
        <div class="banner" v-if="!error">
            <div class="skills" style="display:none">
                <div class="Expert radio">
                    <img src="@/assets/images/skills/human/cook.png" alt="cook">
                    <p>Expert radio</p>
                </div>
                <div class="Expert logistique">
                    <img src="@/assets/images/skills/human/cook.png" alt="cook">
                    <p>Expert logistique</p>
                </div>
                <div class="Tireur">
                    <img src="@/assets/images/skills/human/cook.png" alt="cook">
                    <p>Tireur</p>
                </div>
            </div>
            <div class="description">
                <p v-if="characterHovered">{{ hoveredCharacter.abstract }}</p>
                <p v-else-if="characterSelected">{{ selectedCharacter.abstract }}</p>
            </div>
            <div class="gamestart" v-if="selectedCharacter">
                <p class="choice">
                    {{ $t("charSelection.youChoose") }} <strong>{{ characterCompleteName(selectedCharacter) }}</strong>.
                </p>
                <a class="start" href="#" @click="selectCharacter(selectedCharacter)"><span>{{ $t("charSelection.startGame") }}</span></a>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import ApiService from "@/services/api.service";
import { characterEnum } from "@/enums/character";
import PlayerService from "@/services/player.service";
import { Character } from "@/entities/Character";
import Spinner from "@/components/Utils/Spinner.vue";
import { defineComponent } from "vue";
import { mapGetters, mapActions } from "vuex";
import { gameLocales } from "@/i18n";

export default defineComponent ({
    name: 'CharSelection',
    components: {
        Spinner
    },
    props: {
    },
    data: () => {
        return {
            loading: false,
            daedalusId: -1,
            characters: Array<Character>(),
            daedaluses: Array<any>(),
            daedalusName: '',
            characterHovered: false,
            hoveredCharacter: null,
            characterSelected: false,
            selectedCharacter: null,
            error: null as any,
            languages: gameLocales
        };
    },
    computed: {
        ...mapGetters('auth', [
            'getUserInfo'
        ])
    },
    methods: {
        loadAvailableCharacters(language: string) {
            this.loading = true;
            ApiService.get('daedaluses/available-characters', { params: { language: language } })
                .then((response) => {
                    this.daedalusId = response.data.daedalus;
                    this.characters = response.data.characters;
                    this.loading = false;
                })
                .catch((error) => {
                    this.clearError();
                    this.error = error.response?.data?.detail;
                    this.loading = false;
                });

        },
        characterPortrait: function(character: Character) {
            return characterEnum[character.key] ? characterEnum[character.key].portrait : require('@/assets/images/items/todo.jpg');
        },
        characterBody: function(character: Character) {
            return characterEnum[character.key] ? characterEnum[character.key].body : require('@/assets/images/items/todo.jpg');
        },
        characterCompleteName: function(character: Character) {
            return characterEnum[character.key] ? characterEnum[character.key].completeName : 'Unknown';
        },
        resetValues: function() {
            this.characterHovered = false;
            this.hoveredCharacter = null;
            this.characterSelected = false;
            this.selectedCharacter = null;
        },
        selectCharacter: function(character: Character) {
            PlayerService.selectCharacter(this.getUserInfo.userId, this.daedalusId, character.key)
                .then(() => {
                    this.loading = false;
                })
                .catch((error) => {
                    this.error = error.response?.data?.detail;
                    this.loading = false;
                });
        },
        ...mapActions('error', [
            'clearError'
        ])
    }
});
</script>

<style lang="scss" scoped>

.box-container {
    justify-content: stretch;
    min-height: 625px;
}

h1 {
    flex-flow: row wrap;
    margin: 15px;
    font-size: 1.5em;
    font-variant: small-caps;
}


.daedalus-selection {
    display: flex;
    align-self: center;
    flex-direction: column;
    align-items: center;
    padding: 1em 1em 1.8em;
    font-size: 1.25em;

    div {
        flex-direction: row;
        align-items: center;

        & > * { margin: 0 .15em; }

        select option .placeholder {
            color: red;
        }

        .flag {
            margin: 4% 15% 0 15%;
        }
    }

    label {
        font-weight: bold;
        font-style: italic;
        color: #88a6fe;
    }

    button { @include button-style; }

    .error {
        margin: 1em 0;
        padding: .3em .8em;
        background: transparentize($red, .7);
        border: 1px solid $red;
        border-radius: 6px;
        font-size: .9em;
        font-style: italic;
    }

}

.char-selection {
    flex-flow: row wrap;
    justify-content: space-evenly;
    flex: 1;
}

.char {
    cursor: pointer;
    max-width: 230px;
    height: 384px;
    margin: 12px 12px 28px;
    box-shadow: 0 0 6px -6px #0f0f43;

    transition: all .15s;

    .header {
        flex-direction: row;
        min-height: 34px;
        margin-bottom: -1px;

        @include corner-bezel(12px, 0);

        .level {
            width: 33px;
            margin: 0;
            padding-top: .15em;
            font-family: $font-days-one;
            font-size: 1.65em;
            text-align: center;
            background: rgba(54,76,148,0.3);
        }

        .name {
            margin: auto;
            margin-left: .5em;
            font-size: 1.1em;
            line-height: 1em;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
        }
    }

    .portrait {
        width: 100%;
        padding: 10px;
        background: rgba(54,76,148,0.3);
    }

    .body {
        position: relative;
        align-items: center;
        justify-content: flex-end;
        padding: 10px;
        padding-left: calc(100% - 20px);
        height: 30px;
        background: rgba(54,76,148,0.3);

        img {
            transform: translate(-18px, 8px);
            z-index: 2;
        }

        &::after {
            content: "";
            position: relative;
            transform: translate(-17px, 0);
            min-width: 39px;
            min-height: 21px;
            background: url("~@/assets/images/figure.png") center no-repeat;
        }
    }

    &:hover, &:focus, &:active {
        margin-top: 4px;
        margin-bottom: 36px;
        box-shadow: 0 9px 6px -6px #0f0f43;

    }
}

.banner {
    flex-flow: row wrap;
    justify-content: flex-start;
    // font-size: .85em;

    & > * {
        flex: 1;
        min-width: 260px;
        margin: 12px;
    }

    .skills {
        flex-flow: row wrap;
        min-width: 260px;
        padding: .3em .75em;
        border-radius: 5px;
        background: rgba(23,68,142,0.6);

        div {
            flex-direction: row;
            align-items: center;
            padding: 0 .8em;

            img { padding-right: .3em; }
        }
    }

    .description {
        padding: 1em;
        line-height: 1.4em;
    }

    .gamestart {
        min-width: 320px;
        align-self: center;
        align-items: center;
        padding: 1.25em;

        @media screen and (min-width: 960px) { border-left: 1px solid rgba(255, 255, 255, 0.1); }

        .choice {
            margin: 0 0 .8em;
            strong { color: #01c3df; }
        }

        a.start {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 40px;
            color: white;
            font-size: 1.3em;
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
    }
}

</style>



