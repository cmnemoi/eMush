<template>
    <div class="crewmate-container">
        <div class="mate">
            <div class="card">
                <div class="avatar">
                    <img src="@/assets/images/char/portrait/Gioele_rinaldo_portrait.jpg" alt="crewmate">
                </div>
                <div>
                    <p class="name">Gioele Rinaldo</p>
                    <div class="status">
                        <span><img src="@/assets/images/status/spore.png">x2</span>
                        <span><img src="@/assets/images/status/thinklinked.png"></span>
                        <span><img src="@/assets/images/status/laid.png"></span>
                    </div>
                </div>
            </div>
            <p class="presentation">Armateur philantrope caféinomane.</p>
            <div class="skills">
                <img src="@/assets/images/skills/cook.png" alt="cook">
                <img src="@/assets/images/skills/sturdy.png" alt="sturdy">
                <img src="@/assets/images/skills/opportunist.png" alt="opportunist">
            </div>
        </div>
        <div class="interactions">
            <a href="#">Action 1</a>
            <a href="#">Action 2</a>
        </div>
        <a class="close" href="#">x</a>
    </div>
</template>

<script>

export default {
    name: "CrewmatePanel",
};
</script>

<style lang="scss" scoped>
.crewmate-container {
    position: absolute;
    z-index: 5;
    bottom: 0;
    width: 100%;
    flex-direction: row;
    padding: 3px;
    background-color: #222a6b;

    .mate {
        flex: 1;
        max-width: 50%;
        border-right: 1px dotted #4a5d8f;
        padding: 1px;
        padding-right: 4px;
        font-size: .85em;

        .card {
            flex-flow: row wrap;

            & > * { flex: 1 } //divs will wrap only if too small

            .avatar {
                align-items: center;
                min-width: 110px;
                height: 70px;
                overflow: hidden;
                border: 1px solid #161951;

                img {
                    position: relative;
                    width: 210px;
                    height: auto;
                    left: 20px;
                    top: -36px;
                }
            }

            .status {
                flex-direction: row;
                flex-wrap: wrap;
                font-size: .9em;
                span { padding: 1px; }
            }

            .name {
                font-weight: 700;
                text-transform: uppercase;
                padding-left: 4px;
                margin: 0;
            }
        }

        .presentation {
            margin: 0;
            padding: 2px 0px;
            font-size: .9em;
            font-style: italic;
        }

        .skills {
            flex-direction: row;
            flex-wrap: wrap;
        }
    }

    .interactions {
        flex: 1;
        max-width: 50%;
        padding: 1px;
        padding-left: 4px;

        a { @include button-style(); } // Provisional
    }

    .close {
        position: absolute;
        top: -30px;
        right: 6px;
        width: 22px;
        height: 22px;

        @include button-style(.83em, 400);
    }
}
</style>
