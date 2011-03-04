<?php 

function friends_list_rowcallback($ds)
{
  $rowid = 'row_'.$ds['_key'];
  ?><div id="<?= $rowid ?>">
    <input type="button" value="<?= l10n('remove') ?>" onclick="friend_remove('<?= $rowid ?>', <?= $ds['_key'] ?>);"/>
    <span id="<?= $rowid ?>_status"></span>
  </div><?
}

print('<div class="balloonhelp">'.l10n('index.balloon').'</div>');

include_once('templates/friendlist.php');
tmpl_friendlist($this->model->getFriends('friend'), 'friends_list_rowcallback');

?><script>
  
  function status_indicator(rowid, status)
  {
    if(status == null) status = '<img src="themes/default/ajax-loader.gif" align="absmiddle"/>';
    $('#'+rowid+'_status').html(status);
  }
  
  function friend_remove(rowid, dskey)
  {
    status_indicator(rowid);
    $.post('<?= actionUrl('ajax_remove', 'friends') ?>', {'key' : dskey}, function(data) {
			$('#'+rowid).html('<?= l10n('removed') ?>');
      });
  }
  
</script>