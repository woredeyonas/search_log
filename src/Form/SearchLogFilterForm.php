<?php
/**
 * @file
 * Contains \Drupal\search_log\Form\SearchLogFilterForm.
 */

namespace Drupal\search_log\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class SearchLogFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */    
  public function getFormId()
  {
      return 'search_log_filter';
  }  

  /**
   * {@inheritdoc}
   */  
  public function buildForm(array $form, FormStateInterface $form_state, array $date = [], array $filter = []) {
    $today = date('Y-m-d', _search_log_get_time());
    $links = [
        [
            '#title' => $this->t('Today'),
            '#type' => 'link',
            '#url' =>  \Drupal\Core\Url::fromUserInput('/admin/reports/search/today'),
        ],
        [
            '#title' => $this->t('This week'),
            '#type' => 'link',
            '#url' =>  \Drupal\Core\Url::fromUserInput('/admin/reports/search/week'),
        ],
        [
            '#title' => $this->t('This month'),
            '#type' => 'link',
            '#url' =>  \Drupal\Core\Url::fromUserInput('/admin/reports/search/month'),
        ],
        [
            '#title' => $this->t('This year'),
            '#type' => 'link',
            '#url' =>  \Drupal\Core\Url::fromUserInput('/admin/reports/search/year'),
        ]
    ];
    $form['period'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Period'),
    ];
    $form['period']['links'] = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#items' => $links,
        '#wrapper_attributes' => ['class' => 'search-log-links'],
    ];  
    $form['period']['from_date'] = [
        '#type' => 'textfield',
        '#title' => $this->t('From'),
        '#default_value' => $date['from'] ?: $today,
    ];
    $form['period']['to_date'] = [
        '#type' => 'textfield',
        '#title' => $this->t('To'),
        '#default_value' => $date['to'] ?? $today,
        '#description' => $this->t('Enter custom period for search reporting.'),
    ];

    //if(\Drupal::service('database')->queryRange)
    if (1 === 1) {
        $result_options = [
          SEARCH_LOG_RESULT_UNKNOWN => $this->t('All'),
          SEARCH_LOG_RESULT_SUCCESS => $this->t('Success'),
          SEARCH_LOG_RESULT_FAILED => $this->t('Failed'),
        ];
    
        $form['result'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Result'),
        ];
    
        $form['result']['result'] = [
          '#type' => 'radios',
          '#description' => $this->t('Select result to include in search reporting.'),
          '#options' => $result_options,
          '#default_value' => $filter['result'] ?: SEARCH_LOG_RESULT_UNKNOWN,
          '#required' => TRUE,
        ];
    }  
  // Search modules.
  $module_options = array();
  $query = db_query('SELECT DISTINCT module FROM {search_log}');
  while ($row = $query->fetchObject()) {
    $module_options[$row->module] = $row->module;
  }
  if (count($module_options) > 1) {
    $module_default = !empty($filter['modules']) ? $filter['modules'] : array_keys($module_options);

    $form['modules'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Modules'),
    );

    $form['modules']['modules'] = array(
      '#type' => 'checkboxes',
      '#description' => $this->t('Select modules to include in search reporting.'),
      '#options' => $module_options,
      '#default_value' => $module_default,
    );
  }
  
    // Search languages.
    $language_options = array();
    $query = db_query('SELECT DISTINCT language FROM {search_log}');
    while ($row = $query->fetchObject()) {
      $language_options[$row->language] = $row->language;
    }
    if (count($language_options) > 1) {
      $language_default = !empty($filter['languages']) ? $filter['languages'] : array_keys($language_options);
  
      $form['languages'] = array(
        '#type' => 'fieldset',
        '#title' => $this->t('Languages'),
      );
  
      $form['languages']['languages'] = array(
        '#type' => 'checkboxes',
        '#description' => $this->t('Select languages to include in search reporting.'),
        '#options' => $language_options,
        '#default_value' => $language_default,
      );
    }

    $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Update Report'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */  
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $today = _search_log_get_time();
    $from = strtotime($form_state->getValue('from_date'));
    if (!$from) {
      $from = $today;
    }
  
    if ($from > $today) {
      $form_state->setErrorByName('from_date', $this->t('From date cannot be after today.'));
    }
  
    $to = strtotime($form_state->getValue('to_date'));
    if (!$to) {
      $to = $today;
    }
  
    if ($from > $to) {
      $form_state->setErrorByName('from_date', $this->t('From date cannot be after To date.'));  
    }
  
     if ($form_state->getValue('modules') !== null) {
      $modules = array_flip($form_state['values']['modules']);
      unset($modules[0]);
      if (count($modules) < 1) {
        $form_state->setErrorByName('modules', $this->t('At least one module must be selected.'));    
      }
    }
  
    if ($form_state->getValue('languages') !== null) {
      $languages = array_flip($form_state['values']['languages']);
      unset($languages[0]);
      if (count($languages) < 1) {
        $form_state->setErrorByName('languages', $this->t('At least one language must be selected.'));    
      }
    }   
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $modules = $form_state->getValue('modules') !== null ? array_flip($form_state->getValue('modules')) : [];
    unset($modules[0]);
  
    $languages = $form_state->getValue('languages') !== null ? array_flip($form_state->getValue('languages')) : [];
    unset($languages[0]);
  
    $_SESSION['search_log'] = array(
      'from' => $form_state->getValue('from_date'),
      'to' => $form_state->getValue('to_date'),
      'modules' => array_keys($modules),
      'languages' => array_keys($languages),
      'result' => $form_state->getValue('result'),
    );
  }  


}

