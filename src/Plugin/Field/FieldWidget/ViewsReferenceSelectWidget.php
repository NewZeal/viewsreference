<?php

namespace Drupal\viewsreference\Plugin\Field\FieldWidget;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'options_select' widget.
 *
 * @FieldWidget(
 *   id = "viewsreference_select",
 *   label = @Translation("Views Reference Select list"),
 *   description = @Translation("An autocomplete views select list field."),
 *   field_types = {
 *     "viewsreference"
 *   }
 * )
 */
class ViewsReferenceSelectWidget extends OptionsSelectWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $select_element['target_id'] = parent::formElement($items, $delta, $element, $form, $form_state);
    $field_name = $items->getName();
    $name = $field_name . '[' . $delta . '][0]';

    $select_element['target_id']['#target_type'] = 'view';
    $select_element['target_id']['#ajax'] = array(
      'callback' => array($this, 'getDisplayIds'),
      'event' => 'change',
      'progress' => array(
        'type' => 'throbber',
        'message' => t('Getting display Ids...'),
      ),
    );

    $default_value = isset($items[$delta]->getValue()['display_id']) ? $items[$delta]->getValue()['display_id'] : '';
    if ($default_value == '') {
      $options = $this->getAllViewsDisplayIds();
    }
    else {
      $options = $this->getViewDisplayIds($items[$delta]->getValue()['target_id']);
    }

    // We build a unique class name from field elements and any parent elements
    // that might exist which will be used to render the display id options in
    // our ajax function.
    $class = !empty($element['target_id']['#field_parents']) ? implode('-', $element['target_id']['#field_parents']) . '-' : '';
    $class .= $field_name  . '-' . $delta . '-display-id';

    $select_element['display_id'] = array(
      '#title' => 'Display Id',
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $default_value,
      '#weight' => 10,
      '#attributes' => array(
        'class' => array(
          $class
        )
      ),
      '#states' => array(
        'visible' => array(
          ':input[name="' . $name . '"]' => array('!value' => '_none'),
        ),
      ),
    );

    $select_element['argument'] = array(
      '#title' => 'Argument',
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->getValue()['argument']) ? $items[$delta]->getValue()['argument'] : '',
      '#weight' => 20,
      '#states' => array(
        'visible' => array(
          ':input[name="' . $name . '"]' => array('!value' => '_none'),
        ),
      ),
    );

    $select_element['title'] = array(
      '#title' => 'Include View Title',
      '#type' => 'checkbox',
      '#default_value' => isset($items[$delta]->getValue()['title']) ? $items[$delta]->getValue()['title'] : '',
      '#weight' => 21,
      '#states' => array(
        'visible' => array(
          ':input[name="' . $name . '"]' => array('!value' => '_none'),
        ),
      ),
    );

    $select_element['target_id']['#element_validate'][] = array(get_class($this), 'validateElement');
    $select_element['#attached']['library'][] = 'viewsreference/viewsreference';

    return $select_element;
  }

  public static function validateElement(array $element, FormStateInterface $form_state) {}

  /**
   *  AJAX function to get display IDs for a particular View
   */
  public function getDisplayIds(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $delta = $trigger['#delta'];
    $field_name = $trigger['#parents'][0];
    $values = $form_state->getValues();
    $parents = $trigger['#parents'];
    array_shift($parents);

    // Get the value for the target id of the View
    $entity_id = $this->getEntityId($values[$field_name], $parents);
    // The following is relevant if our field is nested inside other fields, eg paragraph or field collection
    if (count($parents) > 2) {
      $field_name = $parents[count($parents)-3];
    }

    // Obtain the display ids for the given View
    $options = $this->getViewDisplayIds($entity_id);
    // We recreate the same unique class as in the parent function
    $class = !empty($trigger['#field_parents']) ? implode('-',
        $trigger['#field_parents']) . '-' : '';
    $element_class = '.' . $class . $field_name  . '-' . $delta .
      '-display-id';

    // Construct the html
    $html = '<optgroup>';
    foreach ($options as $key => $option) {
      $html .= '<option value="' . $key . '">' . $option . '</option>';
    }
    $html .= '</optgroup>';
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand($element_class, render($html)));
    return $response;
  }

  /**
   * Helper function to get the current entity_id value from the values array based on parent array
   *
   * @param $values
   * @param $parents
   * @return array|bool
   */
  protected function getEntityId($values, $parents) {
    $key = array_shift($parents);
    \Drupal::logger('viewsreference')->notice('get key <pre>' . print_r($key,1));
    $values = $values[$key];
    \Drupal::logger('viewsreference')->notice('get parents <pre>' . print_r($parents,1));
    \Drupal::logger('viewsreference')->notice('get values <pre>' . print_r($values,1));
    if (is_array($values)) {
      $values = $this->getEntityId($values, $parents);
    }
    return $values;

  }

  /**
   * Helper function to get all display ids
   */
  protected function getAllViewsDisplayIds() {
    $views =  \Drupal\views\Views::getAllViews();
    $options = array();
    foreach ($views as $view) {
      foreach ($view->get('display') as $display) {
        $options[$display['id']] = $display['display_title'];
      }
    }
    return $options;
  }

  /**
   * Helper to get display ids for a particular View
   */
  protected function getViewDisplayIds($entity_id) {
    $views =  \Drupal\views\Views::getAllViews();
    $options = array();
    foreach ($views as $view) {
      if ($view->get('id') == $entity_id) {
        foreach ($view->get('display') as $display) {
          $options[$display['id']] = $display['display_title'];
        }
      }
    }
    return $options;
  }

}
