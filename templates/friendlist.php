<?php 

function tmpl_friendlist($list, $rowCallback2 = '')
{
	?><table width="100%"><?php 
	foreach($list as $ds)
	{
    ?><tr class="drow_<?= $ds['_key'] ?>">
      <td colspan="10"><div style="width: 100%; border-top: 1px solid #aaa;"></div></td>
    </tr class="drow_<?= $ds['_key'] ?>"><tr>
      <td valign="top" width="60"><img src="<?= getDefault($ds['pic'], 'img/anonymous.png') ?>" width="48" style="max-height: 48px"/></td>
      <td valign="top" width="50%">
        <div><?= HubbubEntity::link($ds) ?></div>
        <div class="smalltext"><?= htmlspecialchars($ds['url']) ?></div>
      </td>
			<td valign="top">
				<?
				if($rowCallback2 != '') $rowCallback2($ds);
				?>
			</td>
    </tr><?php
	}
  ?></table><?php 
}

?>
