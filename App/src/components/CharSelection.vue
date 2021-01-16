<template>
    <div class="main">
        <!--<h1>Select a character</h1>
        <ul class="char-selection">
            <li v-for="(character, key) in characters" :key="key">
                <a href="/#" @click="selectCharacter(character)">
                    <div><img :src="characterBody(character)"></div>
                    <span>{{ character.name }}</span>
                </a>
            </li>
        </ul>-->
        <div class="char-selection">
            <section class="char">
                <div class="header">
                    <p class="level">1</p>
                    <h2 class="name">Eleesha Williams</h2>
                </div>
                <div class="portrait">
                    <img src="@/assets/images/char/portrait/Eleesha_williams_portrait.jpg" alt="Eleesha Williams">
                </div>
                <div class="body">
                    <img src="@/assets/images/char/body/eleesha.png">
                </div>
            </section>
        </div>
        <div class="banner">
            <div class="skills">
                <div class="Expert radio">
                    <img src="@/assets/images/skills/cook.png" alt="cook">
                    <p>Expert radio</p>
                </div>
                <div class="Expert logistique">
                    <img src="@/assets/images/skills/cook.png" alt="cook">
                    <p>Expert logistique</p>
                </div>
                <div class="Tireur">
                    <img src="@/assets/images/skills/cook.png" alt="cook">
                    <p>Tireur</p>
                </div>
            </div>
            <div class="description">
                <p>Brilliant biologist and hardcore rebel markswoman, she is driven by the need to recontact Kivanç Terzi. Her technical and logistical skills are highly prized.</p>
            </div>
            <div class="gamestart">
                <p>You have chosen... <strong>Eleesha Williams</strong>.</p>
                <a class="start" href="#"><span>Start the game</span></a>
            </div>
        </div>
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
    justify-content: stretch;
    min-height: 625px;
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

    h1 {
        flex-flow: row wrap;
        margin: 15px;
        font-size: 150%;
        font-variant: small-caps;
    }

    .char-selection {
        flex-flow: row wrap;
        justify-content: space-evenly;
        flex: 1;

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
                    font-family: "Days One";
                    font-size: 1.4em;
                    text-align: center;
                    background: rgba(54,76,148,0.3);
                }

                .name {
                    margin: auto;
                    margin-left: .5em;
                    font-size: .9em;
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
                align-items: flex-end;
                justify-content: flex-end;
                padding: 10px;
                height: 30px;
                background: rgba(54,76,148,0.3);

                img {
                    transform: translate(-13px, -12px);
                    z-index: 2;
                }

                &::after {
                    content: "";
                    position: absolute;
                    right: 10px;
                    bottom: 10px;
                    min-width: 39px;
                    min-height: 21px;
                    background: url("~@/assets/images/figure.png") center no-repeat;
                }
            }

            &:hover, &:focus, &:active {
                transform: translateY(-8px);
                box-shadow: 0 9px 6px -6px #0f0f43;

            }
        }
    }

    .banner {
        flex-direction: row;
        justify-content: flex-start;
        font-size: .85em;

        & > * { margin: 12px; }

        .skills {
            flex-flow: row wrap;
            flex: 1;
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
            flex: 3;
            padding: 1em;
            line-height: 1.4em;
        }

        .gamestart {
            flex: 2;
            min-width: 280px;
            align-self: center;
            align-items: center;
            padding: 0 2em;
            border-left: 1px solid rgba(255, 255, 255, 0.1);

            p strong { color: #01c3df; }

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
}



</style>



