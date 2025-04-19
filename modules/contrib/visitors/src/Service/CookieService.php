<?php

namespace Drupal\visitors\Service;

use Drupal\visitors\VisitorsCookieInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Cookie service.
 */
class CookieService implements VisitorsCookieInterface {

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs a new CookieService.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request object.
   */
  public function __construct(RequestStack $request_stack) {
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function getId(): ?string {
    $_pk_id = NULL;
    foreach ($this->request->cookies as $name => $value) {
      if (strpos($name, '_pk_id_') === 0) {
        $_pk_id = $value;
      }
    }
    [$visitor_id] = is_string($_pk_id) ? explode('.', $_pk_id) : [NULL];

    return $visitor_id;
  }

}
