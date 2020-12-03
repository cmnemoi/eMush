<template>
<div class="chatbox-container" id="discussion-tab">
  <textarea class="chat-input" placeholder="Type your message here!"></textarea>
  <div class="chatbox">
    <div class="unit">
      <div class="main-message">
        <img src="@/assets/images/char/body/jin_su.png">
        <p><span class="author">Jin Su :</span>It would be nice to set up BIOS. Any IT Expert up to it?</p>
        <span class="timestamp">moments ago</span>
        
      </div>
      <div>
        <p><img src="@/assets/images/char/head/jin_su.png"><span class="author">Jin Su :</span><strong><em>Share BIOS parameters</em></strong><br />
          <strong>Foodstuff Destruction</strong> : No<br />
          <strong>CPU Priority</strong> : Astronavigation<br />
          <strong>Crew lock</strong> : Piloting<br />
          <strong>Vocoder Announcements</strong> : Unauthorized<br />
          <strong>Report Deaths</strong> : Yes<br />
          <strong>Inhibit DMZ-CorePeace</strong> : Yes<br />
          <em>Find us an O2 planet!</em>
        </p>
        <span class="timestamp">moments ago</span>
        
      </div>
    </div>

    <div class="unit">
      <div class="main-message neron new">
        <img src="@/assets/images/comms/neron_chat.png">
        <p><span class="author">NERON :</span><strong>Gravity Simulator</strong> engaged, nice. <em>Move Humans more fastly</em>. You not get dirt everywhere! [Hax!]</p>
        <span class="timestamp">25min</span>
      
      </div>
      <div class="new">
        <p><img src="@/assets/images/char/head/hua.png"><span class="author">Hua :</span>Dirty dismantling the shower.</p>
        <span class="timestamp">~1h</span>
      </div>
      <div class="new">
        <p><img src="@/assets/images/char/head/paola.png"><span class="author">Paola :</span>Thanks Hua, got my sofa ready on the bridge now :)</p>
        <span class="timestamp">~1h</span>
      </div>
    </div>

    <div class="unit">
      <div class="main-message new">
        <img src="@/assets/images/char/body/ian.png">
        <p><span class="author">Ian :</span><strong><em>Piloting</em></strong></p>
        <span class="timestamp">~5d</span>
      </div>
      <div>
        <p><img src="@/assets/images/char/head/jin_su.png"><span class="author">Jin Su :</span>So far eight hunters shot total (3 + 5), no scrap collected yet.</p>
        <span class="timestamp">~3d</span>
      </div>
      <div>
        <p><img src="@/assets/images/char/head/ian.png"><span class="author">Ian :</span>Excellent sir, I can see why they have you training the new pilots :P</p>
        <span class="timestamp">~3d</span>
      </div>
      <div>
        <p><img src="@/assets/images/char/head/jin_su.png"><span class="author">Jin Su :</span>Kind of you to say so, yet I sadly can't agree. In fact I find our hull's exellency wanting. It shall be restored once we collected scrap and built the oscilloscope.</p>
        <span class="timestamp">~1d</span>
      </div>
      <div>
        <p><img src="@/assets/images/char/head/jin_su.png"><span class="author">Jin Su :</span>Scrap collected, still a bit left.</p>
        <span class="timestamp">~1d</span>
      </div>
      <div>
        <p><img src="@/assets/images/char/head/paola.png"><span class="author">Paola :</span>This topic is discriminating, I vote for a new one simply labeled "hunters" or "attackers"</p>
        <span class="timestamp">~1d</span>
      </div>
    </div>
  </div>
</div>
</template>

<script>
export default {
  name: "DiscussionTab",
  props: {
  }
}
</script>

<style lang="scss" scoped>

#discussion-input:checked ~ .tabs-content #discussion-tab {
  display: flex;
  visibility: visible;
}

