<template>
<div class="ship-panel">
  <div class="ship-view"></div> <!-- PLACEHOLDER -->
  <p class="room">Laboratory</p>
  <div class="map-container">
    <div class="map">
      <img src="@/assets/images/shipmap.svg">
      <ul class="crew-position">
        <li class="self"></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
      </ul>
    </div>
  </div>
</div>

</template>

<script>
export default {
  name: "ShipPanel",
  props: {
  }
}
</script>

<style lang="scss" scoped>

.ship-panel {
    position: relative;
    flex-direction: row;
    width: 424px;
    height: 460px;

  & .ship-view {
    position: absolute;
    width: 100%;
    height: 100%;
    background: transparentize(#C2F3FC, .6);
    @include corner-bezel(6.5px, 6.5px, 0px);
  }

  & .room {
    position: absolute;
    z-index: 5;
    margin: 8px 16px;
    font-family: 'Pixel-Square';
    font-size: 10px;
    font-weight: 700;
  }

  & .map-container {
    align-self: flex-end;
    z-index: 5;
    bottom: 200px;
    width: 240px;
    height: 200px;
    background: #070724;
    @include corner-bezel(0px, 18px, 0px);

    transform: scale(.5);
    transform-origin: bottom left;

    transition: transform .5s;

    &:hover { transform: scale(1); transition: transform .6s .2s; }

    & .map {
      width: 184px;
      height: 96px;
      margin: auto;
      transform: rotate(30deg);
    }

    & .crew-position {
      position:absolute;

      li {
        position: absolute;
        width: 4px;
        height: 4px;
        background: #FF8888;

        @for $i from 1 through 16 { // randomize crew position, for testing only
          &:nth-child(#{$i}) {
            left: random(156) + 10px;
            top: random(52) + 18px;
          }
        }

        &.self {
          z-index: 2;
          width: 6px;
          height: 6px;
          background: #889C28;

          animation: self-position-color 1.1s infinite;
        }
      }
    }
  }
}



@keyframes self-position-color {
  0%   {background: #ffff00;}
  39%  {background: #ffff00;}
  40%  {background: #889C28;}
  100% {background: #889C28;}
}

</style>