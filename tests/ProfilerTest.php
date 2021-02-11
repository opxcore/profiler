<?php
/*
 * This file is part of the OpxCore.
 *
 * Copyright (c) Lozovoy Vyacheslav <opxcore@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OpxCore\Tests\Profiler;

use OpxCore\Profiler\Profiler;
use PHPUnit\Framework\TestCase;

class ProfilerTest extends TestCase
{
    public function testBasic(): void
    {
        $profiler = new Profiler;
        $profiler->enable();
        $profiler->start('test');
        $profiler->stop('test');
        $profiler->start('test_2');
        $profiler->stop('test_2');

        self::assertIsArray($profiler->profiling());
    }

    public function testDisabled(): void
    {
        $profiler = new Profiler;
        $profiler->enable(false);
        $profiler->start('test');
        $profiler->stop('test');
        $profiler->start('test_2');
        $profiler->stop('test_2');

        self::assertNull($profiler->profiling());
    }

    public function testCalc(): void
    {
        $profiler = new Profiler(0 ,0);
        $profiler->start('test', 10, 10);
        $profiler->stop('test', 20, 20);

        $all = $profiler->profiling();
        $entry = array_shift($all);

        self::assertEquals('test', $entry['action_name']);
        self::assertEquals(10, $entry['started_at']);
        self::assertEquals(10, $entry['execution_time']);
        self::assertEquals(10, $entry['used_memory']);
        self::assertEquals(20, $entry['total_memory']);
    }
}
