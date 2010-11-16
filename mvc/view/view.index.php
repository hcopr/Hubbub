<?

include('templates/publish.thingy.php');

tmpl_postlist($this->profile->getPostList($this->viewEntity, $_REQUEST['post']), true);

?>