<template>
    <img :src="titleIcon(title)">
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { TitleEnum, titleEnum } from "@/enums/title.enum";
import { NameDescObject } from "@/entities/NameDescObject";

export default defineComponent ({
    props: {
        title: NameDescObject
    },
    methods: {
        titleIcon: function(title: NameDescObject) {
            return titleEnum[title.id] ? titleEnum[title.id].image : titleEnum[TitleEnum.COMMANDER].image;
        }
    }
});
</script>
