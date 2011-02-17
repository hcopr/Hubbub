/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: general JavaScript UI functions
 */

function springComment(cid)
{
  $('#post_'+cid+'_temp').html($('#post_'+cid+'_actions').html());
  $('#post_'+cid+'_actions').html($('#post_'+cid+'_temp_commentthing').html());
  document.getElementById('post_'+cid+'_comment').focus();
  post_reflow();
} 

function springVote(cid)
{
	$('#post_'+cid+'_votething').css('display', 'block');
	document.getElementById('vote_'+cid+'_text').focus();
	post_reflow();
}

function cancelVote(cid)
{
  $('#post_'+cid+'_votething').css('display', 'none');
}

function postVote(cid)
{
  $.post('msg-ajax_vote', 
    {'text' : $('#vote_'+cid+'_text').val(), 'pid' : cid }, function(data) {
      cancelVote(cid);
    });
}

function closeCommentIfEmpty(cid)
{
	if($.trim($('#post_'+cid+'_comment').val()) == '')
    $('#post_'+cid+'_actions').html($('#post_'+cid+'_temp').html());  
}

function postComment(cid)
{
  $('#post_'+cid+'_status').html('<img src="themes/default/ajax-loader.gif" align="absmiddle"/>');
  $.post('msg-ajax_comment', 
    {'text' : $('#post_'+cid+'_comment').val(), 'pid' : cid }, function(data) {
      if(data.result != 'OK')
      {
        if(!data.reason) data.reason = '';
        alert('There was a problem publishing your comment. '+data.reason);
      }
      else
      {
        $('#post_'+cid+'_actions').html($('#post_'+cid+'_temp').html());
        $('#comments_'+cid).append(data.post);
        post_reflow();
      }
    }, 'json');
}

function loadThread(cid)
{
  $('#comments_'+cid).fadeTo('normal', 0.5);
	$('#comments_'+cid).load('msg-ajax_thread?pid='+cid);
  $('#comments_'+cid).fadeTo('normal', 1);
  post_reflow();
}

function deletePost(cid)
{
  $.post('msg-ajax_deletepost', {'pid': cid}, function(data) {
    if(data.result == 'OK')
  	{
  	 	$('#post_'+cid).fadeOut('normal').remove();
  		post_reflow();
		}
		else
		  alert('Error. Could not delete this entry.');
	  }, 'json');
}

function deleteComment(cid)
{
  $.post('msg-ajax_deletecomment', {'pid': cid}, function(data) {
		if(data.result == 'OK')
		{
      $('#comment_item_'+cid).fadeOut('normal').remove();
      post_reflow();
    }
    else
      alert('Error. Could not delete this entry.');
    }, 'json');
}

