<div class="soundcloudfield-js-embed-wrapper">
  <div id="<?php echo $id; ?>">

  </div>
  <script>
    var track_url = '<?php echo $url; ?>';
    var settings = {
      auto_play: <?php echo $autoplay; ?>,
      maxheight: '<?php echo $maxheight; ?>',
      show_artwork: <?php echo $showartwork; ?>,
      show_playcount: <?php echo $showplaycount; ?>,
      color: '#<?php echo $color; ?>'
    };

    SC.oEmbed(track_url, settings).then(function(oEmbed){
    document.getElementById('<?php echo $id; ?>').innerHTML=oEmbed.html});
  </script>
</div>