<template>
    <div class="main">
        <div class="death-summary">
            <h1>Vous êtes mort !</h1>
            <div class="char-sheet">
                <img class="avatar" src="@/assets/images/char/portrait/Stephen_seagull_portrait.jpg" alt="avatar">
                <div>
                    <div class="char-card">
                        <img class="body" src="@/assets/images/char/body/stephen.png" alt="">
                        <div>
                            <h3 class="char-name">Stephen</h3>
                            <p class="pseudo">Atar7</p>
                        </div>
                        <p class="score">43<img src="@/assets/images/triumph.png" alt="triumph"></p>
                    </div>
                    <div class="epitaph-form">
                        <textarea id="epitaph" v-model="epitaph" maxlength="300" placeholder="Laissez vos impressions sur la partie ici !"></textarea>
                        <p v-bind:class="{ limit: !(maxChar - epitaph.length) }" class="char-count"> {{ (maxChar - epitaph.length) }} char.</p>
                    </div>
                    <div>
                        <p class="death-cause"><img src="@/assets/images/dead.png" alt="dead"> Assassiné par un équipier !</p>
                    </div>
                    <div class="history-logs">
                        <span class="tab">Grands Moments</span>
                        <span class="tab active">Historique de Triomphe</span>
                        <div class="logs">
                            <p><img src="@/assets/images/point.png" alt="dot"> 2 x Cycle Humain ( + 1 )</p>
                            <p><img src="@/assets/images/point.png" alt="dot"> 1 x Ambitieux ( + 4 )</p>
                            <p><img src="@/assets/images/point.png" alt="dot"> 1 x Contact Avec Sol ( + 8 )</p>
                            <p><img src="@/assets/images/point.png" alt="dot"> 1 x Vers le Nouvel Eden ( + 6 )</p>
                            <p><img src="@/assets/images/point.png" alt="dot"> 3 x Cycle Humain ( + 1 )</p>
                        </div>
                    </div>
                </div>
            </div>
            <table class="crew-summary">
                <tbody>
                    <tr>
                        <th>nom</th>
                        <th>mort</th>
                        <th>cause</th>
                        <th>j'aime</th>
                    </tr>
                    <tr>
                        <td><img src="@/assets/images/char/body/hua.png" class="char hua"> <span class="charname">Hua</span></td>
                        <td>6.7</td>
                        <td>Assassiné</td>
                        <td><button class="like">1 <img src="@/assets/images/dislike.png"></button></td>
                    </tr>
                    <tr>
                        <td><img src="@/assets/images/char/body/paola.png" class="char paola"> <span class="charname">Paola</span></td>
                        <td>Pas Encore</td>
                        <td>-</td>
                        <td><button class="like">1 <img src="@/assets/images/dislike.png"></button></td>
                    </tr>
                    <tr>
                        <td><img src="@/assets/images/char/body/derek.png" class="char derek"> <span class="charname">Derek</span></td>
                        <td>4.6</td>
                        <td>Enlevé par des races supérieures</td>
                        <td><button class="like">0 <img src="@/assets/images/dislike.png"></button></td>
                    </tr>
                    <tr>
                        <td><img src="@/assets/images/char/body/kuan_ti.png" class="char kuan_ti"> <span class="charname">Kuan Ti</span></td>
                        <td>6.7</td>
                        <td>Assassiné</td>
                        <td><button class="like liked">2 <img src="@/assets/images/like.png"></button></td>
                    </tr>
                </tbody>
            </table>
            <p><em>Vous serez notifié quand le classement de fin de partie sera publié.</em></p>
            <a href="#" class="validate">Valider et continuer</a>
        </div>
        <div class="chat">PLACEHOLDER</div>
    </div>

</template>

<script>

export default {
    name: 'Purgatory',

    data: function () {
        return {
            maxChar: 300,
            epitaph: ''
        }
    }
};
</script>

<style lang="scss" scoped>

