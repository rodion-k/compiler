<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Compiler\Element\BlockElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\BlockNode;
use Phug\Parser\Node\MixinNode;
use Phug\Parser\NodeInterface;

class BlockNodeCompiler extends AbstractNodeCompiler
{
    protected function compileAnonymousBlock(BlockNode $node, ElementInterface $parent = null)
    {
        $mixin = $node;
        while ($mixin->hasParent() && !($mixin instanceof MixinNode)) {
            $mixin = $mixin->getParent();
        }
        if (!($mixin instanceof MixinNode)) {
            $this->getCompiler()->throwException('Anonymous blocks are not allowed unless they are part of a mixin.');
        }

        $expression = new ExpressionElement('$__pug_children(get_defined_vars())');
        $expression->uncheck();
        $expression->preventFromTransformation();

        return $expression;
    }

    protected function hasBlockParent(BlockNode $node)
    {
        for ($blockParent = $node->getParent(); $blockParent; $blockParent = $blockParent->getParent()) {
            if ($blockParent instanceof BlockNode) {
                return true;
            }
        }

        return false;
    }

    protected function compileNamedBlock($name, BlockNode $node, ElementInterface $parent = null)
    {
        $compiler = $this->getCompiler();
        $layout = $compiler->getLayout();

        if ($layout && !$this->hasBlockParent($node)) {
            $blocks = &$layout->getCompiler()->getBlocksByName($name);
            array_walk($blocks, function (BlockElement $block) use ($node) {
                $block->proceedChildren(
                    $this->getCompiledChildren($node, $block->getParent()),
                    $node->getMode()
                );
            });

            return null;
        }

        $block = new BlockElement($compiler, $name, $node, $parent);

        return $block->proceedChildren(
            $this->getCompiledChildren($node, $parent),
            $node->getMode()
        );
    }

    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        if (!($node instanceof BlockNode)) {
            $this->getCompiler()->throwException(
                'Unexpected '.get_class($node).' given to block compiler.',
                $node
            );
        }

        /**
         * @var BlockNode $node
         */
        $name = $node->getName();

        return $name
            ? $this->compileNamedBlock($name, $node, $parent)
            : $this->compileAnonymousBlock($node, $parent);
    }
}
