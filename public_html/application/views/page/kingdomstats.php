<style>
.legend table
{
width:auto;
}

table#itemdata
{
width:75%;
font-size:11px;
cellspacing:4px;
}

table#itemdata th
{
border:1px solid #000;
font-weight:bold;
}

table#itemdata td
{
border:1px solid #000;
padding:0px 2px;
vertical-align:top;
}
</style>

<head>
<?php echo html::script('media/js/jquery/plugins/flot/jquery.flot.js', FALSE); ?>

<script type="text/javascript">

$(document).ready
(
function()
{

$("#kingdoms").change(function()
	{									
		$.ajax( //ajax request starting
		{
		url: '<?php echo url::base(true) ?>' + 'page/kingdomstats/', 
		type:"POST",
		data: { id: $("#kingdoms").val() },
		success: 
		function(data) 
			{																							
				onDataReceived(data);
			}
		}	
		);		
		return false;
	});

function showTooltip(x, y, contents) {
		$('<div id="tooltip">' + contents + '</div>').css( {
				position: 'absolute',
				display: 'none',
				top: y + 5,
				left: x + 5,
				border: '1px solid #fdd',
				padding: '2px',
				'background-color': '#fee',
				opacity: 0.80
		}).appendTo("body").fadeIn(200);
}
	
function onDataReceived(series) {

	var m = JSON.parse( series );	 
	var d1 = [];
	var d2 = [];
	var d3 = [];
	var d4 = [];
	var d5 = [];
	var d6 = [];
	
	
	var maxdate = new Date();
	var mindate = new Date();
	mindate.setFullYear( mindate.getFullYear() - 1 );

		// stampa grafici
	r = Math.random();

	for( var x=0; x < m.data.length; x++)
	{
		
		if ( m.data[x].name == 'kingdomownedregions' )
			d1.push([m.data[x].period*1000, m.data[x].param1, m.data[x].param2 ]);			
		
		if ( m.data[x].name == 'kingdomheritage' )
			d2.push([m.data[x].period*1000, m.data[x].param1 * r, m.data[x].param2 ]);		
		if ( m.data[x].name == 'kingdomavgheritage' )
			d3.push([m.data[x].period*1000, m.data[x].param1 * r, m.data[x].param2 ]);	
		
		if ( m.data[x].name == 'kingdompopulation' )
			d4.push([m.data[x].period*1000, m.data[x].param1, m.data[x].param2 ]);	
		
		if ( m.data[x].name == 'kingdomtotalbattles' )
			d5.push([m.data[x].period*1000, m.data[x].param1, m.data[x].param2 ]);	
		if ( m.data[x].name == 'kingdomtotalwonbattles' )
			d6.push([m.data[x].period*1000, m.data[x].param1, m.data[x].param2 ]);	
	}

	$.plot($("#graph1"),  
	[
		{ label: "<?php echo kohana::lang('page.stats_ownedregions');?>",  data: d1},
	]	, { 
	 lines: { show: true },
	 series: {    
    points: {
			show: false,
      symbol: "circle"
    },
		
   },
	grid: { hoverable: true },
	legend: { backgroundOpacity: 0 },
	xaxis: { 
		mode: "time", 
		minTickSize: [1, "month"],
		min: mindate.getTime(),
		max: maxdate.getTime()} ,
	yaxis: { tickDecimals: 0 }	
	});
	
	$.plot($("#graph2"),  
	[
		{ label: "<?php echo kohana::lang('page.stats_kingdomheritage');?>",  data: d2},		
	]	, { 
	 lines: { show: true },
	 series: {    
    points: {
			show: false,
      symbol: "circle"
    }
   },
	grid: { hoverable: true },
	legend: { backgroundOpacity: 0 },
	xaxis: { 
		mode: "time", 
		minTickSize: [1, "month"],
		min: mindate.getTime(),
		max: maxdate.getTime()},
	yaxis: { tickDecimals: 0 }	
	});
	
	$.plot($("#graph3"),  
	[
		{ label: "<?php echo kohana::lang('page.stats_kingdomavgheritage');?>",  data: d3},
	]	, { 
	 lines: { show: true },
	 series: {    
    points: {
			show: false,
      symbol: "circle"
    }
   },
	grid: { hoverable: true }, 
	legend: { backgroundOpacity: 0 },
	xaxis: { mode: "time", 
		minTickSize: [1, "month"],
		min: mindate.getTime(),
		max: maxdate.getTime()} ,
	yaxis: { tickDecimals: 0 }	
	});
	
	$.plot($("#graph4"),  
	[
		{ label: "<?php echo kohana::lang('page.stats_kingdompopulation');?>",  data: d4},
	]	, { 
	 lines: { show: true },
	 series: {    
    points: {
			show: false,
      symbol: "circle"
    }
   },
	grid: { hoverable: true },
	legend: { backgroundOpacity: 0 },
	xaxis: { mode: "time", 
		minTickSize: [1, "month"],
		min: mindate.getTime(),
		max: maxdate.getTime()} ,
	yaxis: { tickDecimals: 0 }	
	});

	$.plot($("#graph5"),  
	[
		{ label: "<?php echo kohana::lang('page.stats_totalbattles');?>",  data: d5},
		{ label: "<?php echo kohana::lang('page.stats_wonbattles');?>",  data: d6},
	]	, { 
	 lines: { show: true },
	 series: {    
    points: {
			show: false,
      symbol: "circle"
    }
   },
	grid: { hoverable: true },
	legend: { backgroundOpacity: 0 },
	xaxis: { mode: "time", 
		minTickSize: [1, "month"],
		min: mindate.getTime(),
		max: maxdate.getTime()} ,
	yaxis: { tickDecimals: 0 }	
	});
	
}	

var previousPoint = null;
$("#graph1, #graph2, #graph3, #graph4, #graph5").bind("plothover", function (event, pos, item) {
    // axis coordinates for other axes, if present, are in pos.x2, pos.x3, ...
    // if you need global screen coordinates, they are pos.pageX, pos.pageY

		if (item) {        				
			if (previousPoint != item.dataIndex) {
				previousPoint = item.dataIndex;
				$("#tooltip").remove();
				showTooltip(item.pageX, item.pageY,item.series.data[item.dataIndex][2]);
			}
		}
		else {
			$("#tooltip").remove();
			previousPoint = null;            
    }
});

$('#kingdoms').trigger('change');

});
</script>

