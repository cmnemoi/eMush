<template>
    <div
        class="research-info"
        v-for="requirement in terminal.infos.requirements"
    >
        <img :src="getImgUrl('info.png')" alt="info" />
        <p v-html="formatText(requirement)"></p>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";
import { Terminal } from "@/entities/Terminal";
import ResearchCard from "./ResearchCard.vue";

export default defineComponent({
    name: "ResearchLabTerminal",
    components: {
        ResearchCard,
    },
    props: {
        terminal: {
            type: Terminal,
            required: true,
        },
    },
    methods: {
        getImgUrl,
        formatText,
    },
});
</script>

<style lang="scss" scoped>
.research-info {
    box-shadow: 0 1px 1px 0 rgba(9, 10, 97, 0.15);
    align-items: center;
    flex-direction: row;
    border-radius: 2px;
    margin-top: 4px;
    margin-bottom: 4px;
    padding: 0.5em;
    background-color: #e1f7ff;
    p {
        margin: 0;
        margin-left: 0.5em;
    }
    img {
        width: fit-content;
        height: fit-content;
    }
}
</style>
