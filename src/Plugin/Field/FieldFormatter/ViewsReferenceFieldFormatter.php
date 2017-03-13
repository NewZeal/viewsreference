<?php

namespace Drupal\viewsreference\Plugin\Field\FieldFormatter;

use Drupal\views\Views;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Field formatter for Viewsreference Field.
 *
 * @FieldFormatter(
 *   id = "viewsreference_formatter",
 *   label = @Translation("Views Reference"),
 *   field_types = {"viewsreference"}
 * )
 */
class ViewsReferenceFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $options = parent::defaultSettings();

    $options['plugin_types'] = array('block');
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $types = Views::pluginList();
    $options = array();
    foreach ($types as $key => $type) {
      if ($type['type'] == 'display') {
        $options[str_replace('display:', '', $key)] = $type['title']->render();
      }
    }

    $form['plugin_types'] = [
      '#type' => 'checkboxes',
      '#options' => $options,
      '#title' => $this->t('View display plugins to allow'),
      '#default_value' => $this->getSetting('plugin_types'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $settings = $this->getSettings();

    $allowed = array();
    foreach ($settings['plugin_types'] as $type) {
      if ($type) {
        $allowed[] = $type;
      }
    }
    $summary[] = t('Allowed plugins: @view', array('@view' => implode(', ', $allowed)));
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $view_name = $item->getValue()['target_id'];
      $display_id = $item->getValue()['display_id'];
      $argument = $item->getValue()['argument'];
      $title = $item->getValue()['title'];
      $view = Views::getView($view_name);
      // Someone may have deleted the View.
      if (!is_object($view)) {
        continue;
      }
      $arguments = [$argument];
      if (preg_match('/\//', $argument)) {
        $arguments = explode('/', $argument);
      }
      $view->setDisplay($display_id);
      $view->setArguments($arguments);
      $view->build($display_id);
      $view->preExecute();
      $view->execute($display_id);

      if (!empty($view->result)) {
        if ($title) {
          $title = $view->getTitle();
          $title_render_array = array(
            '#theme' => 'viewsreference__view_title',
            '#title' => $this->t($title),
          );
        }

        if ($this->getSetting('plugin_types')) {
          if ($title) {
            $elements[$delta]['title'] = $title_render_array;
          }
        }

        $elements[$delta] = $view->buildRenderable($display_id);
      }

    }

    return $elements;
  }

}
