<?

include_once('templates/postlist.php');

include('templates/publish.thingy.php');

tmpl_postlist($this->msg->getStream($this->user->ds['u_key'], $this->user->entity), true);

?>