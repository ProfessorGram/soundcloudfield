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
          maxheight: settings.height
        };

        $('#' + settings.id, context).once('soundcloudfield').each(function() {
          SC.oEmbed(trackUrl, embedSettings).then(function(oEmbed){
            var $markup = $('<div>' + oEmbed.html + '</div>');
            var $iframe = $markup.find('iframe');
            $iframe.height(settings.height + 'px');
            $iframe.width(settings.width + '%');
            if (typeof $iframe.attr('title') === 'undefined') {
              $iframe.attr('title', oEmbed.title);
            }             
            var url = new URL($iframe.attr('src'));
            url.searchParams.set('visual', settings.visualplayer);
            url.searchParams.set('color', settings.color);
            url.searchParams.set('auto_play', settings.autoplay);
            url.searchParams.set('show_comments', settings.showcomments);
            url.searchParams.set('hide_related', settings.hiderelated);
            url.searchParams.set('show_teaser', settings.showteaser);
            url.searchParams.set('show_artwork', settings.showartwork);
            url.searchParams.set('show_user', settings.showuser);
            url.searchParams.set('show_playcount', settings.showplaycount);
            $iframe.attr('src', url.href);
            $('#' + settings.id, context).html($markup.html());
          });
        });
      });
    }
  };

})(jQuery, Drupal, SC, drupalSettings);
