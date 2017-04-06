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

namespace Rdthk\EmbeddedPHP\Cache;

use Rdthk\EmbeddedPHP\Exceptions\MissingPermissionException;

class FilesystemCache implements Cache
{
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;

        if (!is_writable($path)) {
            throw new MissingPermissionException(
                "Cannot write to '$path'"
            );
        }

        if (!is_readable($path)) {
            throw new MissingPermissionException(
                "Cannot read from '$path'"
            );
        }
    }

    public function exists(string $template): bool
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $template;
        return file_exists($path);
    }

    public function load(string $template): ?string
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $template;
        return file_get_contents($path);
    }

    public function store(string $template, string $code): void
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $template;
        file_put_contents($path, $code);
    }
}
