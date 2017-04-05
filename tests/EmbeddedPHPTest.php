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

    public function testExpressionHtmlEscaping()
    {
        $ephp = new EmbeddedPHP(new StringLoader);

        ob_start();
        $ephp->render('Hello <%="<b>World</b>"%>!');
        $this->assertEquals('Hello &lt;b&gt;World&lt;/b&gt;!', ob_get_clean());
    }

    public function testExpressionHtmlSafe()
    {
        $ephp = new EmbeddedPHP(new StringLoader);

        ob_start();
        $ephp->render('Hello <%=safe("<b>World</b>")%>!');
        $this->assertEquals('Hello <b>World</b>!', ob_get_clean());
    }

    public function testComment()
    {
        $ephp = new EmbeddedPHP(new StringLoader);

        ob_start();
        $ephp->render('Hello <%#World%>!');
        $this->assertEquals('Hello !', ob_get_clean());
    }

    public function testStatement()
    {
        $ephp = new EmbeddedPHP(new StringLoader);

        ob_start();
        $ephp->render('Hello <%echo "World";%>!');
        $this->assertEquals('Hello World!', ob_get_clean());
    }

    public function testParameters()
    {
        $ephp = new EmbeddedPHP(new StringLoader);

        ob_start();
        $ephp->render('Hello <%=$x%>!', ['x' => 'World']);
        $this->assertEquals('Hello World!', ob_get_clean());
    }

    public function testInclude()
    {
        $ephp = new EmbeddedPHP(new StringLoader);

        ob_start();
        $ephp->render('Hello <% $ephp->render("World") %>!', ['x' => 'World']);
        $this->assertEquals('Hello World!', ob_get_clean());
    }

    public function testGlobals()
    {
        $ephp = new EmbeddedPHP(new StringLoader);
        $ephp->setGlobal('x', 'World');

        ob_start();
        $ephp->render('Hello <%=$x %>!');
        $this->assertEquals('Hello World!', ob_get_clean());
    }
}
