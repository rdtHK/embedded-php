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
use function Rdthk\EmbeddedPHP\safe;

class EmbeddedPHP
{
    private $loader;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    public function render(string $template)
    {
        $raw = $this->loader->load($template);

        $code = $this->compile($raw);

        $scope = function ($__CODE__) {
            eval($__CODE__);
        };

        call_user_func($scope, $code);
    }

    private function compile(string $raw)
    {
        $offset = 0;
        $code = '';
        $code .= 'use Rdthk\EmbeddedPHP\SafeString;';
        $code .= 'use function Rdthk\\EmbeddedPHP\\safe;';
        $code .= '?>';

        while (true) {
            $pos = strpos($raw, '<%=', $offset);

            if ($pos === false) {
                // after all <% %>s there's only text
                $code .= $this->compileString(substr($raw, $offset));
                break;
            } else {
                // text between expressions
                $code .= $this->compileString(substr($raw, $offset, $pos - $offset));

                $start = $pos + 3; // after the <%=
                $end = strpos($raw, '%>', $start);

                if ($end === false) {
                    throw new \Exception; // TODO: Better exception + error message
                }

                $code .= $this->compileExpression(substr($raw, $start, $end - $start));
                $offset = $end + 2; // after the %>
            }
        }

        return $code;
    }

    private function compileString(string $raw)
    {
        return $raw;
    }

    private function compileExpression(string $raw)
    {
        return "<?=SafeString::print($raw)?>";
    }
}
