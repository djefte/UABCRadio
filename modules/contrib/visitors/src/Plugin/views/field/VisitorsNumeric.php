<?php

namespace Drupal\visitors\Plugin\views\field;

use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\field\NumericField;

/**
 * Field handler to display numeric values from the visitors module.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("visitors_numeric")
 */
class VisitorsNumeric extends NumericField {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return $account->hasPermission('view visitors counter');
  }

}
