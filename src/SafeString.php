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


class SafeString
{
    private $str;

    public function __construct($str)
    {
        $this->str = (string) $str;
    }

    public static function print($str): void
    {
        if ($str instanceof SafeString) {
            echo $str;
        } else {
            echo htmlentities($str);
        }
    }

    public function __toString()
    {
        return $this->str;
    }
}
