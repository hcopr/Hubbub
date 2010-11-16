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
} 

function springVote(cid)
{
	$('#post_'+cid+'_votething').css('display', 'block');
	document.getElementById('vote_'+cid+'_text').focus();
}

function cancelVote(cid)
{
  $('#post_'+cid+'_votething').css('display', 'none');
}

function postVote(cid)
{
  $.post('profile-ajax_vote', 
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
  $.post('profile-ajax_comment', 
    {'text' : $('#post_'+cid+'_comment').val(), 'pid' : cid }, function(data) {
      $('#post_'+cid+'_actions').html($('#post_'+cid+'_temp').html());
      $('#comments_'+cid).append(data);
    });
}

function loadThread(cid)
{
  $('#comments_'+cid).fadeTo('normal', 0.5);
	$('#comments_'+cid).load('profile-ajax_thread?pid='+cid);
  $('#comments_'+cid).fadeTo('normal', 1);
}

function deletePost(cid)
{
  $.post('profile-ajax_deletepost', {'pid': cid}, function(data) {
    if(data.result == 'OK')
  		$('#post_'+cid).fadeOut('normal');
		else
		  alert('Error. Could not delete this entry.');
	  }, 'json');
}

function deleteComment(cid)
{
  $.post('profile-ajax_deletecomment', {'pid': cid}, function(data) {
		if(data.result == 'OK')
      $('#comment_item_'+cid).fadeOut('normal');
    else
      alert('Error. Could not delete this entry.');
    }, 'json');
}

