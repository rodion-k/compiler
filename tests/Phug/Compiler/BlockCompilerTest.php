<?php

namespace Phug\Test\Compiler;

use Phug\Compiler;
use Phug\Compiler\BlockCompiler;
use Phug\Parser\Node\BlockNode;
use Phug\Parser\Node\ElementNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\BlockCompiler
 */
class BlockCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers                   ::<public>
     * @expectedException        \Phug\CompilerException
     * @expectedExceptionMessage Unexpected Phug\Parser\Node\ElementNode
     * @expectedExceptionMessage given to block compiler.
     */
    public function testException()
    {
        $blockCompiler = new BlockCompiler(new Compiler());
        $blockCompiler->compileNode(new ElementNode());
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Compiler\Block::<public>
     */
    public function testBlock()
    {
        $this->assertCompile(
            [
                '<div>',
                '<p>Foo</p>',
                '</div>',
            ],
            [
                "div\n",
                "  block place\n",
                '    p Foo',
            ]
        );
    }

    /**
     * @covers                   \Phug\Compiler::compileBlocks
     * @expectedException        \Phug\CompilerException
     * @expectedExceptionMessage Unexpected block for the name foo
     */
    public function testCompileBlocksException()
    {
        include_once __DIR__.'/TestBlockCompiler.php';
        $compiler = new Compiler([
            'node_compilers' => [
                BlockNode::class => TestBlockCompiler::class,
            ]
        ]);
        $compiler->compile('block foo');
    }
}
