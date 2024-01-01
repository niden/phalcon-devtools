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

namespace Phalcon\DevTools\Builder\Project;

use Phalcon\Config\Config;

use function fclose;
use function file_exists;
use function file_get_contents;
use function fopen;
use function fwrite;
use function json_decode;
use function mkdir;
use function preg_quote;
use function preg_replace;
use function realpath;
use function strtolower;
use function touch;
use function trim;
use function ucfirst;

use const DIRECTORY_SEPARATOR;

/**
 * Abstract Builder to create application skeletons
 */
abstract class AbstractProjectBuilder
{
    /**
     * Project directories
     *
     * @var array
     */
    protected array $projectDirectories = [];
    /**
     * Stores variable values depending on parameters
     *
     * @var array
     */
    protected array $variableValues = [];

    public function __construct(
        protected Config $options
    ) {
    }

    /**
     * Build Project
     *
     * @return mixed
     */
    abstract public function build();

    /**
     * Build project directories
     *
     * @return $this
     */
    public function buildDirectories()
    {
        foreach ($this->projectDirectories as $dir) {
            mkdir(realpath($this->options->get('projectPath')) . DIRECTORY_SEPARATOR . $dir, 0777, true);
        }

        return $this;
    }

    /**
     * Generate file $putFile from $getFile, replacing @@variableValues@@
     *
     * @param string $getFile From file
     * @param string $putFile To file
     * @param string $name
     *
     * @return $this
     */
    protected function generateFile($getFile, $putFile, $name = '')
    {
        if (!file_exists($putFile)) {
            touch($putFile);
            $fh = fopen($putFile, "w+");

            $str = file_get_contents($getFile);
            if ($name) {
                $namespace = ucfirst($name);
                if (strtolower(trim($name)) == 'default') {
                    $namespace = 'MyDefault';
                }

                $str = preg_replace('/@@name@@/', $name, $str);
                $str = preg_replace('/@@namespace@@/', $namespace, $str);
            }

            if (sizeof($this->variableValues) > 0) {
                foreach ($this->variableValues as $variableValueKey => $variableValue) {
                    $variableValueKeyRegEx = '/@@' . preg_quote($variableValueKey, '/') . '@@/';
                    $str                   = preg_replace($variableValueKeyRegEx, $variableValue, $str);
                }
            }

            fwrite($fh, $str);
            fclose($fh);
        }

        return $this;
    }

    /**
     * Generate variable values depending on parameters
     *
     * return $this
     */
    protected function getVariableValues()
    {
        $variableValuesResult = [];
        $variablesJsonFile    =
            $this->options->get('templatePath') . DIRECTORY_SEPARATOR
            . 'project' . DIRECTORY_SEPARATOR
            . $this->options->get('type') . DIRECTORY_SEPARATOR .
            'variables.json';

        if (file_exists($variablesJsonFile)) {
            $variableValues = json_decode(file_get_contents($variablesJsonFile), true);
            if ($variableValues) {
                foreach ($this->options as $k => $option) {
                    if (!isset($variableValues[$k])) {
                        continue;
                    }
                    $valueKey             = $option ? 'true' : 'false';
                    $variableValuesResult = $variableValues[$k][$valueKey];
                }
            }
            $this->variableValues = $variableValuesResult;
        }

        return $this;
    }
}
