<?php
/**
 * @file
 * Contains \Drupal\search_log\Form\SearchLogConfigForm.
 */

namespace Drupal\search_log\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

class SearchLogConfigForm extends ConfigFormBase {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $moduleHandler, Connection $database) {
    parent::__construct($config_factory);
    $this->moduleHandler = $moduleHandler;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['search_log_config.settings',];
  }

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'search_log_config_form';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('search_log_config.settings');

    $form['logging'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Search log settings'),
    );

    $terms_options = [
      SEARCH_LOG_TERMS_LOWERCASE => $this->t('lowercase (%1 stored as %2)', array('%1' => 'Apple iPod', '%2' => 'apple ipod')),
      SEARCH_LOG_TERMS_UPPERCASE_FIRST => $this->t('uppercase first word (%1 stored as %2)', array('%1' => 'Apple iPod', '%2' => 'Apple ipod')),
      SEARCH_LOG_TERMS_UPPERCASE_WORDS => $this->t('uppercase all words (%1 stored as %2)', array('%1' => 'Apple iPod', '%2' => 'Apple Ipod')),
    ];
    $form['logging']['search_log_terms'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Search term normalization'),
      '#description' => $this->t('Search terms are normalized before they are stored in Search log. Changing this value may result in duplicate terms for the current day.'),
      '#options' => $terms_options,
      '#default_value' => $config->get('search_log_terms'),
    );
    foreach ($this->moduleHandler->getImplementations('search_info') as $module) {
      $module_options[$module] = $module;
    }
    if(!empty($module_options)) {
      $form['logging']['search_log_modules_enabled'] = array(
        '#type' => 'checkboxes',
        '#title' => $this->t('Modules'),
        '#description' => t('Select modules to record in Search log. If no modules are checked, all modules which implement hook_search_info() will be recorded.'),
        '#options' => $module_options,
        '#default_value' => $config->get('search_log_modules_enabled'),
      );
    }
  
    $form['logging']['search_log_preprocess'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Collect search result with preprocess_search_results()'),
      '#description' => $this->t('Search does not have a hook to obtain the number of search results. This theme function will work in certain circumstances. If enabled, the function will add one extra DB write for failed search results.'),
      '#default_value' => $config->get('search_log_preprocess'),
    );
  
    $form['status'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Search log status'),
    );

    $query = $this->database->select('search_log', 'sl')
          ->fields('sl', ['qid']);
    $results = $query->execute()->fetchAll();
    $num_of_results = count($results);
  
    $form['status']['search_log_count']['#markup'] = '<p>' . t('There are :count entries in the Search log table.', array(':count' => number_format($num_of_results))) . '</p>';
  
    $form['status']['search_log_cron'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Days to keep search log'),
      '#description' => $this->t('Search log table can be automatically truncated by cron. Set to 0 to never truncate Search log table.'),
      '#size' => 4,
      '#default_value' => $config->get('search_log_cron'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
  }

}
