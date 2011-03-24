<?php 

function friends_rcv_rowcallback($ds)
{
  $rowid = 'row_'.$ds['_key'];
  ?><div id="<?= $rowid ?>">
    <input type="button" value="<?= l10n('ignore') ?>" onclick="friend_ignore('<?= $rowid ?>', <?= $ds['_key'] ?>);"/>
    <input type="button" value="<?= l10n('accept') ?>" onclick="friend_accept('<?= $rowid ?>', <?= $ds['_key'] ?>);"/>
    <span class="smalltext">into</span>
    <select id="group_select_<?= $rowid ?>">
      <?= implode('', $GLOBALS['group.options']) ?>
    </select>
    <span id="<?= $rowid ?>_status"></span>
  </div><?
}

print('<div class="balloonhelp">'.l10n('rcv.balloon').'</div>');

$GLOBALS['group.options'] = array('<option value="0">Select a group:</option>');
foreach($this->model->getMyGroups() as $grp)
  $GLOBALS['group.options'][] = '<option value="'.$grp['lg_key'].'">'.$grp['lg_name'].'</option>';

include_once('templates/friendlist.php');
tmpl_friendlist($this->model->getFriends('req.rcv'), 'friends_rcv_rowcallback');

?><script>
  
  function status_indicator(rowid, status)
  {
    if(status == null) status = '<img src="themes/default/ajax-loader.gif" align="absmiddle"/>';
    $('#'+rowid+'_status').html(status);
  }
  
  function friend_ignore(rowid, dskey)
  {
    status_indicator(rowid);
    $.post('<?= actionUrl('ajax_ignore', 'friends') ?>', {'key' : dskey}, function(data) {
      $('#'+rowid).html(data);
      });
  }
  
  function friend_accept(rowid, dskey)
  {
    status_indicator(rowid);
    $.post('<?= actionUrl('ajax_accept', 'friends') ?>', {'key' : dskey, 'group' : $('#group_select_'+rowid+' option:selected').val() }, function(data) {
      $('#'+rowid).html(data);
      });
  }
  
</script>