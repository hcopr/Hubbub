<?

include('templates/publish.thingy.php');

?><pre>
  <? print_r(object('user')->ds); ?>
</pre><?

tmpl_postlist($this->msg->getPostList($this->user->entity), true);

?>