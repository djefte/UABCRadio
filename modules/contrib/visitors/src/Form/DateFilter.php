<?php

namespace Drupal\visitors\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\visitors\Service\DateRangeService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Date Filter form.
 */
class DateFilter extends FormBase {


  /**
   * The date service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The date range service.
   *
   * @var \Drupal\visitors\Service\DateRangeService
   */
  protected $dateRangeService;

  /**
   * DateFilter constructor.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\visitors\Service\DateRangeService $date_range_service
   *   The date range service.
   */
  public function __construct(DateFormatterInterface $date_formatter, DateRangeService $date_range_service) {
    $this->dateFormatter = $date_formatter;
    $this->dateRangeService = $date_range_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): DateFilter {
    return new static(
      $container->get('date.formatter'),
      $container->get('visitors.date_range')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'visitors_date_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = [];

    $form['visitors_date_filter'] = [
      '#collapsible' => TRUE,
      '#title' => $this->dateRangeService->getSummary(),
      '#type' => 'details',
      '#open' => FALSE,
    ];

    $form['visitors_date_filter']['from'] = [
      '#title' => $this->t('From'),
      '#type' => 'date',
      '#default_value' => $this->dateRangeService->getStartDate(),
    ];

    $form['visitors_date_filter']['to'] = [
      '#title'            => $this->t('To'),
      '#type' => 'date',
      '#default_value'    => $this->dateRangeService->getEndDate(),
      '#states' => [
        'visible' => [
          ':input[name="period"]' => ['value' => 'range'],
        ],
      ],
    ];

    $form['visitors_date_filter']['period'] = [
      '#type' => 'radios',
      '#title' => $this->t('Period'),
      '#options' => [
        'day' => $this->t('Day'),
        'week' => $this->t('Week'),
        'month' => $this->t('Month'),
        'year' => $this->t('Year'),
        'range' => $this->t('Range'),
      ],
      '#default_value' => $this->dateRangeService->getPeriod(),
    ];

    $form['visitors_date_filter']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Apply'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $period = $form_state->getValue('period');
    $from = $form_state->getValue('from');
    $to = $form_state->getValue('to');

    $this->dateRangeService->setPeriodAndDates($period, $from, $to);
  }

}
