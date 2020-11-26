<template>
  <div class="main">
    <h1>Select a character</h1>
    <ul class="char-selection">
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/jin_su.png"></div>
        <span>Jin Su</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/frieda.png"></div>
        <span>Frieda</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/kuan_ti.png"></div>
        <span>Kuan Ti</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/janice.png"></div>
        <span>Janice</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/roland.png"></div>
        <span>Roland</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/hua.png"></div>
        <span>Hua</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/paola.png"></div>
        <span>paola</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/chao.png"></div>
        <span>Chao</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/finola.png"></div>
        <span>Finola</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/stephen.png"></div>
        <span>Stephen</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/ian.png"></div>
        <span>Ian</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/chun.png"></div>
        <span>Chun</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/raluca.png"></div>
        <span>Raluca</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/gioele.png"></div>
        <span>Gioele</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/eleesha.png"></div>
        <span>Eleesha</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/terrence.png"></div>
        <span>Terrence</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/derek.png"></div>
        <span>Derek</span>
      </a></li>
      <li><a href="/#">
        <div><img src="@/assets/images/char/body/andie.png"></div>
        <span>Andie</span>
      </a></li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'CharSelection',
  props: {
  }
}
</script>

<style lang="scss" scoped>

h1 {
  margin: 15px;
  font-size: 150%;
  font-variant: small-caps;
}

.char-selection {
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: space-evenly;

  & li a {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    min-width: 160px;
    padding: 12px;
    margin: 4px;
    color: white;
    font-size: .91em;
    font-weight: 700;
    letter-spacing: .03em;
    text-transform: uppercase;
    text-decoration: none;
    background: rgba(54, 76, 148, 0.35);
    @include corner-bezel(12px, 0px);

    & > div {

      position: relative;
      align-items: center;
      margin-right: 16px;
      & img { z-index: 2; }

      &::after {
        content: "";
        position: relative;
        left: 1px;
        bottom: 8px;
        width: 39px;
        height: 21px;
        background: url("~@/assets/images/figure.png") center no-repeat;
      }
    }

    &:hover, &:active { background: rgba(54, 76, 148, 0.65); }

  }
}

</style>



