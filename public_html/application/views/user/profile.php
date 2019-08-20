<div class="pagetitle"><?php echo kohana::lang('user.account_pagetitle' ) ?></div>

<?php echo $submenu ?>

<br/>

<table style='width:80%;margin:auto'>
<tr class='alternaterow_1'><td class="label" width="30%"><?php echo kohana::lang('global.username' ) ?></td><td width="70%" class="value"><?php echo $user->username?></td></tr>
<tr><td class="label" width="30%"><?php echo kohana::lang('global.status' ) ?></td><td width="70%" class="value"><?php echo $user->status?></td></tr>
<tr class='alternaterow_1'><td class="label" width="30%"><?php echo kohana::lang('global.email' ) ?></td><td width="70%"  class="value"><?php echo $user->email?></td></tr>
<tr><td class="label" width="30%"><?php echo kohana::lang('global.createddate' ) ?></td><td width="70%"  class="value"><?php echo date("d/m/Y:H:i:s", $user->created)?></td></tr>
<tr class='alternaterow_1'><td class="label" width="30%"><?php echo kohana::lang('global.last_login' ) ?></td><td width="70%"  class="value"><?php echo date("d/m/Y:H:i:s", $user->last_login)?></td></tr> 
<tr><td class="label" width="30%"><?php echo kohana::lang('global.ipaddress' ) ?></td><td width="70%"  class="value"><?php echo $user->ipaddress?></td></tr> 
</table>

<br style="clear:both">
