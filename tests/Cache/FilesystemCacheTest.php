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
use Rdthk\EmbeddedPHP\Cache\FilesystemCache;
use Rdthk\EmbeddedPHP\Exceptions\MissingPermissionException;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class FilesystemCacheTest extends TestCase
{
    public function testStore()
    {
        $root =  vfsStream::setup('root');
        $cache = new FilesystemCache(vfsStream::url('root'));
        $cache->store('foo', 'bar');
        $this->assertTrue($root->hasChild('foo'));
    }

    public function testExists()
    {
        vfsStream::setup('root', null, ['foo' => 'bar']);
        $cache = new FilesystemCache(vfsStream::url('root'));
        $this->assertTrue($cache->exists('foo'));
    }

    public function testLoad()
    {
        vfsStream::setup('root', null, ['foo' => 'bar']);
        $cache = new FilesystemCache(vfsStream::url('root'));
        $this->assertEquals('bar', $cache->load('foo'));
    }

    public function testNoWritePermissions()
    {
        vfsStream::setup('root', 0444); // -r--r--r--
        $this->expectException(MissingPermissionException::class);
        $cache = new FilesystemCache(vfsStream::url('root'));
    }

    public function testNoReadPermission()
    {
        vfsStream::setup('root', 0222); // --w--w--w-
        $this->expectException(MissingPermissionException::class);
        $cache = new FilesystemCache(vfsStream::url('root'));
    }

}
