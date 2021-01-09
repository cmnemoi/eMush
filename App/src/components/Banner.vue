<template>
    <div class="banner">
        <div class="tid_shadow" />
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

<script>
import Login from "@/components/Login";

export default {
    name: 'Banner',
    components: {
        Login
    }
};
</script>

<style lang="scss" scoped>

.mainmenu > a {
    margin: 0 20px;
    padding: 5px 10px;
    color: white;

    &:hover,
    &:active {
        color: #dffaff;
        text-shadow: 0 0 1px rgb(255, 255, 255), 0 0 1px rgb(255, 255, 255);
    }
}

.unavailable {
    text-decoration: line-through;
    opacity: 0.6;
}

</style>
