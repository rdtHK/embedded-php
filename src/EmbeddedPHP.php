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
use Rdthk\EmbeddedPHP\Cache\Cache;
use Rdthk\EmbeddedPHP\Exceptions\SyntaxException;
use function Rdthk\EmbeddedPHP\safe;

class EmbeddedPHP
{
    private $loader;
    private $cache;
    private $globals;
    private $layout;

    public function __construct(Loader $loader, ?Cache $cache=null)
    {
        $this->loader = $loader;
        $this->cache = $cache;
        $this->globals = ['ephp' => $this];
        $this->layout = null;
    }

    public function render(string $template, array $parameters=[])
    {
        $php = $this->loadTemplate($template);
        $scope = function ($__CODE__, $__PARAMS__) {
            extract($__PARAMS__);
            eval($__CODE__);
        };
        $params = array_merge($parameters, $this->globals);

        if ($this->layout === null) {
            call_user_func($scope, $php, $params);
        } else {
            $layout = eval($this->loadLayout($this->layout));
            $layout = call_user_func($layout, $this->globals);

            foreach ($layout as $block) {
                $GLOBALS['__EPHP_CONTENT_BLOCK__'] = $block ?? 'content';
                call_user_func($scope, $php, $params);
            }
        }
    }

    private function loadTemplate(string $name)
    {
        if ($this->cache && $this->cache->exists($name)) {
            $code = $this->cache->load($name);
        } else {
            $code = $this->compileTemplate($name);

            if ($this->cache) {
                $this->cache->store($name, $code);
            }
        }

        return $code;
    }

    private function loadLayout(string $name)
    {
        if ($this->cache && $this->cache->exists($name)) {
            $code = $this->cache->load($name);
        } else {
            $code = $this->compileLayout($name);

            if ($this->cache) {
                $this->cache->store($name, $code);
            }
        }

        return $code;
    }

    public function setGlobal(string $name, $value)
    {
        $this->globals[$name] = $value;
    }

    public function getGlobal(string $name)
    {
        return $this->globals[$name];
    }

    public function setLayout(?string $layout)
    {
        $this->layout = $layout;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    private function compile(string $code)
    {
        $offset = 0;
        $php = '';

        while (true) {
            $pos = strpos($code, '<%', $offset);

            if ($pos === false) {
                // after all <% %>s there's only text
                $php .= $this->compileString(substr($code, $offset));
                break;
            } else {
                // grabbing the bit of text between <% %>s
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
        $str = str_replace('\\', '\\\\', $code);
        $str = str_replace('\'', '\\\'', $str);
        return "echo '$str';";
    }

    private function compileExpression(string $code)
    {
        return "SafeString::print($code);";
    }

    private function compileStatement(string $code)
    {
        return "$code;";
    }

    private function useStatements()
    {
        $php  = 'use Rdthk\\EmbeddedPHP\\SafeString;';
        $php .= 'use function Rdthk\\EmbeddedPHP\\safe;';
        $php .= 'use function Rdthk\\EmbeddedPHP\\content;';
        return $php;
    }

    private function compileTemplate(string $code)
    {
        $php  = $this->useStatements();
        $php .= $this->compile($code);
        return $php;
    }

    private function compileLayout(string $code)
    {
        $php  = $this->useStatements();
        $php .= 'return function ($__PARAMS__) {';
        $php .= 'extract($__PARAMS__);';
        $php .= $this->compile($code);
        $php .= '};';
        return $php;
    }
}
