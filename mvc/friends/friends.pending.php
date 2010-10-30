<?php 

function friends_pending_rowcallback($ds)
{
	return;
	$rowid = 'row_'.$ds['_key'];
	?><div id="<?= $rowid ?>">
    <input type="button" value="Ignore" onclick="friend_ignore('<?= $rowid ?>', <?= $ds['_key'] ?>);"/>
    <input type="button" value="Accept" onclick="friend_accept('<?= $rowid ?>', <?= $ds['_key'] ?>);"/>
		<span id="<?= $rowid ?>_status"></span>
	</div><?
}

print('<div class="balloonhelp">'.$this->l10n('pending.balloon').'</div>');

include_once('templates/friendlist.php');
tmpl_friendlist($this->model->getFriends('req.sent'), 'friends_pending_rowcallback');

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
    $.post('<?= actionUrl('ajax_accept', 'friends') ?>', {'key' : dskey}, function(data) {
      $('#'+rowid).html(data);
      });
	}
	
</script>