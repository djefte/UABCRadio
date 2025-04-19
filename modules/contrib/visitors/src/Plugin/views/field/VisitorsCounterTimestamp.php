<?php

namespace Drupal\visitors\Plugin\views\field;

use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\field\Date;

/**
 * Field handler to display the most recent time the node has been viewed.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_counter_timestamp")
 */
class VisitorsCounterTimestamp extends Date {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return $account->hasPermission('view visitors counter');
  }

}
