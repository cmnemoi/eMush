<template>
    <div class="banner">
        <div class="logo">
            <a href="/" class="logo"><img src="@/assets/images/logo_new.png" alt=""></a>
        </div>
        <div class="mainmenu">
            <a href="/#">Daedalus</a>
            <Login />
            <!--
        <a class="unavailable" href="/#">Mon Compte</a>
        <a class="unavailable" href="/#">Castings</a>
        <a class="unavailable" href="/#">Classement</a>
        <a class="unavailable" href="/#">Forum</a>
      -->
        </div>
    </div>
</template>

<script lang="ts">
import Login from "@/components/Login.vue";
import { defineComponent } from "vue";


export default defineComponent ({
    name: 'Banner',
    components: {
        Login,
    }
});
</script>

<style lang="scss" scoped>

.mainmenu {
    display: flex;
    flex-direction: row;

    a {
        margin: 0 1.4em;
        padding: .3em .6em;
        color: white;
        font-size: 1.1rem;
        font-weight: normal;
        letter-spacing: .06em;
        text-decoration: none;

        &:hover,
        &:active {
            color: #dffaff;
            text-shadow: 0 0 1px white, 0 0 1px white;
        }

        .unavailable {
            text-decoration: line-through;
            opacity: 0.6;
        }
    }
}

.banner {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.logo {
    height: 100%;
    margin: -0.05em 5em 0.1em 5em;
}

</style>
