<div style="width: 800px;">
  <div class="balloonhelp"><?= l10n('add.byusername.balloon') ?></div>
  <blockquote><?= $this->user->getUrl() ?></blockquote>
  <br/>
  <h2 style="padding-bottom: 8px;"><?= l10n('add.a.friend') ?></h2>
  
  <table width="100%">
    <tr>
      <td valign="top" width="90%">
        <input type="text" id="friend_url" value="" placeholder="<?= l10n('friendurl') ?>" 
          style="font-size: 110%;line-height:170%;" onkeypress="if(event.keyCode == 13) friend_search();"/>
      </td>
      <td valign="top" width="10%">
        <input type="button" value="<?= l10n('addbyurl.btn') ?>" onclick="friend_search();"/>
      </td>
    </tr>
  </table>
  <br/>
  <div id="search_results">
    
  </div>
  
</div>  
<script>
  
  function friend_search()
  {
    $('#search_results').html('<img src="themes/default/ajax-loader.gif" align="absmiddle" /> <?= l10n('searching') ?>');
    var fr_url = $('#friend_url').val();
    $.post('<?= actionUrl('ajax_search', 'friends') ?>', { 'q': fr_url }, function(data) {
      $('#search_results').html(data);
      //apply_style();
      });
  }
  
</script>
