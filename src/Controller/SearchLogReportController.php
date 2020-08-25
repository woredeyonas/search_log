<?php

namespace Drupal\search_log\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Search_log report controller.
 */

class SearchLogReportController extends ControllerBase {

  public function search_log_report() {
    $today = getdate(_search_log_get_time());
    $date['from'] = $date['to'] = date('Y-m-d', $today[0]);
    $filter['modules'] = isset($_SESSION['search_log']['modules']) ?: array();
    $filter['languages'] = ['amharic', 'english'];
    $filter['result'] = isset($_SESSION['search_log']['result']) ?: SEARCH_LOG_RESULT_UNKNOWN;  
    $form = \Drupal::formBuilder()->getForm('Drupal\search_log\Form\SearchLogFilterForm', $date, $filter);
    return [
      '#theme' => 'search_log_report',
      '#summary' => 'summary test',
      '#filters' => $form,
      '#table' => 'table test',
      '#pager' => 'pager test'
    ];
  }
}

