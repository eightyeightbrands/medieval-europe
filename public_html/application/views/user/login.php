<div class="title" style="margin-bottom:5px"><?php echo kohana::lang('user.login_pagetitle'); ?></div>
<p class='right'><?php echo html::anchor('page/index',Kohana::lang('page-homepage.goto-homepage')); ?></p>

<br/>

<?php echo form::open('user/login') ?>

<?php echo form::label('username', Kohana::lang('global.username') );?>
<?php echo form::input( array( 'name'=>'username', 'value' => null, 'id'=>'username' , 'style' => 'margin-left:10px') ); ?>

<?php echo form::label('password', Kohana::lang('global.password'));?>
<?php echo form::password( array( 'name'=>'password', 'value' => null, 'id'=>'password' , 'style' => 'margin-left:10px') ); ?>

<?php echo form::submit( array('id' => 'submit',  'class' => 'submit'), kohana::lang('user.login_submit')); ?>

<?php echo form::close(); ?>

<div style="margin-top:10px">
<?php echo html::anchor( "/user/resendpassword", kohana::lang('user.login_resendpassword' ) ); ?><br/>
<?php echo html::anchor( "/user/resendvalidationtoken", kohana::lang('user.resendvalidationtoken_pagetitle' ) ); ?>
</div>


<br style="clear:both;" />
