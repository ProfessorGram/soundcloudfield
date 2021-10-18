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
          show_playcount: settings.showplaycount
        };

        $('#' + settings.id, context).once('soundcloudfield').each(function() {
          SC.oEmbed(trackUrl, embedSettings).then(function(oEmbed){
            var $markup = $('<div>' + oEmbed.html + '</div>');
            var $iframe = $markup.find('iframe');
            $iframe.height(settings.maxheight + 'px');
            $iframe.attr('src', $iframe.attr('src') + '&amp;color=%23' + settings.color);
            $('#' + settings.id, context).html($markup.html());
          });
        });
      });
    }
  };

})(jQuery, Drupal, SC, drupalSettings);
