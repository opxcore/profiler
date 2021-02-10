<?php
/*
 * This file is part of the OpxCore.
 *
 * Copyright (c) Lozovoy Vyacheslav <opxcore@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OpxCore\App;

use OpxCore\App\Interfaces\ProfilerInterface;

class Profiler implements ProfilerInterface
{
    /** @var array Profiling application */
    protected array $profiling = [];

    /** @var int External timestamp of profiling start */
    protected int $startTime;

    /** @var int External memory usage of profiling start */
    protected int $startMemory;

    /** @var array Timestamp of application start */
    protected array $actions = [];

    /** @var bool Is profiling enabled */
    protected bool $enabled = true;

    /**
     * Profiler constructor.
     *
     * @param int|null $startTime
     * @param int|null $startMem
     *
     * @return  void
     */
    public function __construct(?int $startTime = null, ?int $startMem = null)
    {
        $this->startTime = $startTime ?? hrtime(true);
        $this->startMemory = $startMem ?? memory_get_usage();
    }

    /**
     * Start profiling stopwatch.
     *
     * @param string $action
     *
     * @return  void
     */
    public function start(string $action): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->actions[$action] = hrtime(true);
    }

    /**
     * Write action to profiling or get whole profiling list.
     *
     * @param string $action
     * @param int|null $timestamp
     * @param int|null $memory
     *
     * @return  void
     */
    public function stop(string $action, ?int $timestamp = null, ?int $memory = null): void
    {
        if ($this->enabled === false) {
            return;
        }
        $now = (int)hrtime(true);
        $executionTime = array_key_exists($action, $this->actions) ? ($now - $this->actions[$action]) : null;
        $timeStamp = $timestamp ?? ($now - $this->startTime - $executionTime ?? 0);
        $stack = debug_backtrace(0);
        // Exclude profilingStop() function call from stacktrace
        array_shift($stack);

        $this->profiling[] = [
            'action' => $action,
            'timestamp' => $timeStamp,
            'time' => $executionTime,
            'memory' => $memory ?? memory_get_usage(),
            'stack' => $stack,
        ];

        unset($this->actions[$action]);
    }

    /**
     * Returns profiling list or set profiling mode enabled or disabled.
     *
     * @return  array[]|null
     */
    public function profiling(): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        // Order profiled items by timestamp
        usort($this->profiling, static function ($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });

        return $this->profiling;
    }

    /**
     * Enable or disable profiling.
     *
     * @param bool $enable
     *
     * @return  void
     */
    public function enable(bool $enable = true): void
    {
        $this->enabled = $enable;
    }
}