#discussion-tab {
  position: relative;
  z-index: 2;
  height: 435px;
  font-size: .8em;
  line-height: initial;
  background: rgba(194, 243, 252, 1);
  @include corner-bezel(0px, 6.5px, 0px);

  display: none;
  visibility: hidden;

  & textarea {
    position: sticky;
    resize: vertical;
    min-height: 29px;
    margin: 7px 7px 4px 7px;
    padding: 3px 5px;
    font-style: italic;
    opacity: .85;

    box-shadow: 0px 1px 0px white;
    border: 1px solid #aad4e5;
    border-radius: 3px;

    &:active, &:focus {
      min-height: 48px;
      max-height: 80%;
      font-style: initial;
      opacity: 1;
    }
  }

  & .chatbox {
    overflow: auto;
    padding: 7px;
    color: #090a61;

    & .unit {
      display: block;
      align-items: flex-end;
      padding: 5px 0;
      border-bottom: 1px solid rgb(170, 212, 229);

      & div {
        position: relative;
        display: block;
        clear: both;

        & p:not(.timestamp) {
          position:relative;
          margin: 3px 0;
          padding: 4px 6px;
          border-radius: 3px;
          background: white;

          & .author {
            color: #2081e2;
            font-weight: 700;
            font-variant: small-caps;
            padding-right: .25em; 
          }

          & em {color: #cf1830;}
        }

        & .timestamp {
          position: absolute;
          z-index: 2;
          right: 5px;
          bottom: 5px;
          font-size: .85em;
          font-style: italic;
          opacity: .5;
          float: right;
        }

        &.new:not(.neron) p, &.new.neron {
          border-left: 2px solid #EA9104;

          &::after {
            content:"";
            position: absolute;
            top: 7px;
            left: -6px;
            height: 11px;
            width: 11px;
            background: transparent url('~@/assets/images/comms/thinklinked.png') center no-repeat;
          }
        }

        &.main-message {
          & img { margin: 2px; float: left; }

          & p { margin-left: 36px; min-height: 52px; }

          & p::before { /* bubble triangle*/
            $size: 8px;
            content:"";
            position: absolute;
            top: 4px;
            left: -$size;
            width: 0px;
            height: 0px;
            border-top: $size solid transparent;
            border-bottom: $size solid transparent;
            border-right: $size solid white;
          }

          &.new p {
            &::before { border-right-color: #EA9104 }
            &::after { top: 22px; }
          }
        }

        &.neron {
          padding: 2px 4px;
          border-radius: 4px;
          background: #74CBF3;

          & p {
            min-height: 36px;
            padding: 0 0 0 8px;
            font-variant: small-caps;
            color: inherit;
            background: transparent;
            & .author{ color: inherit; }
            &::before { content: none; }
            &::after { top: 8px;}
          }

          &::after { top: 12px !important; }
        }

        &:not(.main-message) {
          margin-left: 50px;
          & img { margin-right: 3px; }
          & p { margin-top: 12px; }

          & p::before { /* bubble triangle*/
            $size: 8px;
            content:"";
            position: absolute;
            top: -$size;
            left: 4px ;
            width: 0px;
            height: 0px;
            border-left: $size solid transparent;
            border-right: $size solid transparent;
            border-bottom: $size solid white;
          }

/* MESSAGES LINKTREE */

          &::before {
            --border-radius: 5px;
            content: "";
            position: absolute;
            top: calc( -12px - var(--border-radius) );
            left: -36px;
            width: calc( 28px + var(--border-radius) );
            height: calc( 26px + var(--border-radius) );
            border-left: 1px solid #aad4e5;
            border-bottom: 1px solid #aad4e5;
            border-radius: var(--border-radius);

            clip-path: polygon(
              0 var(--border-radius),
              calc(100% - var(--border-radius)) var(--border-radius),
              calc(100% - var(--border-radius)) 100%,
              0 100%
            );
          }

          &:not(:last-child)::after {
            --border-radius: 5px;
            content: "";
            position: absolute;
            top: 13px;
            left: -36px;
            width: calc( 28px + var(--border-radius) );
            bottom: calc( -4px - var(--border-radius) );
            border-left: 1px solid #aad4e5;
            border-top: 1px solid #aad4e5;
            border-radius: var(--border-radius);

            clip-path: polygon(
              0 0,
              calc(100% - var(--border-radius)) 0,
              calc(100% - var(--border-radius)) calc(100% - var(--border-radius)),
              0 calc(100% - var(--border-radius))
            );
          }
        }
      }
    }
  }
}

/* SCROLLBAR STYLING */

.chatbox, {
  --scrollbarBG: white;
  --thumbBG: #090a61;

  scrollbar-width: thin;
  scrollbar-color: var(--thumbBG) var(--scrollbarBG);
  &::-webkit-scrollbar { width: 6px; }
  &::-webkit-scrollbar-track { background: var(--scrollbarBG); }
  &::-webkit-scrollbar-thumb { background-color: var(--thumbBG); }
}

</style>