</head>

<div class="pagetitle"><?php echo kohana::lang('page.stats_kingdomstats') ?></div>

<?php
$mode = 'all';
$centeronplayer = ( $mode == 'all' ) ? true : false;
?>

<ul class="dropdown">
	<li>
	<a href="#"><?php echo kohana::lang('global.churches')?></a>
		<ul class="sub_menu">
			 <li>
			 <?php echo html::anchor('page/rankings/church/mostfollowedchurch/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.mostfollowedchurch')); ?> </li>
		</ul>
	</li>
	<li>
		<a href="#"><?php echo kohana::lang('global.kingdoms')?></a>
		<ul class="sub_menu">
			 <li><?php echo html::anchor('page/rankings/kingdom/richestkingdoms/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.richestkingdoms')); ?> </li>
			 <li><?php echo html::anchor('page/rankings/kingdom/populatedkingdoms/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.populatedkingdoms')); ?> </li>
			 <li><?php echo html::anchor('page/rankings/kingdom/raiderskingdoms/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.raiderskingdoms'));  ?> </li>
			 <li><?php echo html::anchor('page/rankings/kingdom/raidedkingdoms/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.raidedkingdoms'));  ?> </li>			 
			 <li><?php echo html::anchor('page/rankings/kingdom/activekingdoms/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.activekingdoms'));  ?> </li>			 
			 <li><?php echo html::anchor('page/kingdomstats/', kohana::lang('rankings.ranking_kingdomstats'));  ?> </li>			 
		</ul>
	</li>
	<li>
	<a href="#"><?php echo kohana::lang('global.regions')?></a>
		<ul class="sub_menu">
		 <li><?php echo html::anchor('page/rankings/region/richestcities/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.richestcities')); ?> </li>
		 <li><?php echo html::anchor('page/rankings/region/populatedcities/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.populatedcities')); ?> </li>
		</ul>
	</li>
	<li>
	<a href="#"><?php echo kohana::lang('global.characters')?></a>
		<ul class="sub_menu">
			<li><?php echo html::anchor('page/rankings/char/gamescore/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.gamescore'));  ?></li>
			 <li><?php echo html::anchor('page/rankings/char/richestchars/' . $mode  . '/' . $centeronplayer, kohana::lang('rankings.richestchars'));?></li>
			 <li><?php echo html::anchor('page/rankings/char/oldestchars/' . $mode  . '/' . $centeronplayer,   kohana::lang('rankings.oldestchars'));?></li>
			 <li><?php echo html::anchor('page/rankings/char/fightstats/' . $mode  . '/' . $centeronplayer,   kohana::lang('rankings.fightstats'));?></li>
			 <li><?php echo html::anchor('page/rankings/char/bestduelist/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.bestduelist'));  ?></li>
			 <li><?php echo html::anchor('page/rankings/char/honorpoints/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.honorpoints'));  ?></li>
			 <li><?php echo html::anchor('page/rankings/char/battlechampion/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.battlechampion'));?></li>
			 <li><?php echo html::anchor('page/rankings/char/arrests/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.arrests'));?></li>
			 <li><?php echo html::anchor('page/rankings/char/boughtdoubloons/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.mesupporters')); ?></li>
			 <li><?php echo html::anchor('page/rankings/char/fpcontribution/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.fpcontribution'));  ?></li>
			 <li><?php echo html::anchor('page/rankings/char/mostcharitable/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.mostcharitable'));  ?></li>
			  
		</ul>
	</li>
</ul>

<br/><br/>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br style='clear:both'/>

<?php
echo form::open();
echo form::label( kohana::lang('global.kingdom')) . '&nbsp;' . 	form::dropdown( 'kingdoms', $kingdoms ,'standard');
echo form::close();
?>

<h5><?php echo kohana::lang('page.stats_ownedregions') ?></h5>
<div id="graph1" style="width:700px;height:150px;margin-top:5px"></div>
<br/>
King: <div id='graph1king'></div>
<h5><?php echo kohana::lang('page.stats_kingdomheritage') ?></h5>

<div id="graph2" style="width:700px;height:150px;margin-top:5px"></div>
<br/>
<div class='boxevidence'><?php echo kohana::lang('page.stats_kingdomheritagenote') ?></div>
<br/>
<h5><?php echo kohana::lang('page.stats_kingdomavgheritage') ?></h5>
<div id="graph3" style="width:700px;height:150px;margin-top:5px"></div>
<br/>
<div class='boxevidence'><?php echo kohana::lang('page.stats_kingdomheritagenote') ?></div>
<h5><?php echo kohana::lang('page.stats_kingdompopulation') ?></h5>
<div id="graph4" style="width:700px;height:150px;margin-top:5px"></div>
<br/>
<h5><?php echo kohana::lang('page.stats_battles') ?></h5>
<div id="graph5" style="width:700px;height:150px;margin-top:5px"></div>

<br style='clear:both'/>
