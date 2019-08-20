<div class="pagetitle">Test - Fight Report</div>

<div class="separator">&nbsp;</div>

<fieldset>
<h5>Massive Battle</h5>
<br/>
<?php echo form::open('/test/testbe');?>
How many fighters? <?php echo form::input( array('name' => 'fighters', 'value' => 5, 'id' => 'fighters', 'size' => 3 ) ) ?>
&nbsp;
Group? <?php echo form::input( array('name' => 'group', 'id' => 'group', 'size' => 30 ) ) ?>
<br/>
Debug <?php echo form::checkbox( array( 'name' => 'debug', 'value' => true, 'checked' => false ) ) ?>
<br/>
<?php echo form::submit( array ('id' => 'submit', 'class' => 'button button-medium' , 'name'=>'fight' ), 'Test_Battle_Engine' );?>
&nbsp;&nbsp;
<?php echo form::submit( array ('id' => 'submit', 'class' => 'button button-medium' , 'name'=>'fight' ), 'Test_Conquer_Ir' );?>
<?php echo form::close()?>
</fieldset>

<br/>

<fieldset>
<h5>Duel</h5>
<?php echo form::open('/test/testbe');?>

Fighter 1: <?php echo form::input( array('name' => 'fighter1' ) ); ?>
<br/>
Fighter 2: <?php echo form::input( array('name' => 'fighter2' ) ); ?>
<br/>
<?php echo form::submit( array ('id' => 'submit', 'class' => 'button button-medium' , 'name'=>'fight' ), 'Test_Duel' );?>
<?php echo form::close()?>
</fieldset>

<br/>

<hr/>

<?php if ( count($report) > 0 )  echo $report ; ?>

<br/> 
