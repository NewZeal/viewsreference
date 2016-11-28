<?php

namespace Drupal\viewsreference\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;


/**
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

    $options['render_view'] = TRUE;
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    // We may decide on alternatives to rendering the view so get settings established
    $form['render_view'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Render View'),
      '#default_value' => $this->getSetting('render_view'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $settings = $this->getSettings();

    $summary[] = t('Render View: @view', array('@view' => $settings['render_view'] ? 'TRUE' : 'FALSE'));
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
      $view = \Drupal\views\Views::getView($view_name);
      // Someone may have deleted the view
      if (!is_object($view)) {
        continue;
      }
      // Todo also apply check in case someone deleted the display id
      $view->setDisplay($display_id);
      
      if ($argument != '') {
        $view->setArguments(array($argument));
      }

      if ($title) {
        $title = $view->getTitle();
        $title_render_array = array(
          '#markup' => '<div class="viewsreference-title">' . t('@title', ['@title'=> $title]) . '</div>'
        );
      }
      $view->build($display_id);
      $view->execute($display_id);
      $result = $view->result;
      $render = $view->render();
      $render['#view']->setTitle($title);
      if ($this->getSetting('render_view')) {
        if ($title && !empty($result)) {
          $elements[$delta]['title'] = $title_render_array;
        }
        $elements[$delta]['contents'] = $render;
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
//  public static function isApplicable(FieldDefinitionInterface $field_definition) {
//    return $field_definition->getTargetEntityTypeId() === 'user' && $field_definition->getName() === 'name';
//  }

}
