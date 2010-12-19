<div id="publisher">
	
	<table width="100%">
		<tr>
      <td valign="top">
        <textarea style="width: 600px;" id="publish_text"></textarea>      	
      </td>
      <td valign="top" width="230">
      	<input type="button" value="Share" onclick="do_publish();"/>
				<span id="status_indicator"></span>
      </td>
		</tr>
	</table>
	
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
	
	function do_publish()
	{
		$('#publisher').fadeTo('normal', 0.5);
		$('#status_indicator').html('<img src="themes/default/ajax-loader.gif"/>');
		$.post('<?= actionUrl('ajax_post', 'profile') ?>', 
		  {'text' : $('#publish_text').val()<? if($this->viewEntity) print(", 'to' : ".$this->viewEntity) ?> }, 
			function(data) {
			  if(data.result != 'OK')
			  {
			    if(!data.reason) data.reason = '';
			    alert('There was a problem publishing your post. '+data.reason);
			  }
			  else
			  {
  			  $('#publish_text').val('');
  			  $('#postlist').prepend(data.post);
  	      $('#publisher').fadeTo('normal', 1);
  	      $('#status_indicator').html('&nbsp;');
        }
		  }, 'json');
	}
	
</script><div id="log"></div>
