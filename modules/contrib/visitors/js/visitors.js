/**
 * @file
 * Visitors behaviors.
 */

(function visitorsTrack(Drupal, drupalSettings, once) {
  /**
   * Attach visitors tracking behaviors.
   */
  Drupal.behaviors.visitorsTracker = {
    attach() {
      once('visitorsTracker', 'html').forEach(function visitors() {
        window._paq = window._paq || [];

        const { _paq } = window;
        const u = drupalSettings.path.baseUrl;
        const path = `/${drupalSettings.path.currentPath}`;
        const { uid } = drupalSettings.user;
        const { route, server, module, counter } = drupalSettings.visitors;

        function visitorsTracker() {
          _paq.push(['setSiteId', 1]);
          _paq.push(['setTrackerUrl', `${u}visitors/_track`]);
          _paq.push(['setUserId', uid]);
          _paq.push(['setCustomVariable', 1, 'route', route, 'visit']);
          _paq.push(['setCustomVariable', 2, 'path', path, 'visit']);
          _paq.push(['setCustomVariable', 3, 'server', server, 'visit']);
          if (counter) {
            _paq.push(['setCustomVariable', 4, 'viewed', counter, 'visit']);
          }

          _paq.push(['trackPageView']);

          const d = document;
          const g = d.createElement('script');
          const s = d.getElementsByTagName('script')[0];

          g.type = 'text/javascript';
          g.defer = true;
          g.async = true;
          g.src = `${module}/js/tracker.min.js`;

          s.parentNode.insertBefore(g, s);
        }

        window.addEventListener('load', function tracker() {
          visitorsTracker();
        });
      });
    },
  };
})(Drupal, drupalSettings, once);
