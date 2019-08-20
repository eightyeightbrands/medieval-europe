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
border:1px solid;
font-weight:bold;
}

table#itemdata td
{
border:1px solid;
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

$("#id").change(function()
	{									
		$.ajax( //ajax request starting
		{
		url: "index.php/market/stats_items", 
		type:"POST",
		data: { structure_id: $("#structure_id").val(), id: $("#id").val() },
		success: 
		function(data) 
			{																							
				onDataReceived(data);
			}
		}	
		);		
		return false;
	});

function onDataReceived(series) {

var m = JSON.parse( series );	 
var d1 = [];
var d2 = [];

var html = '<thead><th><?php echo kohana::lang('global.date') ?></th><th><?php echo kohana::lang('global.total') ?></th><th><?php echo kohana::lang('page.stats_items_avgsoldprice') ?></th></thead><tbody>';
var rowstyle = '';
var avg_tot_sold_price = 0;

$("#itemdata tr").remove();
$('#itemname').html(m.name);
$('#sellableitem').html(m.sellable);

for( var x=0; x < m.data.length; x++)
{
	
	avg_tot_sold_price = avg_tot_sold_price + Number(m.data[x].avg_price);
	min = max = 0;
	
	if ( min >= m.data[x].avg_price )
		min = m.data[x].avg_price;
	
	if ( max <= m.data[x].avg_price )
		max = m.data[x].avg_price;
	
	
	if ( x % 2  == 0 )
		rowstyle='alternaterow_1';
	else
		rowstyle='';
	
	d = new Date(m.data[x].timestamp*1000).toDateString();	
	d1.push([m.data[x].timestamp*1000, m.data[x].total]);			
	d2.push([m.data[x].timestamp*1000, m.data[x].avg_price]);		

	html += "<tr class='" + rowstyle + "'><td>" + d + "</td><td class='right'>" + m.data[x].total + "</td>" + 	
	"<td class='right'>" + m.data[x].avg_price + "</td>" +
	"</tr>";		
}

html += '</tbody>';

$('#averagesoldprice').html('<b>' + (avg_tot_sold_price/x).toFixed(2) + '</b>' );
$('#itemdata').append(html);
$.plot($("#graph1"),  
	[
		{ label: "Total",  data: d1},
	]	, { 
	 lines: { show: true },
	 series: {    
    points: {
			show: true,
      symbol: "circle"
    }
   },
	legend: { backgroundOpacity: 0 },
	xaxis: { mode: "time", minTickSize: [1, "month"] } ,
	yaxis: { tickDecimals: 0 }	
	});
	
	$.plot($("#graph2"),  
	[
		{ label: "Avg Price (deals)",  data: d2, color: "#00cc00"},        
	]	, { 
	 lines: { show: true },
	 series: {    
    points: {
			show: true,
      symbol: "circle",			
    }
   },
	legend: { backgroundOpacity: 0 },
	xaxis: { mode: "time", minTickSize: [1, "month"] } ,
	yaxis: { tickDecimals: 0 }	
	})
	
}	

$('#id').trigger('change');

var previousPoint = null;
$("#graph1").bind("plothover", function (event, pos, item) {
	$("#graph1king").html(pos.x.toFixed(2));

});
 
});
</script>

</head>

<div class="pagetitle"><?php echo kohana::lang('page.stats_items_pagetitle') ?></div>

<?php echo $submenu ?>
<div id='helper'><?php echo kohana::lang('page.stats_items_helper')?></div>
<br/>
<?php
echo form::open('/page/stats_items/', array('id'=>'sendform') );
echo form::label( kohana::lang('global.name')) . '&nbsp;' . 	form::dropdown('id', $items,'standard');
echo " <input type='hidden' name = 'structure_id', value=' " . $structure -> id . "', id = 'structure_id' >"; 
echo form::close();
?>

<br/>

<hr style='border-bottom:0.1px solid #333'/>

<br/>

<h5><?php echo kohana::lang('page.stats_items_header')?><span id='itemname'></span></h5>

<br/>

<p>
<?php echo kohana::lang('page.stats_items_sellableitem') ?>&nbsp;<span style='font-weight:bold' id='sellableitem'></span>
, <?php echo kohana::lang('page.stats_items_totalaveragesold') ?> <span id='averagesoldprice'></span>
</p>

<div id="graph1" style="width:700px;height:150px;"></div>
<div id="graph2" style="width:700px;height:150px;"></div>

<br style='clear:both'/>

<br/>

<center><table id='itemdata'></table></center>

<br style='clear:both'/>

