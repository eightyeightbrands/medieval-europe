<div class ="submenutabs">

<ul>
<li>
<? $status = $this->uri->segment(3);?>
<? if ($status == 'new' or $status == '' ) $class='button selected'; else	$class='button'; ?>
<?php echo html::anchor('suggestion/index/new', kohana::lang('suggestions.new'), array( 'class' => $class ) ); ?>
</li>

<li>
<? if ($status == 'fundable') $class='button selected'; else $class='button'; ?>
<?php echo html::anchor('suggestion/index/fundable', kohana::lang('suggestions.fundable'), array( 'class' => $class ) ); ?>
</li>
<li>
<? if ($status == 'funded') $class='button selected'; else	$class='button'; ?>	
<?php echo html::anchor('suggestion/index/funded', kohana::lang('suggestions.funded'), array( 'class' => $class ) ); ?>
</li>
<li>
<? if ($status == 'completed') $class='button selected'; else	$class='button'; ?>	
<?php echo html::anchor('suggestion/index/completed', kohana::lang('suggestions.completed'), array( 'class' => $class ) ); ?>
</li>
</ul>	
</div>
