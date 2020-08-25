<?php
/**
 * @file
 * Contains \Drupal\search_log\Routing\RouteSubscriber.
 */
 
namespace Drupal\search_log\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('dblog.search')) {
      $route->setDefaults(array(
        '_controller' => '\Drupal\search_log\Controller\SearchLogReportController::search_log_report',
      ));
    }
  }
}
