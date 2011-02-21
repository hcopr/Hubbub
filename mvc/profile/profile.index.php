<?

include('templates/publish.thingy.php');

tmpl_postlist($this->msg->getPostList($this->user->entity), true);

?>