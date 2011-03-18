<?

/*
 * displays a list of posts
 * @param $list List of post datasets
 * @param $withContainer=false optional, makes postlist container around the list
 */
function tmpl_postlist($list, $withContainer = false)
{
  if($withContainer) { ?><div id="postlist"><? 
    #$GLOBALS['subcat']['view.opt'][] = array('caption' => l10n('post.pile-up'), 'url' => actionUrl($_REQUEST['action'], $_REQUEST['controller'], array('view' => 'pileup'))); 
    #$GLOBALS['subcat']['view.opt'][] = array('caption' => l10n('post.list'), 'url' => actionUrl($_REQUEST['action'], $_REQUEST['controller'], array('view' => 'list'))); 
  }

  if(sizeof($list['list'])>0) foreach($list['list'] as $ds)
	{
		$data = HubbubMessage::unpackData($ds);
		$typeFunction = 'dyn_type_'.$ds['m_type'];
		if(!is_callable($typeFunction)) $typeFunction = 'tmpl_type_notsupported';
		if($list['blurp_entity'] && $ds['m_owner'] != $list['blurp_entity']) $typeFunction = 'dyn_foreign_post_blurp';
		?><div class="dynamic_box post_item post_type_<?= $ds['m_type'] ?>"><?
    if(!is_callable($typeFunction)) 
    {
      if(isset($GLOBALS['config']['plugins']['show_'.$ds['m_type']]))
      {
        ?><div class="post msg_<?= $ds['m_type'] ?>" id="post_<?= $ds['m_key'] ?>"><?
        h2_execute_event('show_'.$ds['m_type'], $data, $ds);
        ?></div><?
      }
      else print('<div class="banner">Cannot display message type: '.$ds['m_type'].'</div>');
    }
    else 
    {
      $flags = array();
      if(isset($GLOBALS['config']['plugins']['display_'.$ds['m_type']]))
        h2_execute_event('display_'.$ds['m_type'], $data, $ds, $flags);
      $typeFunction($data, $ds, $flags);
    }
    ?></div><?
	}

  if($withContainer) { ?></div><script>
    $(window).load(function(){
      $('#postlist').masonry({
        animate: true,
        columnWidth: <?= CSS_COL_QUANTUM ?>,
      });
    });
    
    function post_reflow()
    {
      $('#postlist').masonry({
        animate: true,
      }).masonry({
        animate: true,
        columnWidth: <?= CSS_COL_QUANTUM ?>,
      });
    }
  </script><? }
}

/*
 * display a list of comments
 * @param $postDS The dataset of the parent post
 * @param &$comments List of comment datasets
 * @param $withContainer=false optional, makes the comment_list container around the list
 */
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
	    ageToString($comment['m_created']));
    if(object('user')->entity == $comment['m_owner'] || object('user')->entity == $comment['m_author']) $metaElements[] = '<a onclick="deleteComment('.$comment['m_key'].')">Delete</a>';
		?><div id="comment_item_<?= $comment['m_key'] ?>" class="comment_item comment_entry">
				<div class="comment_img"><img src="img/anonymous.png" width="32"/></div>
				<div class="comment_text"><?= HubbubEntity::linkFromId($comment['m_author']) ?>
				  <?= h2_post_excerpt($data['text'], $comment['m_key']) ?>	
				  <div class="comment_meta"><?= implode(' · ', $metaElements) ?></div></div>
		</div><?
	}

  if($withContainer) { ?></div><? }
}

/*
 * Single post display handler: for errors
 */
function dyn_type_notsupported(&$data, &$ds)
{
	?><div class="smalltext">(cannot display "<?= getDefault($ds['m_type'], 'undefined') ?>" message)</div><?
}

function dyn_foreign_post_blurp(&$data, &$ds)
{
	$excerpt = h2_make_excerpt($data['text']);
	?><div class="blurp_post" id="post_<?= $ds['m_key'] ?>">◇ <?= HubbubEntity::linkFromId($ds['m_author'], array('short' => true, 'nolink' => true)) ?>
			  <a href="<?= actionUrl('index', 'view', array('id' => $ds['m_owner'], 'post' => getDefault($ds['m_parent'], $ds['m_key']))) ?>"><?= l10n('commented') ?></a>
				<?= HubbubEntity::linkFromId($ds['m_owner']) ?>'s
				<?= l10n('comment_on_wall') ?>:
		<div class="smalltext blurp_excerpt">
			<?= htmlspecialchars($excerpt) ?>
		</div>
  </div><?
}

