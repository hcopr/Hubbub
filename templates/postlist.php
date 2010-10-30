<?

function tmpl_postlist($list, $withContainer = false)
{
  if($withContainer) { ?><div id="postlist"><? }

  if(sizeof($list)>0) foreach($list as $ds)
	{
		$data = HubbubMessage::unpackData($ds);
		$typeFunction = 'tmpl_type_'.$ds['m_type'];
		if(!is_callable($typeFunction)) $typeFunction = 'tmpl_type_notsupported';
    $typeFunction($data, $ds);
	}

  if($withContainer) { ?></div><? }
}

function tmpl_commentlist($postDS, &$comments, $withContainer = false)
{
  if($withContainer) print('<div class="comment_list" id="comments_'.$postDS['m_key'].'">');

  if($comments['count'] > 3 && sizeof($comments['list'])==3)
	{
		?><div class="comment_item smalltext">&nbsp; <a onclick="loadThread(<?= $postDS['m_key'] ?>)">▼ Show entire conversation</a> (<?= $comments['count'] ?>)</div><?
	}

  if(sizeof($comments['votes']) > 0)
  {
  	foreach($comments['votes'] as $voteDS)
		{
			$vData = HubbubMessage::unpackData($voteDS);
      ?><div class="comment_item smalltext">&nbsp; <?= htmlspecialchars(substr($vData['text'], 1)) ?> (<?= $voteDS['votecount'] ?>)</div><?
		}
  }

  if(sizeof($comments['list'])) foreach($comments['list'] as $comment)
	{
    $data = HubbubMessage::unpackData($comment);
	  $metaElements = array(
	    SQLCoolTime($comment['m_created']));
    if(object('user')->entity == $comment['m_owner'] || object('user')->entity == $comment['m_author']) $metaElements[] = '<a onclick="deleteComment('.$comment['m_key'].')">Delete</a>';
		?><div id="comment_item_<?= $comment['m_key'] ?>" class="comment_item comment_entry">
				<div class="comment_img"><img src="img/anonymous.png" width="32"/></div>
				<div class="comment_text"><?= HubbubEntity::linkFromId($comment['m_author']) ?>
				  <?= nl2br(htmlspecialchars($data['text'])) ?>	
				  <div class="comment_meta"><?= implode(' · ', $metaElements) ?></div></div>
		</div><?
	}

  if($withContainer) { ?></div><? }
}

function tmpl_type_notsupported(&$data, &$ds)
{
	?><div class="smalltext">(cannot display "<?= getDefault($ds['m_type'], 'undefined') ?>" message)</div><?
}

function tmpl_type_post(&$data, &$ds)
{
	$comments = ProfileModel::getComments($ds['m_key']);
	$metaElements = array(
	  SQLCoolTime($ds['m_created']), 
	  '<a onclick="springComment('.$ds['m_key'].')">Comment</a>', 
		'<a onclick="springVote('.$ds['m_key'].')">Vote</a>');
	if(object('user')->entity == $ds['m_owner'] || object('user')->entity == $ds['m_author']) $metaElements[] = '<a onclick="deletePost('.$ds['m_key'].')">Delete</a>';
  ?><div class="post" id="post_<?= $ds['m_key'] ?>">
  	<div class="postimg"><img src="img/anonymous.png" width="64"/></div>
		<div class="postcontent">
			<div><?= HubbubEntity::linkFromId($ds['m_owner']) ?>
			<?= nl2br(htmlspecialchars($data['text'])) ?></div>
			<div class="postmeta"><?= implode(' · ', $metaElements) ?></div>
      <div id="post_<?= $ds['m_key'] ?>_votething" style="display:none" class="comment_item">
        &nbsp;✓&nbsp; I am: <input type="text" id="vote_<?= $ds['m_key'] ?>_text" value="liking it" style="width: 100px" onkeypress="if(event.keyCode == 13) postVote(<?= $ds['m_key'] ?>); else if(event.keyCode == 27) cancelVote(<?= $ds['m_key'] ?>);"/>
				<input class="smallbtn" type="button" value="Vote" onclick="postVote(<?= $ds['m_key'] ?>)"/><input class="smallbtn" type="button" value="Cancel" onclick="cancelVote(<?= $ds['m_key'] ?>)"/>
      </div>
			<? tmpl_commentlist($ds, $comments, true) ?>
			<div class="post_actions" id="post_<?= $ds['m_key'] ?>_actions">
				<div class="post_pseudocomment" onclick="springComment(<?= $ds['m_key'] ?>)">Click here to comment</div>
			</div>
      <div id="post_<?= $ds['m_key'] ?>_temp" style="display:none"></div>
      <div id="post_<?= $ds['m_key'] ?>_temp_commentthing" style="display:none">
      	<textarea id="post_<?= $ds['m_key'] ?>_comment" onblur="closeCommentIfEmpty(<?= $ds['m_key'] ?>)"></textarea>
        <input type="button" value="Comment" onclick="postComment(<?= $ds['m_key'] ?>)"/> 
				<span id="post_<?= $ds['m_key'] ?>_status"></span>
      </div>
		</div>
  </div><?
}

?>