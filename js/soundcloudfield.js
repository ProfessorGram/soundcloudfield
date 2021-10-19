(function ($, Drupal, SC) {

  var initialized;

  Drupal.behaviors.Soundcloud = {
    attach: function(context) {

      if (!initialized) {
        initialized = true;
        SC.initialize();
      }

      $('.soundcloudfield-js-embed-wrapper div', context).once('soundcloudfield').each(function() {
        var self = this;
        var id = $(self).attr('id');
        var settings = Drupal.settings.soundcloudfield[id];
        var trackUrl = settings.url;
        var embedSettings = {
          maxheight: settings.height
        };

        SC.oEmbed(trackUrl, embedSettings).then(function(oEmbed){
          var $markup = $('<div>' + oEmbed.html + '</div>');
          var $iframe = $markup.find('iframe');
          $iframe.height(settings.height + 'px');
          $iframe.width(settings.width + '%');
          var url = new URL($iframe.attr('src'));
          url.searchParams.set('visual', settings.visual);
          url.searchParams.set('color', settings.color);
          url.searchParams.set('auto_play', settings.autoplay);
          url.searchParams.set('show_comments', settings.showcomments);
          url.searchParams.set('hide_related', settings.hiderelated);
          url.searchParams.set('show_teaser', settings.showteaser);
          url.searchParams.set('show_artwork', settings.showartwork);
          url.searchParams.set('show_user', settings.showuser);
          url.searchParams.set('show_playcount', settings.showplaycount);
          $iframe.attr('src', url.href);
          $(self).html($markup.html());
        });
      });
    }
  };

})(jQuery, Drupal, SC);