function h2_post_process($raw)
{
  $lines = array();
  foreach(explode(chr(10), str_replace(chr(13), chr(10), $raw)) as $line)
  {
    $words = array();
    foreach(explode(' ', trim($line)) as $word)
    {
      $wlen = strlen($word);
      if($wlen > POST_MAX_WORDSIZE)
      {
        $wordbreak = '';
        for($a = 0; $a < $wlen; $a++)
        {
          $wordbreak .= substr($word, $a, 1);
          if($a % POST_MAX_WORDSIZE == 0) $wordbreak .= ' ';
        }
        $word = $wordbreak;
      }
      if($word != '') $words[] = $word; 
    }
    if(sizeof($words) > 0)
      $lines[] = implode(' ', $words);
  }  
  return(nl2br(htmlspecialchars(implode(chr(10), $lines))));
}

function h2_post_excerpt($raw, $id)
{
  $raw = trim($raw);
  $max_excerpt_length = 200;
  $br_count = substr_count(nl2br($raw), '<br');
  
  if(strlen($raw) > $max_excerpt_length || $br_count >2) 
    $text = '<span id="post_more_'.$id.'">'.h2_post_process(h2_make_excerpt((str_replace(array(chr(10),chr(13)), ' ', $raw)), round($max_excerpt_length*0.7))).
    '<a title="'.str_word_count($raw, 0).' words" onclick="$(\'#post_more_'.$id.'\').html($(\'#post_morecont_'.$id.'\').html()); post_reflow();"> &lt;&middot;&gt;</a></span>'.
    '<span style="display:none" id="post_morecont_'.$id.'">'.h2_post_process($raw).'</span>'; 
  else
    $text = h2_post_process($raw);
  
  return($text);
}

/*
 * Single post display handler: for standard posts
 */
function dyn_type_post(&$data, &$ds, &$flags)
{
  /* are there any comments? */
	$comments = MsgModel::getComments($ds['m_key']);
	/* define the standard actions for this post */
	$metaElements = array(
	  ageToString($ds['m_created']), 
	  '<a onclick="springComment('.$ds['m_key'].')">Comment</a>', 
		'<a onclick="springVote('.$ds['m_key'].')">Vote</a>');
	/* insert entries from the cmd array in $flags (these come from plugins) */
	if(is_array($flags['cmd'])) foreach($flags['cmd'] as $cmd) $metaElements[] = $cmd;
	/* admin users get to see an "inspect" button */
  if(object('user')->isAdmin()) $metaElements[] = '<a target="_blank" href="'.actionUrl('inspect', 'test', array('id' => $ds['m_key'])).'">Inspect</a>';
  /* if user is either the owner or the author, she gets to see the delete button */
	if(object('user')->entity == $ds['m_owner'] || object('user')->entity == $ds['m_author']) $metaElements[] = '<a onclick="deletePost('.$ds['m_key'].')">Delete</a>';
  $text = h2_post_excerpt($data['text'], $ds['m_key']);
  /* onward to the actual display of the message: */
  ?><div class="post" id="post_<?= $ds['m_key'] ?>">
  	<div class="postimg"><img src="img/anonymous.png" width="64"/></div>
		<div class="postcontent">
			<div>
			<?
			if($ds['m_author'] != $ds['m_owner']) print(HubbubEntity::linkFromId($ds['m_author']).' ► ');
			?>
			<?= HubbubEntity::linkFromId($ds['m_owner']) ?>
			<?= $text ?></div>
			<? if(isset($flags['infoblock'])) print('<div>'.$flags['infoblock'].'</div>'); ?>
			<div class="postmeta"><?= implode(' · ', $metaElements) ?></div>
      <div id="post_<?= $ds['m_key'] ?>_votething" style="display:none" class="comment_item">
        &nbsp;✓&nbsp; I am: <input type="text" id="vote_<?= $ds['m_key'] ?>_text" value="liking it" style="width: 100px" 
        onkeypress="if(event.keyCode == 13) postVote(<?= $ds['m_key'] ?>); else if(event.keyCode == 27) cancelVote(<?= $ds['m_key'] ?>);"/>
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