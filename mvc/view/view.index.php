<?

include('templates/publish.thingy.php');

tmpl_postlist($this->msg->getPostList($this->viewEntity, $_REQUEST['post']), true);

?>