<?php

namespace Drupal\visitors\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\ParamConverter\ParamNotConvertedException;
use Drupal\Core\Utility\Error;
use Drupal\visitors\VisitorsRebuildRouteInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * Service for rebuilding routes.
 */
class RebuildRouteService implements VisitorsRebuildRouteInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The router.
   *
   * @var \Symfony\Component\Routing\Matcher\RequestMatcherInterface
   */
  protected $routerMatcher;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new Rebuild Route Service.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Symfony\Component\Routing\Matcher\RequestMatcherInterface $router_matcher
   *   The router matcher.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(Connection $connection, RequestMatcherInterface $router_matcher, LoggerInterface $logger) {
    $this->database = $connection;
    $this->routerMatcher = $router_matcher;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function rebuild(string $path): int {
    $count = 0;
    $route = '';
    try {
      $request = Request::create($path);
      $result = $this->routerMatcher->matchRequest($request);
      if (!empty($result['_route'])) {
        $route = $result['_route'];
      }

    }
    catch (ParamNotConvertedException $e) {
      $route = $e->getRouteName();
    }
    catch (ResourceNotFoundException $e) {
      // Do nothing.
    }
    catch (\Exception $e) {
      Error::logException($this->logger, $e);
    }

    if (empty($route)) {
      return $count;
    }

    try {
      $count = $this->database->update('visitors')
        ->fields(['route' => $route])
        ->condition('visitors_path', $path)
        ->execute();
    }
    catch (\Exception $e) {
      Error::logException($this->logger, $e);
    }

    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaths(): array {
    $records = [];
    try {
      $records = $this->database->select('visitors', 'v')
        ->fields('v', ['visitors_path'])
        ->condition('route', '')
        ->distinct()
        ->execute()
        ->fetchAll();
    }
    catch (\Exception $e) {
      Error::logException($this->logger, $e);
    }

    return $records;
  }

}
