<template>
  <div class="homepage-container">
    <div class="boxcenter">
      <div class="box3top"></div>
      <div class="box3bg">
        <div class="box3content">
          <div class="pdesc">
            <p>
              <em>eMush</em> n'est actuellement pas ouvert au public car the jeu n'est pas terminé et est infesté de <em>Mush !!!</em>
              <br/><br/>
              Si vous êtes intéressés par le projet, n'hésitez pas à nous rejoindre sur les <a href="https://discord.gg/ERc3svy">discord</a> & <a href="https://eternal-twin.net/forum">Forum</a> d'Eternal-Twin.
              Toutes les informations sont disponibles sur <a href="https://eternal-twin.net">Eternal-Twin</a>.
              <br/><br/>
              Concernant la version Alpha, elle sera lancée le 3 Janvier 2021. Le lien sera disponible sur <a href="https://eternal-twin.net">Eternal-Twin</a> & un code sera distribué le jour du lancement (Uniquement aux joueurs qui participent au vaisseau test de l'Alpha. En privé ou sur le <a href="https://discord.gg/ERc3svy">discord</a>.)
              Pour faire partie du vaisseau test alpha, vous aurez besoin d'un compte sur le site <a href="https://eternal-twin.net/">Eternal-Twin</a>. Vous aurez besoin de vous connecter avec votre compte <a href="https://twinoid.com/">Twinoid</a>. Les comptes Hammerfest & Dino-Parc fonctionnent aussi.
              <br/><br/>
              Pour lier son compte Eternal-Twin à votre compte <a href="https://twinoid.com/">Twinoid</a>, vous aurez besoin d'être ami avec <a href="https://twinoid.com/user/38">Demurgos</a> sur <a href="https://twinoid.com/">Twinoid</a>.
              Vous devrez accepter son application qui demande l'accès à toutes les données venant du site Twinoid. Cela permettra de garder l'ancienne progression de tous vos jeux.
              <br/><br/>
              <em>Pour ceux qui voudraient participer à l'Alpha.</em> Pas d'inquiétude ! Il y aura d'autres vaisseaux tests qui seront lancés ultérieurement.
              <br/><br/>
            </p>
          </div>
        </div>
      </div>
      <div class="box3bottom"></div>
    </div>
    <div class="boxcenter">
      <div class="box3top"></div>
      <div class="box3bg">
        <div class="box3content">
          <div class="pdesc">
            <p>
              <em>eMush</em> is not Open to the public right now because the game is unfinished and full of <em>Mush !!!</em>
              <br/><br/>
              If you are interested in the project, feel free to join us on Eternal-Twin <a href="https://discord.gg/ERc3svy">discord</a> or <a href="https://eternal-twin.net/forum">Forum</a>. All the informations are on <a href="https://eternal-twin.net">Eternal-Twin</a>.
              <br/><br/>
              Concerning the Alpha version, it will be launch the 3rd January 2021. A link will be available on <a href="https://eternal-twin.net/">Eternal-Twin</a> website & a code will be provide the launching day (Only to the player who take part of the Alpha Test ship. In Private or on <a href="https://discord.gg/ERc3svy">discord</a>.)
              To take part of this Alpha test ship, you will need an account on <a href="https://eternal-twin.net/">Eternal-Twin</a> website. You will need to connect you from your <a href="https://twinoid.com/">Twinoid</a> account. Hammerfest or Dino-Parc account work also.
              <br/><br/>
              With <a href="https://twinoid.com/">Twinoid</a>, to link your account to your <a href="https://eternal-twin.net/">Eternal-Twin</a> account, you will need to be friend with <a href="https://twinoid.com/user/38">Demurgos</a> on <a href="https://twinoid.com/">Twinoid</a>.
              You will need to accept his app who request access to all data coming from twinoid. It will allow to keep the progress of all your games.
              <br/><br/>
              <em>For thoses who would like to participate to the Alpha State of the game.</em> No worries ! There will be other test ship who will be launched later.
              <br/><br/>
            </p>
          </div>
        </div>
      </div>
      <div class="box3bottom"></div>
    </div>
  </div>
</template>

<script>
export default {
  name: "HomePage"
}
</script>

<style scoped>

.homepage-container {
  display: flex;
  flex-direction: row;
  justify-content: center;
}
.box3content em {
  color: #01c3df;
  font-size: 15pt;
  font-style: normal;
  font-weight: bold;
}

a {
  color: #84E100;
}

a:hover {
  color: #e9ebf3;
}

a:visited {
  color: #84E100;
}

.boxcenter {
  margin-bottom: 20px;
  margin-right: 20px;
  position: relative;
  float: left;
  z-index: 2;
}

.box3top {
  vertical-align: top;
  width: 462px;
  background: transparent url("~@/assets/images/box3top.png") no-repeat center top;
  padding-top: 60px;
  z-index: -1000;
  position: relative;
}

.box3top {
  margin: auto;
  margin-top: 20px;
  width: 100%;
  text-align: center;
}

.box3bg {
  padding: 1px 0px 10px 0px;
}

.box3bg {
  background: transparent url("~@/assets/images/box3bg.png") repeat-y center top;
  width: 462px;
  margin: auto;
  z-index: 0;
  flex-grow: 1;
}

.box3content {
  margin-top: -50px;
  margin-bottom: -50px;
  width: 460px;
  position: relative;
  z-index: 10;
}

.pdesc {
  width: 435px;
  padding-top: 20px;
  padding-left: 5px;
  padding-right: 5px;
  padding-bottom: 20px;
  text-align: justify;
  position: relative;
  z-index: 20;
}

.box3content p {
  margin-left: 15px;
}

p {
  text-shadow: -1px 0 2px rgba(0, 0, 0, 0.5), 0 1px 2px rgba(0, 0, 0, 0.5), 1px 0 2px rgba(0, 0, 0, 0.5);
}

p {
  font-size: 11pt;
  margin-bottom: 6px;
}

body,
p,
ul,
ol,
li,
table,
tr,
td,
th,
h1,
h2,
h3,
dl,
dt,
dd,
img,
textarea {
  border: 0px;
  padding: 0px;
  margin: 0px;
  padding: 0px;
}

.box3bottom {
  background: transparent url("~@/assets/images/box3bottom.png") no-repeat center bottom;
  padding-bottom: 60px;
  width: 462px;
  margin: auto;
  position: relative;
  z-index: -1000;
}
</style>