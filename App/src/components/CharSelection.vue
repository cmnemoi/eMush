<template>
    <div class="main">
        <h1>Select a character</h1>
        <ul class="char-selection">
            <li v-for="(character, key) in characters" :key="key">
                <a href="/#" @click="selectCharacter(character)">
                    <div><img :src="characterBody(character)"></div>
                    <span>{{ character.name }}</span>
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
import ApiService from "@/services/api.service";
import { characterEnum } from "@/enums/character";
import PlayerService from "@/services/player.service";

export default {
    name: 'CharSelection',
    props: {
    },
    data: () => {
        return {
            loading: false,
            daedalusId: null,
            characters: []
        };
    },
    beforeMount() {
        this.loading = true;
        ApiService.get('daedalus/available-characters')
            .then((response) => {
                this.daedalusId = response.data.daedalus;
                this.characters = response.data.characters;
                this.loading = false;
            });
    },
    methods: {
        characterBody: function(character) {
            return characterEnum[character.key] ? characterEnum[character.key].body : require('@/assets/images/items/todo.jpg');
        },
        selectCharacter: function(character) {
            PlayerService.selectCharacter(this.daedalusId, character.key)
                .then(() => {
                    this.loading = false;
                })
                .catch((error) => {
                    console.error(error);
                    this.loading = false;
                });
        }
    }
};
</script>

<style lang="scss" scoped>

.main {
    position: relative;
    min-height: 424px;
    max-width: 1080px;
    width: 100%;
    margin: 36px auto;
    padding: 12px 12px 42px 12px;
    z-index: 10;

    &::after {
        content: "";
        position: absolute;
        z-index: -1;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;

        @include corner-bezel(18.5px);

        box-shadow: inset 0 0 35px 25px rgb(15, 89, 171);
        background-color: rgb(34, 38, 102);
        opacity: 0.5;
    }
}

h1 {
    margin: 15px;
    font-size: 150%;
    font-variant: small-caps;
}

.char-selection {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: space-evenly;

    & li a {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        min-width: 160px;
        padding: 12px;
        margin: 4px;
        color: white;
        font-size: 0.91em;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        text-decoration: none;
        background: rgba(54, 76, 148, 0.35);

        @include corner-bezel(12px, 0);

        & > div {
            position: relative;
            align-items: center;
            margin-right: 16px;
            & img { z-index: 2; }

            &::after {
                content: "";
                position: relative;
                left: 1px;
                bottom: 8px;
                width: 39px;
                height: 21px;
                background: url("~@/assets/images/figure.png") center no-repeat;
            }
        }

        &:hover,
        &:active { background: rgba(54, 76, 148, 0.65); }
    }
}

</style>



