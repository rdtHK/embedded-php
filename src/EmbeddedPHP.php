<?php

declare(strict_types=1);

/**
 * Copyright 2017 Mário Camargo Palmeira
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Rdthk\EmbeddedPHP;

use Rdthk\EmbeddedPHP\Loaders\Loader;
use Rdthk\EmbeddedPHP\Exceptions\SyntaxException;
use function Rdthk\EmbeddedPHP\safe;

class EmbeddedPHP
{
    private $loader;
    private $globals;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
        $this->globals = ['ephp' => $this];
    }

    public function render(string $template, array $parameters=[])
    {
        $code = $this->loader->load($template);

        $php = $this->compile($code);

        $scope = function ($__CODE__, $__PARAMS__) {
            extract($__PARAMS__);
            eval($__CODE__);
        };

        $p = array_merge($parameters, $this->globals);

        call_user_func($scope, $php, $p);
    }

    public function setGlobal(string $name, $value)
    {
        $this->globals[$name] = $value;
    }

    public function getGlobal(string $name)
    {
        return $this->globals[$name];
    }

    private function compile(string $code)
    {
        $offset = 0;
        $php = '';
        $php .= 'use Rdthk\EmbeddedPHP\SafeString;';
        $php .= 'use function Rdthk\\EmbeddedPHP\\safe;';
        $php .= '?>';

        while (true) {
            $pos = strpos($code, '<%', $offset);

            if ($pos === false) {
                // after all <% %>s there's only text
                $php .= $this->compileString(substr($code, $offset));
                break;
            } else {
                $php .= $this->compileString(substr($code, $offset, $pos - $offset));

                $end = strpos($code, '%>', $pos);
                $offset = $end + 2; // after the %>

                if ($end === false) {
                    throw new SyntaxException(
                        "Missing %> at: " . substr($code, $pos, 20) . "...'"
                    );
                }

                if ($code[$pos + 2] === '=') {
                    // expression
                    $start = $pos + 3; // after the <%=
                    $php .= $this->compileExpression(substr($code, $start, $end - $start));
                } elseif ($code[$pos + 2] === '#') {
                    // comments are ignored
                } else {
                    // statement
                    $start = $pos + 2; // after the <%
                    $php .= $this->compileStatement(substr($code, $start, $end - $start));
                }
            }
        }

        return $php;
    }

    private function compileString(string $code)
    {
        return $code;
    }

    private function compileExpression(string $code)
    {
        return "<?=SafeString::print($code)?>";
    }

    private function compileStatement(string $code)
    {
        return "<?php $code ?>";
    }
}
