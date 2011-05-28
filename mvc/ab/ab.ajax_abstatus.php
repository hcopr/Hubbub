<?

if(sizeof($this->myEntry) > 0)
{
  $paStyle = 'display:none;';
  ?><h3>You have an entry in the global address book:</h3><br/><?
  foreach($this->myEntry as $k => $v) if(!is_array($v))
  {
    ?><div>
      <span style="color: gray"><?= l10n('ab.'.$k) ?>: </span>
      <?
      switch($k)
      {
        case('pic'): {
          if($v != '') 
            print('<img src="'.urlencode($v).'" width="32"/>'); 
          else
            print('(none)'); 
        }
        default: {
          print(htmlspecialchars(getDefault($v, '-'))); 
          break; 
        }        
      }
    ?></div><? 
  } 
  ?><div id="postab" style="padding-top: 16px">
    <a onclick="$('#postab').html($('#abtempl').html());design_reflow();">change</a>
    &middot;
    <a onclick="
      $('#loaderimg1').css('display', 'inline');
      $('#postab').html($('#loaderimg1').html());
      $.post('<?= actionUrl('ajax_abremove', 'ab') ?>', {}, function(data) { $('#postab').html(data); } );">remove</a>
  </div><?
}
else
{
  $script = 'design_reflow();';
  ?><div>
    Status: <b style="color: gray">you are not listed.</b>
  </div>
  <br/>If you get listed in the global address book,
  your friends will have an easier time finding you. The address
  book contains your name, your Hubbub URL, your picture, an
  encrypted form of your email address (allowing others to find
  you if they already know your email), and a comment about yourself
  if you like:<br/>
  <?
}

?><br/>
<div id="abtempl" style="<?= $paStyle ?>">
  
  <textarea 
    style="width: 400px; height: 80px;"
    placeholder="A comment about yourself for the address book (optional)"
    id="ab_comment"><?= getDefault($this->abMetaData['comment']) ?></textarea>
  <br/><a href="#" class="btn"
    onclick="
      $('#loaderimg1').css('display', 'inline');
      $.post('<?= actionUrl('ajax_abnew', $this->name) ?>', { 'comment' : $('#ab_comment').val() }, function(data) {
        $('#mystatus').html(data);
      });
      "><img src="img/restart-1f.png" align="absmiddle"/> &nbsp;
      Update</a> 
      <img align="absmiddle" src="themes/default/ajax-loader.gif" style="display:none" id="loaderimg1"/>

</div>

<script>
  <?= $script ?>
</script>