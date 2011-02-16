<?

include('templates/publish.thingy.php');

tmpl_postlist($this->msg->getPostList($this->user->entity), true);

?><div id="board_wrapper">
  <?
  srand(0);
  for($a = 0; $a < 30; $a++)
  {
    ?><div class="masbox" style="width: <?= 120-8-10+rand(0, 3)*120 ?>px; height: <?= rand(1, 8)*32 ?>px;">
      test <?= $a ?>
    </div><? 
  }  
  ?>
</div>

<script>
$(window).load(function(){
  $('#board_wrapper').masonry({
    columnWidth: 120, 
    itemSelector: '.masbox' 
  });
});
</script>