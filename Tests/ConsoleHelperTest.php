<?php
/**
 * Highlighter
 *
 * Copyright (C) 2016, Some right reserved.
 *
 * @author Kacper "Kadet" Donat <kacper@kadet.net>
 *
 * Contact with author:
 * Xmpp: me@kadet.net
 * E-mail: contact@kadet.net
 *
 * From Kadet with love.
 */

namespace Kadet\Highlighter\Tests;

use Kadet\Highlighter\Utils\ConsoleHelper;

class ConsoleHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testReset()
    {
        $console = new ConsoleHelper();
        $this->assertEquals("\033[0m", $console->reset());
    }

    public function testStyled()
    {
        $console = new ConsoleHelper();
        $this->assertEquals("\e[0m\e[31mtest\033[0m", $console->styled(["color" =>"red"], "test"));
    }

    public function testStacking()
    {
        $console = new ConsoleHelper();
        $this->assertEquals(
            "\e[0m\e[31mtest\e[0m\e[32mtest2\e[0m\e[0m\e[31mtest3\e[0m",
            $console->open(["color" => "red"]).
                "test".
                    $console->open(["color" => "green"])."test2".$console->close().
                "test3".
            $console->close()
        );
    }

    /**
     * @dataProvider stylesProvider
     */
    public function testStyles($expected, $style)
    {
        $console = new ConsoleHelper();
        $this->assertEquals($expected, $console->open($style));
    }

    public function stylesProvider()
    {
        return [
            'background red' => ["\e[0m\033[41m", ["background" => "red"]],
            'bold'           => ["\e[0m\033[1m",  ["bold" => true]],
            'dim'            => ["\e[0m\033[2m",  ["dim" => true]],
            'underline'      => ["\e[0m\033[4m",  ["underline" => true]],
            'blink'          => ["\e[0m\033[5m",  ["blink" => true]],
            'invert'         => ["\e[0m\033[7m",  ["invert" => true]],

            'wrong'         => [null,  ["wrong" => true]],

            'bg and bold' => ["\e[0m\033[41;1m",  ["background" => "red", 'bold' => true]],

            'color, bg and bold' => ["\e[0m\033[31;41;1m",  ["color" => "red", "background" => "red", 'bold' => true]],
        ];
    }
}
