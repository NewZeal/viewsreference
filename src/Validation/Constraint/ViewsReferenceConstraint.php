<?php

namespace Drupal\viewsreference\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
* Checks that the submitted value is a unique integer
*
* @Constraint(
*   id = "viewsreference_validation",
*   label = @Translation("Views reference entity reference validation", context = "Validation"),
* )
*/
class ViewsReferenceConstraint extends Constraint
{
// The message that will be shown if the value is not an integer
  public $hasNoDisplay = 'This view has no available display.  Try another view.';

// The message that will be shown if the value is not unique
  public $notUnique = '%value is not unique';
}