.main {
    position: relative;
    flex-flow: row wrap;
    justify-content: stretch;
    min-height: 625px;
    max-width: 1080px;
    width: 100%;
    margin: 36px auto;
    padding: 12px 12px 42px 12px;
    z-index: 10;
    font-size: .9rem;

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

    .death-summary { flex: 1; }

    h1 {
        font-size: 1.4em;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin: .6em 1.2em 1.2em;
    }

    .char-sheet {
        flex-direction: row;

        .avatar {
            width: fit-content;
            height: fit-content;
            margin-right: -1.4em;
            opacity: .6;
            mask-image: radial-gradient(ellipse 100% 100%, black 30%, transparent 50%);
        }

        .char-card {
            flex-direction: row;
            align-items: center;

            .body {
                width: fit-content;
                height: fit-content;
                margin-right: .8em;
            }

            .char-name, .pseudo, .score { margin: .1em 0; }

            .char-name {
                font-size: 1.6em;
                font-weight: 400;
            }

            .pseudo { font-size: 1.05em; }

            .score {
                flex: 1;
                font-family: Days-One;
                color: #01c3df;
                text-align: right;
                font-size: 2.2em;
                letter-spacing: .05em;
                text-shadow: 0 0 2px black;

                img {
                    vertical-align: middle;
                    margin-left: .25em;
                }
            }
        }

        .epitaph-form {
            position: relative;
            margin: 1.4em 0;
            border: 1px solid #5f67bf;
            background-color: rgba(31, 39, 104, .5);
            box-shadow: 0px 8px 6px -6px rgba(23, 68, 142, .6);

            &::before {
                content:"";
                position: absolute;
                left: -7px;
                top: 6px;
                width: 14px;
                height: 14px;
                border: 1px solid #5f67bf;
                background-color: #1b215c;
                transform: rotate(-45deg);
                clip-path: polygon(0 0, 100% 0, 0 100%);
            }

            #epitaph {
                padding: 1em .8em;
                color: white;
                font-size: 1.2em;
                line-height: 1.6em;
                font-style: italic;
                border: none;
                background: none;
                resize: vertical;
            }

            /* do not group these rules */
            #epitaph::-webkit-input-placeholder {
                color: rgba(255, 255, 255, .8);
            }
            #epitaph:-moz-placeholder {
                /* FF 4-18 */
                color: rgba(255, 255, 255, .8);
                opacity: 1;
            }
            #epitaph::-moz-placeholder {
                /* FF 19+ */
                color: rgba(255, 255, 255, .8);
                opacity: 1;
            }
            #epitaph:-ms-input-placeholder {
                /* IE 10+ */
                color: rgba(255, 255, 255, .8);
            }
            #epitaph::-ms-input-placeholder {
                /* Microsoft Edge */
                color: rgba(255, 255, 255, .8);
            }
            #epitaph::placeholder {
                /* modern browser */
                color: rgba(255, 255, 255, .8);
            }

            .char-count {
                position: absolute;
                top: -.8em;
                right: 1.2em;
                margin: 0;
                padding: .2em .6em;
                font-size: .8em;
                font-style: italic;
                font-weight: bold;
                letter-spacing: .05em;
                text-shadow: 0 0 4px #092f6d;
                /* border: 1px solid #5f67bf; */
                border-radius: 4px;
                background-color: #5f67bf;

                transition: all .2s;

                &.limit { background-color: #ff4e64; }
            }
        }

        .death-cause { margin-top: 0; }

        .history-logs {
            display: block;

            .tab {
                display: block;
                float: left;
                cursor: pointer;
                font-size: .86em;
                opacity: .6;
                padding: 5px 15px;
                border-radius: 5px 5px 0 0;

                &:hover, &:focus, &:active, &.active { opacity: 1; }

                &.active {
                    background: #222b6b;
                    border-top: 1px solid #3d4dbf;
                }
            }

            .logs {
                width: 100%;
                border-top: 1px solid #3d4dbf;
                font-size: .8em;
                letter-spacing: .05em;
                padding: 1em;

                p { margin: .5em 0; }
            }
        }
    }

    .crew-summary {
        width: 100%;
        background: #222b6b;
        border-radius: 5px;
        border-collapse: collapse;

        img { vertical-align: middle; }

        tr:not(:first-of-type) {
            border-top: 1px solid rgba(0,0,0,0.2);

            &:hover { background: rgba(255, 255, 255, .03); }
        }

        th, td {
            padding: 1em .5em;
            text-align: center;
            vertical-align: middle;
        }

        th {
            opacity: .6;
            font-size: .85em;
            letter-spacing: .05em;
        }

        button.like {
            cursor: pointer;
            color: white;
            font-weight: bold;
            padding: .2em .4em;
            margin: 0 auto;
            background: rgba(17,84,165,0.5);
            border-radius: 4px;
            white-space: pre;

            transition: all .15s;

            &:hover, &:focus, &:active {
                background: rgba(17, 84, 165, 1);
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .15);
                }

            &.liked {
                background: rgba(255, 54, 118, .5);

                &:hover, &:focus, &:active { background: rgba(255, 54, 118, .7); }
            }
        }
    }

    .validate {
        @include button-style($font-size: 1em);
        margin: .5em auto;
        padding: .2em 1em .4em;
    }

    
    .chat { /* PROVISIONAL */
        width: 406px;
        height: 435px;
        margin-left: 15px;
        margin-top: 2em;
        color: red;
        background: #c2f3fc;
    }
}



</style>



