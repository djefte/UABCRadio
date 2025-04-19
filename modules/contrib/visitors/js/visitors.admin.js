/**
 * @file
 * Visitors admin behaviors.
 */

(function visitorAdminForm(Drupal, $) {
  /**
   * Provide the summary information for the tracking settings vertical tabs.
   */
  Drupal.behaviors.visitorsSettingsSummary = {
    attach(context) {
      const $context = $(context);
      // Make sure this behavior is processed only if drupalSetSummary is defined.
      if (typeof jQuery.fn.drupalSetSummary === 'undefined') {
        return;
      }

      $context
        .find('#edit-page-visibility-settings')
        .drupalSetSummary((context) => {
          const $editContext = $(context);

          const paths = $editContext
            .find('textarea[name="visitors_visibility_request_path_pages"]')
            .get(0)?.value;

          if (paths.length === 0) {
            return Drupal.t('Not restricted');
          }

          const radio = $editContext.find(
            'input[name="visitors_visibility_request_path_mode"]:checked',
          );

          if (radio.val() === '0') {
            return Drupal.t('All pages with exceptions');
          }

          return Drupal.t('Restricted to certain pages');
        });

      $context
        .find('#edit-role-visibility-settings')
        .drupalSetSummary((context) => {
          const values = [];
          const $editContext = $(context);
          $editContext
            .find('#edit-visitors-visibility-user-role-roles input:checked')
            .next('label')
            .each(function visitorsVisibilityUserRole() {
              values.push(Drupal.checkPlain(this.textContent));
            });

          if (values.length === 0) {
            return Drupal.t('Not restricted');
          }
          const $restricted = $editContext.find(
            '#edit-visitors-visibility-user-role-mode-1',
          );
          if ($restricted.checked) {
            return Drupal.t('Except: @roles', { '@roles': values.join(', ') });
          }

          return values.join(', ');
        });

      $context
        .find('#edit-user-visibility-settings')
        .drupalSetSummary((context) => {
          const values = [];
          const $editContext = $(context);
          $editContext
            .find('input[name="visitors_visibility_user_account_mode"]:checked')
            .next('label')
            .each(function visitorsVisibilityUserAccountMode() {
              values.push(Drupal.checkPlain(this.textContent));
            });

          return values.join(', ');
        });

      $context.find('#edit-entity').drupalSetSummary((context) => {
        const values = [];
        const $editContext = $(context);
        $editContext
          .find('input:checked')
          .next('label')
          .each(function editEntity() {
            values.push(Drupal.checkPlain(this.textContent));
          });
        if ($editContext.find('#edit-counter-enabled:checked').length === 0) {
          values.unshift(Drupal.t('Disabled'));
        }
        return values.join(', ');
      });

      $context.find('#edit-retention').drupalSetSummary((context) => {
        const values = [];
        const $editContext = $(context);

        $editContext
          .find('select[name="flush_log_timer"] option:selected')
          .each(function flushLogTimer() {
            values.push(
              Drupal.t('Logs: @log', {
                '@log': Drupal.checkPlain(this.textContent),
              }),
            );
          });

        $editContext
          .find('select[name="bot_retention_log"] option:selected')
          .each(function botRetentionLog() {
            values.push(
              Drupal.t('Bots: @bot', {
                '@bot': Drupal.checkPlain(this.textContent),
              }),
            );
          });

        return values.join(', ');
      });

      $context.find('#edit-miscellaneous').drupalSetSummary((context) => {
        const values = [];
        const $editContext = $(context);
        $editContext
          .find('input[name="script_type"]:checked')
          .next('label')
          .each(function scriptType() {
            values.push(Drupal.checkPlain(this.textContent));
          });

        $editContext
          .find('select[name="items_per_page"] option:selected')
          .each(function itemsPerPage() {
            values.push(
              Drupal.t('Items: @items', {
                '@items': Drupal.checkPlain(this.textContent),
              }),
            );
          });

        return values.join(', ');
      });
    },
  };
})(Drupal, jQuery);
