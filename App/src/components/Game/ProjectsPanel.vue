<template>
    <ul class="projects">
        <!--    <li class="project"><span>Project</span><img :src="getImgUrl('projects/equipment_sensor.png')" alt="Equipment sensor"></li>-->
        <!--    <li class="project"><span>Project</span><img :src="getImgUrl('projects/fission_coffee_roaster.png')" alt="Fission coffee roaster"></li>-->
        <!--    <li class="project"><span>Project</span><img :src="getImgUrl('projects/plasma_shield.png')" alt="Plasma shield"></li>-->
        <!--    <li class="project"><span>Project</span><img :src="getImgUrl('projects/gold/thalasso.png')" alt="Thalasso"></li>-->
        <!--    <li class="research"><span>Research</span><img :src="getImgUrl('researches/anti_mush_serum.png')" alt="Anti-mush serum"></li>-->
        <!--    <li class="research"><span>Res.</span><img :src="getImgUrl('researches/drug_dispenser.png')" alt="Drug dispenser"></li>-->
        <!--    <li class="research"><span>Res.</span><img :src="getImgUrl('researches/myco_alarm.png')" alt="Myco-alarm"></li>-->
        <!--    <li class="research"><span>Res.</span><img :src="getImgUrl('researches/natamy_gun.png')" alt="Natamy gun"></li>-->
        <!--    <li class="research"><span>Res.</span><img :src="getImgUrl('researches/ncc_lens.png')" alt="NCC lens"></li>-->
        <!--    <li class="research"><span>Res.</span><img :src="getImgUrl('researches/mushicide_soap.png')" alt="Mushicide soap"></li>-->
    </ul>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";

export default defineComponent ({
    name: "ProjectsPanel",
    props: {
    },
    methods: {
        getImgUrl
    }
});
</script>

<style  lang="scss" scoped>
.projects {
    flex-direction: row;
    flex-wrap: wrap;
    margin-top: 28px;

    li {
        position: relative;
        margin: 0 4px 4px 0;

        span {
            position: absolute;
            z-index: 5;
            margin: 1px 0;
            width: 100%;
            text-align: center;
            font-size: 0.8em;
            text-shadow: 0 0 2px rgba(27, 28, 85, 1), 0 0 2px rgba(27, 28, 85, 1), 0 0 2px rgba(27, 28, 85, 1);
        }
    }
}
</style>
