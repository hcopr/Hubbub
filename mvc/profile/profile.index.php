<?

include_once('templates/postlist.php');

include('templates/publish.thingy.php');

tmpl_postlist($this->model->getPostList($this->user->entity), true);

?>