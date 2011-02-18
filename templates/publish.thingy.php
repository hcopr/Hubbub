<?

$attachment_types = array();
h2_execute_event('publish_attachments_register', $attachment_types);

?>
<div id="publisher">
	
	<table width="100%">
		<tr>
      <td rowspan="2" valign="top" width="500">
        <textarea style="width: 500px;" id="publish_text"></textarea>      	
      </td>
      <td valign="top" width="*" colspan="2">
        <input type="button" value="Share" onclick="do_publish();"/>
        <? if(sizeof($attachment_types) > 0) { ?>
        <select style="padding: 4px;" onchange="loadAttachmentEditor($(this).val());">
          <option value="">▼ Attachment</option>
          <?
          foreach($attachment_types as $att)
          {
            ?><option value="<?= md5($att['editor']) ?>"><?= htmlspecialchars($att['caption']) ?></option><? 
          }          
          ?>
        </select>
        <? } ?>
      </td>
		</tr>
		<tr>
      <td valign="middle" colspan="2">
        <span id="status_indicator">&nbsp;</span>
      </td>
    </tr>
	</table>
	
	<div id="publisher_attackments" style="padding-left: 4px">
    &nbsp;
  </div>
	
</div>
<script>
	
	$('#publish_text').autoResize({
	    // On resize:
	    onResize : function() {
	        $(this).css({opacity:0.8});
	    },
	    // After resize:
	    animateCallback : function() {
	        $(this).css({opacity:1});
	    },
	    // Quite slow animation:
	    animateDuration : 300,
	    // More extra space: 
	    extraSpace : 10
	});
	
	function loadAttachmentEditor(editorId)
	{
	  if(editorId == '') 
	    $('#publisher_attackments').html('&nbsp;');
	  else
	  {
	    $('#publisher_attackments').html('<span style="color: gray"><img src="themes/default/ajax-loader.gif" align="absmiddle"> loading...</span>');
      $.post('<?= actionUrl('ajax_loadeditor', 'ui') ?>', {'id' : editorId}, function(data) {
        $('#publisher_attackments').html(data);
        });
    }
  }
	
	function do_publish()
	{
	  if($('#publish_text').val() == '') return;
		$('#publisher').fadeTo('normal', 0.5);
		$('#status_indicator').html('<img src="themes/default/ajax-loader.gif"/>');
		$.post('<?= actionUrl('ajax_post', 'msg') ?>', 
		  {'text' : $('#publish_text').val()<? if($this->viewEntity) print(", 'to' : ".$this->viewEntity) ?> }, function(data) {
			  if(data.result != 'OK')
			  {
			    if(!data.reason) data.reason = '';
			      alert('There was a problem publishing your post. '+data.reason);
			  }
			  else
			  {
  			  $('#publish_text').val('');
  			  $('#postlist').prepend(data.post).masonry();
		      $('#status_indicator').html('&nbsp;'); 
        }
		  }, 'json')
		  .error(function(e, xhr, settings, exception) {  })
		  .complete(function(e) { 
		    $('#publisher').fadeTo('normal', 1);
		    if($('#status_indicator').html() != '&nbsp;')
  		    $('#status_indicator').html('<br/>Ooops, something went wrong..<a title="'+e.responseText+'">!</a>'); 
		    else
  		    $('#status_indicator').html('&nbsp;'); 
		  });
	}
	
</script><div id="log"></div>
