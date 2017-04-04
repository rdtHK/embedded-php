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
use Rdthk\EmbeddedPHP\EmbeddedPHP;
use Rdthk\EmbeddedPHP\Loaders\StringLoader;

use PHPUnit\Framework\TestCase;

class EmbeddedPhpTest extends TestCase
{
    public function testSimpleTemplate()
    {
        $ephp = new EmbeddedPHP(new StringLoader);

        ob_start();
        $ephp->render('Hello!');
        $this->assertEquals('Hello!', ob_get_clean());
    }

    public function testExpression()
    {
        $ephp = new EmbeddedPHP(new StringLoader);

        ob_start();
        $ephp->render('Hello <%="World"%>!');
        $this->assertEquals('Hello World!', ob_get_clean());
    }

}
