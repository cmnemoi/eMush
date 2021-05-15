<template>
<li><div class="tooltip">
		14<img src="@/assets/images/fuel.png">
		<div class="tooltiptext">
			<div class="tiptop">
				<div class="tipbottom">
					<div class="tipbg">
						<div class="tipcontent">
							<h1>Fuel en réserve [ 14 / 32 ]</h1><p>Le <strong>fuel</strong> est indispensable pour ne pas dériver dans l'espace jusqu'à la fin des temps. Cette ressource peut être trouvée lors de vos <strong>explorations</strong> sur les planètes. Cet indicateur n'affiche pas le fuel dans la <strong>Chambre de Combustion</strong>.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</li>
</template>

<script>
export default {
    name: "Tooltip"
};
</script>

<style lang="scss" scoped>
/* Tooltip container */
.tooltip {
  position: relative;
  display: inline-block;
  border-bottom: 1px dotted black; /* If you want dots under the hoverable text */
}

/* Tooltip text */
.tooltip .tooltiptext {
  visibility: hidden;
  width: 120px;
  background-color: transparent;
  color: #fff;
  text-align: center;
  padding: 5px 0;
  border-radius: 6px;
 
  /* Position the tooltip text - see examples below! */
  position: absolute;
  z-index: 10;
}

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
  visibility: visible;
}
.tiptop {
	background-image : url('~@/assets/images/tiptop.png');
	background-repeat : no-repeat;
	background-position : left top;
	padding-top : 16px;
	padding-bottom : 1px;
	width : 263px;
}
.tiptop .tipbottom {
	background : transparent url('~@/assets/images/tipbottom.png') no-repeat left bottom;
	padding-bottom : 16px;
}
.tiptop .tipbottom .tipbg {
	background : transparent url('~@/assets/images/tipbg.png') repeat-y left top;
	padding : 1px;
	padding-left : 10px;
	padding-right : 10px;
	font-size : 9pt;
	min-height : 25px;;
}
.tiptop .tipbottom .tipbg .tipcontent {
	margin-top : -10px;
	margin-bottom : -10px;
	line-height : 13pt;
	font-size : 9pt;
	padding-right : 14px;
    text-align: left;
}
.tiptop .tipbottom .tipbg h1 {
	padding-bottom : 2px;
	font-size : 10pt;
	padding-left : 16px;
	margin-bottom : 3px;
	font-variant : small-caps;
	background : transparent url('~@/assets/images/notes.gif') no-repeat left top;
}
.tiptop .tipbottom .tipbg h1 img {
	vertical-align : -25%;
}
.tiptop .tipbottom .tipbg img {
	vertical-align : middle;
}
.tiptop .tipbottom .tipbg p {
	margin-bottom : 10px;
}
.tiptop .tipbottom .tipbg strong {
	color : #01c3df;
}
.tiptop .tipbottom .tipbg em {
	color : #ff4059;
}
.tiptop .tipbottom .tipbg ul {
	font-style : italic;
	margin-top : 10px;
}
.tiptop .tipbottom .tipbg ul li {
	margin-left : 10px;
	margin-bottom : 15px;
	padding-left : 18px;
	background : transparent url('~@/assets/images/point.png') no-repeat 5px 6px;
}
.tiptop .tipbottom .tipbg ul li li {
	background-image : url('~@/assets/images/point2.png');
}
.tiptop .tipbottom .tipbg .once_skill {
	float : left;
	margin-right : 3px;
}
</style>



