<?php

namespace Phug\Test;

use Phug\Compiler;
use Phug\CompilerModuleInterface;
use Phug\Formatter;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Parser;
use Phug\Parser\Node\ElementNode;

/**
 * @coversDefaultClass \Phug\Compiler
 */
class CompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     * @covers ::__construct
     */
    public function testGetters()
    {
        $compiler = new Compiler();

        self::assertInstanceOf(Formatter::class, $compiler->getFormatter());
        self::assertInstanceOf(Parser::class, $compiler->getParser());
    }

    /**
     * @covers ::compileNode
     * @covers ::getNamedCompiler
     * @covers ::__construct
     */
    public function testCompileNode()
    {
        $compiler = new Compiler();

        self::assertInstanceOf(Formatter::class, $compiler->getFormatter());
        self::assertInstanceOf(Parser::class, $compiler->getParser());

        $section = new ElementNode();
        $section->setName('section');

        /**
         * @var MarkupElement $section
         */
        $section = $compiler->compileNode($section);

        self::assertInstanceOf(MarkupElement::class, $section);
        self::assertSame('section', $section->getName());
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Compiler\AbstractNodeCompiler::compileParserNode
     * @covers \Phug\Compiler\AbstractNodeCompiler::<public>
     * @covers \Phug\Compiler\NodeCompiler\DoctypeNodeCompiler::<public>
     * @covers ::__construct
     */
    public function testCompile()
    {
        // Empty string
        $this->assertCompile('', '');

        // Single tag
        $this->assertCompile('<html></html>', 'html');

        // Children
        $this->assertCompile([
            '<html>',
            '<head></head>',
            '<body></body>',
            '</html>',
        ], [
            "html\n",
            "  head\n",
            "  body\n",
        ]);

        // Doctype
        $this->assertCompile(
            '<!DOCTYPE html><html><input></html>',
            "doctype html\n".
            "html\n".
            '  input'
        );
        $this->assertCompile([
            '<!DOCTYPE html>',
            '<html><input></html>',
            '<!DOCTYPE foobar>',
            '<html><input /></html>',
            '<?xml version="1.0" encoding="utf-8" ?>',
            '<html><input></input></html>',
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
            '<html><input /></html>',
        ], [
            "doctype html\n",
            "html: input\n",
            "doctype foobar\n",
            "html: input\n",
            "doctype xml\n",
            "html: input\n",
            "doctype 1.1\n",
            "html: input\n",
        ]);
    }

    /**
     * @covers \Phug\Compiler\AbstractNodeCompiler::<public>
     */
    public function testGetCompiledChildren()
    {
        $forCompiler = new Compiler\NodeCompiler\ForNodeCompiler($this->compiler);
        $elementNode = new ElementNode();
        $elementNode->setName('section');
        $for = new CodeElement('foreach ($groups as $group)', null, null, [
            new MarkupElement('article'),
            $elementNode,
        ]);
        $compiledChildren = $forCompiler->getCompiledChildren($for, null);

        self::assertSame(1, count($compiledChildren));
        self::assertInstanceOf(MarkupElement::class, $compiledChildren[0]);
        /**
         * @var MarkupElement $markup
         */
        $markup = $compiledChildren[0];
        self::assertSame('section', $markup->getName());
    }

    /**
     * @covers            ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testParserClassException()
    {
        $this->expectMessageToBeThrown(
            'Passed parser class '.
            'Phug\Parser\Node\ElementNode '.
            'is not a valid '.
            'Phug\Parser'
        );

        new Compiler([
            'parser_class_name' => ElementNode::class,
        ]);
    }

    /**
     * @covers            ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testFormatterClassException()
    {
        $this->expectMessageToBeThrown(
            'Passed formatter class '.
            'Phug\Parser\Node\ElementNode '.
            'is not a valid '.
            'Phug\Formatter'
        );

        new Compiler([
            'formatter_class_name' => ElementNode::class,
        ]);
    }

    /**
     * @covers            ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testLocatorClassException()
    {
        $this->expectMessageToBeThrown(
            'Passed locator class Phug\Parser\Node\ElementNode is not a valid Phug\Compiler\LocatorInterface'
        );

        new Compiler([
            'locator_class_name' => ElementNode::class,
        ]);
    }

    /**
     * @covers            ::setNodeCompiler
     * @expectedException \InvalidArgumentException
     */
    public function testSetNodeCompilerException()
    {
        $this->expectMessageToBeThrown(
            'Passed node compiler needs to implement '.
            'Phug\Compiler\NodeCompilerInterface. Phug\Parser\Node\ElementNode given.'
        );

        $compiler = new Compiler();
        $compiler->setNodeCompiler(ElementNode::class, ElementNode::class);
    }

    /**
     * @covers            ::compileNode
     * @expectedException \Phug\CompilerException
     */
    public function testCompileNodeException()
    {
        $this->expectMessageToBeThrown(
            'No compiler found able to compile '.
            'Phug\Test\TestNode'
        );

        include_once __DIR__.'/Node/TestNode.php';
        $compiler = new Compiler();
        $compiler->compileNode(new TestNode());
    }

    /**
     * @covers            ::locate
     * @covers            ::resolve
     * @covers            \Phug\Compiler\NodeCompiler\ImportNodeCompiler::compileNode
     * @expectedException \Phug\CompilerException
     */
    public function testAbsolutePathWithoutPaths()
    {
        $this->expectMessageToBeThrown(
            'Either the "basedir" or "paths" option is required'.
            ' to use includes and extends with "absolute" paths'
        );

        $compiler = new Compiler();
        $compiler->compile('include /foo.pug');
    }

    /**
     * @covers ::locate
     */
    public function testLocate()
    {
        $compiler = new Compiler(['paths' => [__DIR__.'/../templates/example-structure/views']]);

        self::assertStringEndsWith('/views/index.pug', str_replace('\\', '/', $compiler->locate('index')));
        self::assertStringEndsWith('/views/index.pug', str_replace('\\', '/', $compiler->locate('index.pug')));
    }

    /**
     * @covers ::resolve
     */
    public function testResolve()
    {
        $compiler = new Compiler(['paths' => [__DIR__.'/../templates/example-structure/views']]);

        self::assertStringEndsWith('/views/index.pug', str_replace('\\', '/', $compiler->resolve('index')));
        self::assertStringEndsWith('/views/index.pug', str_replace('\\', '/', $compiler->resolve('index.pug')));
    }

    /**
     * @covers ::resolve
     * @expectedException \Phug\CompilerException
     * @expectedExceptionMessage Source file not-existent not found
     */
    public function testResolveNotFoundException()
    {
        $compiler = new Compiler(['paths' => [__DIR__.'/../templates/example-structure/views']]);
        $compiler->resolve('not-existent');
    }

    /**
     * @group hooks
     * @covers ::compileNode
     * @covers ::compile
     * @covers ::__construct
     * @covers \Phug\Compiler\Event\ElementEvent::getNodeEvent
     */
    public function testHooks()
    {
        $compiler = new Compiler([
            'on_node'  => function (Compiler\Event\NodeEvent $event) {
                $node = $event->getNode();
                if ($node instanceof ElementNode) {
                    $node->setName($node->getName().'b');
                }
            },
            'on_element' => function (Compiler\Event\ElementEvent $event) {
                $element = $event->getElement();
                if ($element instanceof MarkupElement) {
                    $element->setName($element->getName().'c'.$event->getNodeEvent()->getNode()->getName());
                }
            },
        ]);

        self::assertSame('<abcab></abcab>', $compiler->compile('a'));

        $compiler = new Compiler([
            'on_compile'  => function (Compiler\Event\CompileEvent $event) {
                $event->setInput($event->getInput().' Hello');
            },
            'on_output' => function (Compiler\Event\OutputEvent $event) {
                $event->setOutput('<p>'.$event->getOutput().'</p>');
            },
        ]);

        self::assertSame('<p><a>Hello</a></p>', $compiler->compile('a'));

        $this->enableJsPhpize();

        $this->assertRender('<p>Hello</p>', 'p=foo.bar', [], [
            'foo' => [
                'bar' => 'Hello',
            ],
        ]);
    }

    /**
     * @covers \Phug\Compiler::dumpFile
     * @covers \Phug\Compiler::dump
     */
    public function testDump()
    {
        $dump = $this->compiler->dumpFile(__DIR__.'/../templates/page.pug');

        self::assertSame(implode("\n", [
            'Document: document',
            '  Markup: section',
            '    Text',
            '    Text',
            '    Text',
            '    Text',
        ]), $dump);
    }

    /**
     * @covers ::getModuleBaseClassName
     */
    public function testGetModuleBaseClassName()
    {
        self::assertSame(CompilerModuleInterface::class, (new Compiler())->getModuleBaseClassName());
    }

    /**
     * @covers ::hasFilter
     * @covers ::getFilter
     * @covers ::setFilter
     * @covers ::unsetFilter
     */
    public function testFilters()
    {
        $compiler = new Compiler([
            'filters' => [
                'a' => 'A',
                'b' => 'B',
            ],
            'filter_resolvers' => [
                function ($name) {
                    return ctype_digit($name) ? $name * 2 : null;
                },
            ],
        ]);

        self::assertTrue($compiler->hasFilter('a'));
        self::assertTrue($compiler->hasFilter('b'));
        self::assertFalse($compiler->hasFilter('c'));
        self::assertTrue($compiler->hasFilter('1'));
        self::assertTrue($compiler->hasFilter('4'));
        self::assertSame('A', $compiler->getFilter('a'));
        self::assertSame('B', $compiler->getFilter('b'));
        self::assertSame(null, $compiler->getFilter('c'));
        self::assertSame(42, $compiler->getFilter('21'));
        self::assertSame(0, $compiler->getFilter('0'));

        $compiler->setFilter('c', 'C');

        self::assertTrue($compiler->hasFilter('c'));
        self::assertSame('C', $compiler->getFilter('c'));

        $compiler->unsetFilter('c');

        self::assertFalse($compiler->hasFilter('c'));
        self::assertSame(null, $compiler->getFilter('c'));
    }

    /**
     * @covers ::throwException
     * @expectedException \Phug\CompilerException
     */
    public function testThrowException()
    {
        $compiler = new Compiler();
        $compiler->throwException('Test Exception');
    }

    /**
     * @covers ::assert
     */
    public function testAssertSuccess()
    {
        $compiler = new Compiler();
        $compiler->assert(true, 'Test Exception');
    }

    /**
     * @covers ::assert
     * @expectedException \Phug\CompilerException
     */
    public function testAssertFailure()
    {
        $compiler = new Compiler();
        $compiler->assert(false, 'Test Exception');
    }
}
