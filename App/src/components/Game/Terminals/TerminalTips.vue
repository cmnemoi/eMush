<template>
    <div class="tips" v-if="content">
        <input
            id="tips"
            type="checkbox"
            name="tips"
            :checked="isBeginner()"/>
        <label for="tips" v-html="formatText(content)"></label>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { formatText } from "@/utils/formatText";
import { Player } from "@/entities/Player";

export default defineComponent ({
    name: "TerminalTips",
    props: {
        content: {
            type: String,
            required: true
        },
        player: {
            type: Player,
            required: false
        }
    },
    methods: {
        formatText(text: string): string {
            if (!text) {
                return '';
            }

            return formatText(text);
        },
        isBeginner() :boolean{
            return this.player?.hasStatusByKey('beginner') ?? false;
        }
    }
});
</script>

<style  lang="scss" scoped>

.tips { @extend %retracted-tips; }

</style>
