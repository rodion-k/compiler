<?php

namespace Phug\Test\Compiler;

use Phug\Compiler;
use Phug\Compiler\ElementCompiler;
use Phug\Parser\Node\DoNode;
use Phug\Test\AbstractCompilerTest;

/**
 * @coversDefaultClass \Phug\Compiler\ElementCompiler
 */
class ElementCompilerTest extends AbstractCompilerTest
{
    /**
     * @covers ::<public>
     */
    public function testCompile()
    {
        $this->assertCompile('<section><input /></section>', 'section: input');
        $this->assertCompile('<section></section>', 'section');
        $this->assertCompile('<section />', 'section/');
    }

    /**
     * @covers ::<public>
     */
    public function testExpansionCompile()
    {
        $this->assertCompile(
            '<ul><li class="list-item"><div class="foo"><div id="bar">baz</div></div></li></ul>',
            "ul\n  li.list-item: .foo: #bar baz"
        );
    }

    /**
     * @covers            ::<public>
     * @expectedException \Phug\CompilerException
     */
    public function testException()
    {
        $this->expectMessageToBeThrown(
            'Unexpected Phug\Parser\Node\DoNode '.
            'given to element compiler.'
        );

        $elementCompiler = new ElementCompiler(new Compiler());
        $elementCompiler->compileNode(new DoNode());
    }
}
