<template>
    <section class="unit">
        <div class="banner">
            <span><img :src="getImgUrl('comms/tip.png')"> {{ channelName }} <img :src="getImgUrl('comms/tip.png')"></span>
        </div>
        <div :class="'tip ' + (isMush ? ' red' : 'cyan')">
            <span class="title" v-html="formatText(teamObjectives.title)"/>
            <ul class="list">
                <li v-for="element in teamObjectives.elements" :key="element" v-html="formatText(element)"/>
            </ul>
            <a
                v-if="teamObjectives.tutorial"
                :href="teamObjectives.tutorial.link"
                class="link"
                target="_blank"
                rel="noopener noreferrer"
                v-html="formatText(teamObjectives.tutorial.title)"/>
        </div>
        <div class="tip focus">
            <span class="title" v-html="formatText(characterObjectives.title)"/>
            <ul class="list">
                <li v-for="element in characterObjectives.elements" :key="element" v-html="formatText(element)"/>
            </ul>
            <a
                :href="characterObjectives.tutorial.link"
                class="link"
                target="_blank"
                rel="noopener noreferrer"
                v-html="formatText(characterObjectives.tutorial.title)"/>
        </div>
        <div class="tip">
            <span class="title">{{ externalResources.title }}</span>
            <ul class="list">
                <li v-for="element in externalResources.elements" :key="element.text">
                    <a
                        :href="element.link"
                        v-if="element.link"
                        class="link"
                        target="_blank"
                        rel="noopener noreferrer"
                        v-html="formatText(element.text)"/>
                    <p v-else v-html="formatText(element.text)"/>
                </li>
            </ul>
        </div>
    </section>
</template>

<script setup lang="ts">
import { CharacterObjectives, ExternalResources, TeamObjectives } from "@/entities/Channel";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";

defineProps<{
    channelName: string,
    teamObjectives: TeamObjectives,
    characterObjectives: CharacterObjectives,
    externalResources: ExternalResources,
    isMush: boolean
}>();
</script>

<style lang="scss" scoped>
#tips-tab a { color: $deepGreen; }

.tip {
    margin: 10px 6px;
    padding: 6px;
    background: white;
    box-shadow: 0 1px 1px 0 rgba(9, 10, 97, 0.15);

    &.cyan {
        border: 3px solid $cyan;
        box-shadow: 0 7px 6px -4px rgba(9, 10, 97, 0.5);
    }
    &.focus {
        border: 3px solid $green;
        box-shadow: 0 7px 6px -4px rgba(9, 10, 97, 0.5);
    }
    &.red {
        border: 3px solid $mushRed;
        box-shadow: 0 7px 6px -4px rgba(9, 10, 97, 0.5);
    }

    span {
        text-align: center;
        font-weight: 700;
        font-variant: small-caps;
    }

    p { margin: 4px 0; }
}

.list {
    flex-direction: column;

    li {
        list-style: disc;
        margin-left: 20px;
    }
}
</style>
