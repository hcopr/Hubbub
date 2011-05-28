<?

if(sizeof($this->myEntry) > 0)
{
  ?><h3>You have an entry in the global address book</h3><?
  foreach($this->myEntry as $k => $v) if(!is_array($v))
  {
    ?><div>
      <span style="color: gray"><?= htmlspecialchars($k) ?>: </span>
      <?= htmlspecialchars($v) ?>
    </div><? 
  }
}
else
{
  ?><div>
    Status: <b style="color: gray">you are not listed.</b>
    <?
    if($this->reqStatus['abrequest'] == 'pending')
      print('(listing request is pending)');
    ?>
  </div>
  <br/>If you get listed in the global address book,
  your friends will have an easier time finding you. The address
  book contains your name, your Hubbub URL, your picture, an
  encrypted form of your email address (allowing others to find
  you if they already know your email), and a comment about yourself
  if you like:<br/>
  <textarea 
    style="width: 400px; height: 80px;"
    placeholder="A comment about yourself for the address book (optional)"
    id="ab_comment"><?= getDefault($this->abMetaData['comment']) ?></textarea>
  <br/><a href="#" class="btn"
    onclick="
      $('#loaderimg1').css('display', 'inline');
      $.post('<?= actionUrl('ajax_abnew', $this->name) ?>', { 'comment' : $('#ab_comment').val(); }, function(data) {
        $('#mystatus').html(data);
      });"><img src="img/restart-1f.png" align="absmiddle"/> &nbsp;
      Create Entry Now</a> 
      <img align="absmiddle" src="themes/default/ajax-loader.gif" style="display:none" id="loaderimg1"/>
  <script>
    design_reflow();
  </script><?
}

?>