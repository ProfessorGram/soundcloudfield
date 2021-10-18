(function ($, Drupal, SC, drupalSettings) {

  var initialized;

  Drupal.behaviors.Soundcloud = {
    attach: function(context) {

      if (!initialized) {
        initialized = true;
        SC.initialize();
      }

      $.each(drupalSettings.soundcloudfield, function(index, settings) {
        var trackUrl = settings.url;
        var embedSettings = {
          auto_play: settings.autoplay,
          maxheight: settings.maxheight,
          show_artwork: settings.showartwork,
          show_playcount: settings.showplaycount,
          color: settings.color
        };

        $('#' + settings.id, context).once('soundcloudfield').each(function() {
          console.log(embedSettings);
          SC.oEmbed(trackUrl, embedSettings).then(function(oEmbed){
            var $markup = $('<div>' + oEmbed.html + '</div>');
            $markup.find('iframe').height(settings.maxheight + 'px');
            $('#' + settings.id, context).html($markup.html());
            console.log($markup.html());
          });
        });
      });
    }
  };

})(jQuery, Drupal, SC, drupalSettings);
