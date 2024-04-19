<template>
    <div class="project" v-if="project">
        <h3>{{ project.name }}</h3>
        <div class="card">
            <img :src="getImgUrl(`projects/${project.key}.png`)">
            <div class="progress-container">
                <div >
                    <Tippy
                        tag="div"
                        v-for="skill in project.bonusSkills"
                        :key="skill.name">
                        <img :src="skillIcons[skill.key].icon">
                        <template #content>
                            <h1 v-html="formatText(skill.name)"></h1>
                            <p v-html="formatText(skill.description)"></p>
                        </template>
                    </Tippy>
                </div>
                <span class="completion">{{ project.progress }}</span>
            </div>
        </div>
        <p class="description">
            {{ project.description }}
        </p>
        <p class="efficiency">
            {{ project.efficiency }}
        </p>
        <button class="action-participate">
            Participer
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";
import { SkillIconRecord } from "@/enums/skill.enum";

export default defineComponent ({
    name: "ProjectCard",
    props: {
        project: {
            type: Object,
            required: true
        }
    },
    computed: {
        skillIcons() {
            return SkillIconRecord;
        }
    },
    methods: {
        getImgUrl,
        formatText
    }
});

</script>

<style lang="scss" scoped>
.project {
    min-width: 132px;
    width: 132px;
    padding-bottom: .4em;
    margin-right: 6px;
    background: $lightCyan url("/src/assets/images/neroncore_bg.svg") no-repeat right bottom;
    border-left: 2px solid #aad4e5;

    scroll-snap-align: start; // to control scroll snapping

    @include corner-bezel(0, 6.5px, 0);

    h3 {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 42px;
        margin: 0;
        padding-bottom: .25em;
        background: #89e8fa;
        text-align: center;
        font-size: 1em;
        font-weight: normal;
        font-variant: small-caps;
        line-height: 1em;
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
            letter-spacing: -.03em;
            opacity: .7;
            text-align: center;
        }
    }

    .description {
        margin: .8em 4px;
        flex: 1;
    }

    .efficiency {
        opacity: .6;
        margin: .6em 0;
        font-size: .9em;
        font-style: italic;
        text-align: center;
    }

    .action-participate {
        @include button-style;
        margin: 1px 4px;
    }

    &.ongoing {
        border-color: $green;

        .completion {
            opacity: 1;
            color: darken($green, 5%);
            text-shadow: 0 0 10px white;
        }
    }
}
</style>
