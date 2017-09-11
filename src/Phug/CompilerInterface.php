<?php

namespace Phug;

use Phug\Compiler\Element\BlockElement;
use Phug\Compiler\Layout;
use Phug\Compiler\NodeCompilerInterface;
use Phug\Formatter\ElementInterface;
use Phug\Parser\NodeInterface;
use Phug\Util\ModuleContainerInterface;

interface CompilerInterface extends ModuleContainerInterface
{
    /**
     * @return Parser
     */
    public function getParser();

    /**
     * @return Formatter
     */
    public function getFormatter();

    /**
     * @return Layout
     */
    public function getLayout();

    /**
     * @param Layout $layout
     *
     * @return mixed
     */
    public function setLayout(Layout $layout);

    public function pushPath($path);

    public function popPath();

    public function locate($path);

    public function resolve($path);

    /**
     * @param string                $className
     * @param NodeCompilerInterface $handler
     *
     * @return null|ElementInterface
     */
    public function setNodeCompiler($className, $handler);

    /**
     * @param string $name
     *
     * @return array
     */
    public function &getBlocksByName($name);

    /**
     * @return array
     */
    public function getBlocks();

    /**
     * @param BlockElement $block
     * @param array        $children
     */
    public function replaceBlock(BlockElement $block, array $children = null);

    /**
     * @throws CompilerException
     *
     * @return $this
     */
    public function compileBlocks();

    /**
     * @param array $blocks
     *
     * @return $this
     */
    public function importBlocks(array $blocks);

    /**
     * @param NodeInterface         $node
     * @param ElementInterface|null $parent
     *
     * @return null|ElementInterface
     */
    public function compileNode(NodeInterface $node, ElementInterface $parent = null);

    /**
     * @param string $input
     * @param string $path
     *
     * @return string
     */
    public function compile($input, $path = null);

    /**
     * @param string $path
     *
     * @return string
     */
    public function compileFile($path);

    /**
     * @param string $input
     * @param string $path
     *
     * @return null|ElementInterface
     */
    public function compileIntoElement($input, $path = null);

    /**
     * @param string $input pug input
     * @param string $path  optional path of the compiled source
     *
     * @throws CompilerException
     *
     * @return null|ElementInterface
     */
    public function compileDocument($input, $path = null);

    /**
     * @param string $path
     *
     * @return null|ElementInterface
     */
    public function compileFileIntoElement($path);

    /**
     * @return null|string
     */
    public function getPath();

    /**
     * @return NodeInterface
     */
    public function getImportNode();

    /**
     * @param NodeInterface $defaultYieldChildren
     *
     * @return $this
     */
    public function setImportNode(NodeInterface $defaultYieldChildren);

    /**
     * @param NodeInterface $yieldNode
     *
     * @return $this
     */
    public function setYieldNode(NodeInterface $yieldNode);

    /**
     * @return NodeInterface
     */
    public function getYieldNode();

    /**
     * @return $this
     */
    public function unsetYieldNode();

    /**
     * @return bool
     */
    public function isImportNodeYielded();

    /**
     * @param string $input pug input
     * @param string $path  optional path of the compiled source
     *
     * @return string
     */
    public function dump($input, $path = null);

    /**
     * @param string $path pug input file
     *
     * @return string
     */
    public function dumpFile($path);

    /**
     * @param string        $message  A meaningful error message
     * @param NodeInterface $node     Node generating the error
     * @param int           $code     Error code
     * @param \Throwable    $previous Source error
     *
     * @throws CompilerException
     */
    public function throwException($message, $node = null, $code = 0, $previous = null);
}
