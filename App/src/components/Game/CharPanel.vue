<template>
  <div class="char-panel">

    <div class="char-sheet">
      <img class="avatar" src="@/assets/images/char/stephen_seagull.jpg" alt="avatar">
      <ul class="statuses"></ul>
      <ul class="skills"></ul>

      <div class="health-points">
        <div class="life">
          <p><img src="@/assets/images/lp.png" alt="lp">12</p>
          <ul>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="empty"></li>
            <li class="empty"></li>
          </ul>
            
        </div>
        <div class="morale">
          <p><img src="@/assets/images/moral.png" alt="mp">4</p>
          <ul>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
          </ul>          
        </div>
      </div>
      <ul class="inventory">
        <li class="empty"></li>
        <li class="empty"></li>
        <li class="empty"></li>
      </ul>
      <div class="interactions">
        <p>Schrödinger :</p>
        <ul>
          <li><a href="#">Lâcher</a></li>
          <li><a href="#">Examiner</a></li>
          <li><a href="#">1 <img src="@/assets/images/pa.png" alt="ap"> Caresser</a></li>
          <li class="crossed"><a href="#">1 <img src="@/assets/images/pa.png" alt="ap"> Câlin infectieux</a></li>
        </ul>
      </div>
    </div>

    <div class="action-sheet">
      <ul class="interactions"></ul>
      <div class="action-points">
        <div class="actions"></div>
        <div class="movements"></div>
        <div class="specials"></div>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: "CharPanel",
  props: {
  }
}
</script>

<style scoped>
</style>