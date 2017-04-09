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
use Rdthk\EmbeddedPHP\Cache\Cache;
use Rdthk\EmbeddedPHP\Cache\InMemoryCache;
use Rdthk\EmbeddedPHP\Exceptions\SyntaxException;

use Prophecy\Argument;
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

    public function testPhpTagsArePrinted()
    {
        $ephp = new EmbeddedPHP(new StringLoader);
        ob_start();
        $ephp->render('<?php ?>');
        $this->assertEquals('<?php ?>', ob_get_clean());
    }

    public function testPhpShortTagsArePrinted()
    {
        $ephp = new EmbeddedPHP(new StringLoader);
        ob_start();
        $ephp->render('<? ?>');
        $this->assertEquals('<? ?>', ob_get_clean());
    }

    public function testMissingCloseExpression()
    {
        $ephp = new EmbeddedPHP(new StringLoader);
        $this->expectException(SyntaxException::class);
        $ephp->render('<%="Hello"');
    }

    public function testMissingCloseStatement()
    {
        $ephp = new EmbeddedPHP(new StringLoader);
        $this->expectException(SyntaxException::class);
        $ephp->render('<% echo "Hello"');
    }

    public function testMissingCloseComment()
    {
        $ephp = new EmbeddedPHP(new StringLoader);
        $this->expectException(SyntaxException::class);
        $ephp->render('<%# Hello');
    }

    public function testLayout()
    {
        $ephp = new EmbeddedPHP(new StringLoader);
        $ephp->setLayout('Hello <% yield %>!');
        ob_start();
        $ephp->render('World');
        $this->assertEquals('Hello World!', ob_get_clean());
    }

    public function testLayoutNamedContentBlocks()
    {
        $ephp = new EmbeddedPHP(new StringLoader);
        $ephp->setLayout('Hello <% yield "foo" %>!');
        ob_start();
        $ephp->render('<% if (content("foo")) { %>World<% } %>');
        $this->assertEquals('Hello World!', ob_get_clean());
    }

    public function testLayoutUnnamedContentBlocks()
    {
        $ephp = new EmbeddedPHP(new StringLoader);
        $ephp->setLayout('Hello <% yield %>!');
        ob_start();
        $ephp->render('<% if (content("content")) { %>World<% } %>');
        $this->assertEquals('Hello World!', ob_get_clean());
    }

    public function testLayoutContentIsTheDefault()
    {
        $ephp = new EmbeddedPHP(new StringLoader);
        $ephp->setLayout('Hello <% yield %>!');
        ob_start();
        $ephp->render('<% if (content()) { %>World<% } %>');
        $this->assertEquals('Hello World!', ob_get_clean());
    }

    public function testLayoutHasAccessToGlobalScope()
    {
        $ephp = new EmbeddedPHP(new StringLoader);
        $ephp->setGlobal('foo', 'World');
        $ephp->setLayout('<% yield %> <%= $foo %>!');
        ob_start();
        $ephp->render('Hello');
        $this->assertEquals('Hello World!', ob_get_clean());
    }

    public function testCacheStore()
    {
        $cache = new InMemoryCache;
        ob_start();
        $ephp = new EmbeddedPHP(new StringLoader, $cache);
        $ephp->render('foo');
        ob_end_clean();
        $this->assertTrue($cache->exists('foo'));
    }

    public function testCacheLoad()
    {
        $cache = new InMemoryCache;
        $cache->store('foo', 'echo "Hello World!";');
        ob_start();
        $ephp = new EmbeddedPHP(new StringLoader, $cache);
        $ephp->render('foo');
        $this->assertEquals('Hello World!', ob_get_clean());
    }

}
