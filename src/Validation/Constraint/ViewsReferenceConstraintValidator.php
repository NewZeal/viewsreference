<?php

namespace Drupal\viewsreference\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the UniqueInteger constraint.
 */
class ViewsReferenceConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
//    ksm($items);
//    ksm($items->getSettings());
//    dpm('here');
//    \Drupal::logger('viewsreference')->notice('constraint triggered.');
    $this->context->addViolation($constraint->hasNoDisplay);
//    foreach ($items as $item) {
//      // First check if the value is an integer.
//      if (!is_int($item->value)) {
//        // The value is not an integer, so a violation, aka error, is applied.
//        // The type of violation applied comes from the constraint description
//        // in step 1.
//        $this->context->addViolation($constraint->hasNoDisplay, ['%value' => $item->value]);
//      }
//
//      // Next check if the value is unique.
//      if (!$this->isUnique($item->value)) {
//        $this->context->addViolation($constraint->hasNoDisplay, ['%value' => $item->value]);
//      }
//    }
  }

  private function isUnique($value) {
    // Here is where the check for a unique value would happen.
  }

}