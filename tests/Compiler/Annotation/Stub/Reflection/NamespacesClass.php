<?php

/**
 * documentation block
 */

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Reflection;

use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Reflection\DirA\Example3;
use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Reflection\DirA\Example4 as E4;

USE \Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Reflection\DirB;
USE \Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Reflection\DirC AS DirectoryC;

use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Reflection\DirD\Example7,
    Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Reflection\DirD\Example8 as E8,
    Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Reflection\DirD,
    Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Reflection\DirE as DirectoryE;

// additional comment

/**
 * @service
 */
class NamespacesClass
{
    /**
     * 1. Used current namespace
     *
     * @autowired
     *
     * @var Example1
     */
    protected $example1;

    /**
     * 2. Based from current namespace
     *
     * @autowired
     *
     * @var DirA\Example2
     */
    protected $example2;

    /**
     * 3. Used declared class in use statement
     *
     * @autowired
     *
     * @var Example3
     */
    protected $example3;

    /**
     * 4. Used declared class alias in use statement
     *
     * @autowired
     *
     * @var E4
     */
    protected $example4;

    /**
     * 5. Used declared folder in use statement
     *
     * @autowired
     *
     * @var DirB\Example5
     */
    protected $example5;

    /**
     * 6. Used declared folder alias in use statement
     *
     * @autowired
     *
     * @var DirectoryC\Example6
     */
    protected $example6;

    /**
     * 7. Used declared class in short use statement
     *
     * @autowired
     *
     * @var Example7
     */
    protected $example7;

    /**
     * 8.Used declared class alias in short use statement
     *
     * @autowired
     *
     * @var E8
     */
    protected $example8;

    /**
     * 9.Used declared folder in short use statement
     *
     * @autowired
     *
     * @var DirD\Example9
     */
    protected $example9;

    /**
     * 10. Used declared folder alias in short use statement
     *
     * @autowired
     *
     * @var DirectoryE\Example10
     */
    protected $example10;

    /**
     * 11. Used absolute declared class
     *
     * @autowired
     *
     * @var \Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Reflection\DirE\Example11
     */
    protected $example11;
}
