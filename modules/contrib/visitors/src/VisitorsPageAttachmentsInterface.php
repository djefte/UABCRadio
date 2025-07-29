<?php

declare(strict_types=1);

namespace Drupal\visitors;

/**
 * Visitors Page Attachments Interface.
 */
interface VisitorsPageAttachmentsInterface {

  /**
   * Add page attachments.
   *
   * @param array $page
   *   The page attachments array.
   */
  public function pageAttachments(array &$page);

}
