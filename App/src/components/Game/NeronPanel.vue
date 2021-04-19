<template>
    <div class="neron-panel">
        <h1><img src="@/assets/images/spot2.svg"> Neron's Core V4.54</h1>
        <div class="tips">
            <input type="checkbox" name="tips" id="tips">
            <label for="tips">
                You are in the core of <em class="red">Neron</em>. Here you can run updates and <strong>unblock features</strong> which will benefit the whole crew. These features were originally destined for the <em class="red">Magellan</em> project.
                <br /><br />
                Projects will advance more quickly if you have <strong>the appropriate skills</strong>.
                <br /><br />
                A solitary person, even if they have the appropriate skills, will have difficulty successfully completing research. If you advance research more than once in a row, your efficiency will decrease. <strong>Working with a crewmate is the key!</strong>
                <br /><br />
                That's not all: if more than one project is advancing in parallel, the first project completed will wipe out any progress made in the other.
            </label>
            <span class="expand"><label for="tips">Expand/hide</label></span>
        </div>
        <div class="project-container">
            <div class="project">
                <h3>Anti-gravity Thrusters</h3>
                <div class="card">
                    <img src="http://mush.twinoid.com/img/cards/projects/icarus_antigrav_propeller.png">
                    <div class="progress-container">
                        <div>
                            <img class="skill" src="http://mush.twinoid.com/img/icons/skills/pilot.png">
                            <img class="skill" src="http://mush.twinoid.com/img/icons/skills/physicist.png">
                        </div>
                        <span class="completion">0%</span>
                    </div>
                </div>
                <p class="description">Increases your chances of successfully landing on a planet</p>
                <p class="efficiency">Efficiency : 18% - 18%</p>
                <button class="action-participate">1x Participate</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "NeronPanel",
    props: {
    }
};
</script>

<style  lang="scss" scoped>

.neron-panel {
    position: relative;
    flex-direction: column;
    width: 424px;
    padding: 5px 8px;
    color: #090a61;
    background: #c2f3fc;
    font-size: .85em;

    h1 {
        font-size: 1em;
        text-transform: uppercase;
        margin: 0 .2em;
    }

    .tips {
        position: relative;
        margin: .35em 0;
        padding: .25em;
        cursor: pointer;
        background: #a6eefb;
        border: 1px solid #84e100;
        border-radius: 3px;

        & * { cursor: pointer; }

        input {
            position: absolute;
            opacity: 0;
        }

        & > label {
            display: block;
            overflow: hidden;
            opacity: .5;
            height: 1.55em;
            line-height: 1.35em;
            font-style: italic;

            em.red {
                font-style: normal;
                color: #cf1830;
            }
        }

        input:checked ~ label {
            opacity: 1;
            height: auto;
        }

        .expand {
            position: absolute;
            top: -.75em;
            right: .8em;
            padding: 1px 3px;
            border-radius: 5px;
            background: #84e100;
            color: white;
            font-size: .75em;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .05em;
            text-shadow: 0 0 3px rgba(0, 0, 0, .4);
        }
    }

    .project-container {
        flex-direction: row;
        align-items: stretch;
        min-height: 272px;

        .project {
            width: 128px;
            padding-bottom: .4em;
            margin-right: 6px;
            background: #a6eefb url("http://data.mush.twinoid.com/img/design/neroncore_bg.png") no-repeat right bottom;
            border-left: 2px solid #aad4e5;

            @include corner-bezel(0, 6.5px, 0);

            h3 {
                width: 100%;
                min-height: 42px;
                margin: 0;
                background: #89e8fa;
                text-align: center;
                font-size: 1em;
                font-weight: normal;
                font-variant: small-caps;
            }

            .card {
                flex-direction: row;
                padding: 4px;

                img {
                    width: fit-content;
                    height: fit-content;
                }

                .progress-container {
                    flex: 1;
                    align-items: stretch;
                    margin: auto;

                    & > div {
                        flex-direction: row;
                        justify-content: center;
                    }
                }

                .completion {
                    margin: .1em;
                    font-size: 2.25em;
                    opacity: .7;
                    text-align: center;
                }
            }

            .description { margin: .8em 4px; }

            .efficiency {
                opacity: .6;
                font-size: .9em;
                font-style: italic;
                text-align: center;
            }

            .action-participate {
                @include button-style($font-size: 1em);
            }
        }
    }
}

</style>
