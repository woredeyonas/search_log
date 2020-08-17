<?php

namespace Drupal\search_log\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Search_log report controller.
 */

class SearchLogReportController extends ControllerBase {
   public function report() {
    $build = [
        '#title' => $this->t('Top search terms'),
      ];
      return $build;
   }
} 
