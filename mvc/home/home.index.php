<?

include_once('templates/postlist.php');

include('templates/publish.thingy.php');

tmpl_postlist($this->msg->getStream($this->user->entity), true);

?>