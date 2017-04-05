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

namespace Rdthk\EmbeddedPHP\Loaders;

use Rdthk\EmbeddedPHP\Exceptions\MissingTemplateException;

class FileLoader implements Loader
{
    public function __construct(string ...$dirs)
    {
        $this->dirs = $dirs;
    }

    public function load(string $template): string
    {
        foreach ($this->dirs as $dir) {
            $path = $dir . DIRECTORY_SEPARATOR . $template . '.ephp';

            if (file_exists($path)) {
                return file_get_contents($path);
            }
        }

        throw new MissingTemplateException($path);
    }
}
