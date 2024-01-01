<?php

/**
 * This file is part of the Phalcon Developer Tools.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\DevTools\Validation\Validator;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Messages\Message;

use function preg_match;

/**
 * Check for namespace
 *
 *<code>
 *use Phalcon\DevTools\Validation\Validator\Namespaces as NSValidator;
 *
 *$validation->add('namespace', new Namespaces(array(
 *    'allowEmpty' => true,
 *    'message' => ':field must be a valid namespace'
 *)));
 *</code>
 */
class Namespaces extends AbstractValidator
{
    /**
     * Executes the namespaces validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     */
    public function validate(Validation $validation, $field): bool
    {
        $value = $validation->getValue($field);

        if ($this->hasOption('allowEmpty') && empty($value)) {
            return true;
        }

        $re = '#^(?:(?:\\\)?[a-z](?:[a-z0-9_]+)?)+(?:\\\\(?:[a-z](?:[a-z0-9_]+)?)+)*$#i';

        if (!preg_match($re, $value)) {
            $label        = $this->getOption('label') ?: $validation->getLabel($field);
            $message      = $this->getOption('message') ?: 'Invalid namespace syntax!';
            $replacePairs = [':field' => $label];

            $validation->appendMessage(new Message(strtr($message, $replacePairs), $field, 'Namespaces'));
            return false;
        }

        return true;
    }
}
