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
use Rdthk\EmbeddedPHP\Loaders\FileLoader;
use Rdthk\EmbeddedPHP\Exceptions\MissingTemplateException;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class FileLoaderTest extends TestCase
{
    public function testLoadingFileWithSingleFolder()
    {
        vfsStream::setup('root', null, ['foo.ephp' => 'Hello!']);
        $loader = new FileLoader(vfsStream::url('root'));
        $this->assertEquals('Hello!', $loader->load('foo'));
    }

    public function testLoadingFileWithMultipleFolders()
    {
        vfsStream::setup('root', null, ['foo.ephp' => 'Hello!']);
        $loader = new FileLoader(vfsStream::url('other'), vfsStream::url('root'));
        $this->assertEquals('Hello!', $loader->load('foo'));
    }

    public function testFolderWithTrailingSlash()
    {
        vfsStream::setup('root', null, ['foo.ephp' => 'Hello!']);
        $loader = new FileLoader(vfsStream::url('root/'));
        $this->assertEquals('Hello!', $loader->load('foo'));
    }

    public function testMissingFile()
    {
        vfsStream::setup('root', null, []);
        $loader = new FileLoader(vfsStream::url('root'));

        $this->expectException(MissingTemplateException::class);
        $loader->load('foo');
    }

}
