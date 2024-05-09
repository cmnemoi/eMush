<template>
    <div class="project" v-if="project">
        <h3>{{ project.name }}</h3>
        <div class="card">
            <Tippy>
                <img :src="getImgUrl(`projects/${project.key}.png`)" :alt="project.key">
                <template #content v-if="project.lore != ''">
                    <h1 v-html="formatText(project.name)"></h1>
                    <p v-html="formatText(project.lore)"></p>
                </template>
            </Tippy>
            <div class="progress-container">
                <div >
                    <Tippy
                        tag="div"
                        v-for="skill in project.bonusSkills"
                        :key="skill.name">
                        <img :src="skillIcons[skill.key].icon" alt="{{ skill.key }}">
                        <template #content>
                            <h1 v-html="formatText(skill.name)"></h1>
                            <p v-html="formatText(skill.description)"></p>
                        </template>
                    </Tippy>
                </div>
                <span class="completion">{{ project.progress }}</span>
            </div>
        </div>
        <p class="description" v-html="formatText(project.description)"></p>
        <Tippy>
            <p class="efficiency">
                {{ project.efficiency }}
            </p>
            <template #content>
                <h1 v-html="formatText(project.efficiencyTooltipHeader)"></h1>
                <p v-html="formatText(project.efficiencyTooltipText)"></p>
            </template>
        </Tippy>
        <div class="repair-pilgred-action" v-if="repairPilgredAction">
            <ActionButton
                :action="repairPilgredAction"
                @click="executeTargetAction(project, repairPilgredAction)"
            />
        </div>
        <div class="neron-project-action" v-else-if="participateAction">
            <ActionButton
                :action="participateAction"
                @click="executeTargetAction(project, participateAction)"
            />
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";
import { SkillIconRecord } from "@/enums/skill.enum";
import { Action } from "@/entities/Action";
import ActionButton from "@/components/Utils/ActionButton.vue";
import { Project } from "@/entities/Project";
import { mapActions } from "vuex";
import { Tippy } from "vue-tippy";

export default defineComponent ({
    name: "ProjectCard",
    components: {
        Tippy,
        ActionButton
    },
    props: {
        project: {
            type: Project,
            required: true
        }
    },
    computed: {
        participateAction(): Action | null {
            return this.project.participateAction;
        },
        repairPilgredAction(): Action | null {
            return this.project.repairPilgredAction;
        },
        skillIcons() {
            return SkillIconRecord;
        }
    },
    methods: {
        ...mapActions({
            'executeAction': 'action/executeAction'
        }),
        async executeTargetAction(target: Project, action: Action): Promise<void> {
            if (!action) throw new Error(`No action provided for project ${target.name}`);
            if (action.canExecute) {
                await this.executeAction({ target, action });
            }
        },
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
        padding: 0 5px .04em;
        background: #89e8fa;
        text-align: center;
        font-size: 1em;
        font-weight: normal;
        font-variant: small-caps;
        line-height: 1.1em;
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
