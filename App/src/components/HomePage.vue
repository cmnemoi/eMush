<template>
    <div class="homepage-container">
        <div class="box">
            <img src="@/assets/images/lang_fr.png" alt="Francais">
            <p>
                <em>eMush</em> n'est actuellement pas ouvert au public car le jeu n'est pas terminé et est infesté de <em class="red">Mush</em> <em>!!!</em>
                <br><br>
                Si vous êtes intéressés par le projet, n'hésitez pas à nous rejoindre sur <a href="https://discord.gg/ERc3svy">Discord</a> et sur les <a href="https://eternal-twin.net/forum">forums d'Eternal-Twin</a>. Toutes les informations sont disponibles sur <a href="https://eternal-twin.net">Eternal-Twin</a>.
                <br><br>
                Concernant eMush alpha, des vaisseaux privés sont lancés régulièrement avec des volontaires sur <a href="https://discord.gg/ERc3svy">Discord</a>. Pour faire partie d'un vaisseau alpha, vous aurez besoin d'un compte <a href="https://eternal-twin.net/">Eternal-Twin</a> et de rejoindre la liste de volontaires sur <a href="https://discord.gg/ERc3svy">Discord</a>.
                <br><br>
                <em>Si vous n'êtes pas sélectionnés pour un vaisseau alpha , pas d'inquiétude !</em> La liste des personnes sélectionnées change pour chaque vaisseau afin de fournir une chance à tous.
            </p>
        </div>
        <div class="box">
            <img src="@/assets/images/lang_en.png" alt="English">
            <p>
                <em>eMush</em> is not currently open to the public as the game is not fully finished and is infested with <em class="red">Mush</em><em>!!!</em>
                <br><br>
                If you are interested in the game, feel free to join us on <a href="https://discord.gg/ERc3svy">Discord</a> and on the <a href="https://eternal-twin.net/forum">Eternal-Twin forums</a>. All information is available on <a href="https://eternal-twin.net">Eternal-Twin</a>.
                <br><br>
                About the eMush alpha, private ships are regularly being launched with the help of volunteers on <a href="https://discord.gg/ERc3svy">Discord</a>. To be part of an alpha ship, you will have to register on <a href="https://eternal-twin.net">Eternal-Twin</a> and join the list of volunteers on <a href="https://discord.gg/ERc3svy">Discord</a>.
                <br><br>
                <em>If you are not shortlisted for an alpha ship, don't worry!</em> The selection list rotates for each ship to offer everyone a chance.
            </p>
        </div>
        <div class="box">
            <img src="@/assets/images/lang_de.png" alt="Deutsch">
            <p>
                <em>eMush</em> ist derzeit nicht für die Öffentlichkeit zugänglich, da das Spiel noch nicht ganz fertig ist und von <em class="red">Mush</em> verseucht ist<em>!!!</em>
                <br><br>
                Wenn du dich für das Spiel interessierst, kannst du uns gerne auf <a href="https://discord.gg/ERc3svy">Discord</a> und in den <a href="https://eternal-twin.net/forum">Eternal-Twin-Foren</a> besuchen. Alle Informationen sind auf <a href="https://eternal-twin.net">Eternal-Twin</a> verfügbar.
                <br><br>
                Was die eMush-Alpha angeht, werden regelmäßig private Schiffe mit Hilfe von Freiwilligen auf <a href="https://discord.gg/ERc3svy">Discord</a> gestartet. Um an einem Alphaschiff teilzunehmen, musst du dich auf <a href="https://eternal-twin.net">Eternal-Twin</a> registrieren und der Liste der Freiwilligen auf <a href="https://discord.gg/ERc3svy">Discord</a> beitreten.
                <br><br>
                <em>Wenn du nicht in die engere Auswahl für ein Alphaschiff kommst, mache dir keine Sorgen!</em> Die Auswahlliste rotiert für jedes Schiff, um jedem eine Chance zu geben.
            </p>
        </div>
        <div class="box">
            <img src="@/assets/images/lang_es.png" alt="Español">
            <p>
                <em>eMush</em> no está abierto al público en este momento ya que el juego no está completamente terminado y está lleno de <em class="red">Mush</em><em>!!!</em>
                <br><br>
                Si estás interesado en el juego, no dudes en unirte a nosotros en <a href="https://discord.gg/ERc3svy">Discord</a> y a los <a href="https://eternal-twin.net/forum">foros de Eternal-Twin</a>. Toda la información está disponible en <a href="https://eternal-twin.net">Eternal-Twin</a>.
                <br><br>
                Acerca del alfa de eMush, las naves privadas se lanzan regularmente con la ayuda de voluntarios en <a href="https://discord.gg/ERc3svy">Discord</a>. Para ser parte de una nave alfa, tendrás que registrarte en <a href="https://eternal-twin.net">Eternal-Twin</a> y unirte a la lista de voluntarios de <a href="https://discord.gg/ERc3svy">Discord</a>.
                <br><br>
                <em>Si no estás preseleccionado para una nave alfa, ¡no te preocupes!</em> La lista de selección rota para cada barco para ofrecer a todos una oportunidad.
            </p>
        </div>
    </div>
</template>

<script>
export default {
    name: "HomePage"
};
</script>

<style lang="scss" scoped>

.homepage-container {
    flex-flow: row wrap;
    justify-content: center;
    align-self: center;
    max-width: 1280px;
    margin: 1em 3vw;

    .box {
        position: relative;
        max-width: calc(640px - 2em);
        margin: 3em 1em;
        padding: 1em 1.5em;

        @include corner-bezel(18.5px);

        box-shadow: inset 0 0 35px 25px rgba(15, 89, 171, 0.5);
        background-color: rgba(34, 38, 102, 0.5);
    }

    p {
        text-align: justify;
        font-size: 15px;
        line-height: 1.4em;
    }

    img {
        width: auto;
        height: auto;
        margin: 0.6em auto .1em;
        padding: .6em;
        border-radius: .4em;
        background-color: rgba(15, 15, 67, .5);
    }

    a {
        color: #84e100;

        &:hover { color: #e9ebf3; }
        &:visited { color: #84e100; }
    }

    em {
        color: #01c3df;
        font-size: 1.2em;
        font-style: normal;
        font-weight: bold;

    //    &.red { color: #ff4e64; }
        &.red {
            color: inherit;
            text-decoration: underline;
            text-decoration-color:#ff4e64;
        }
    }
}

</style>
