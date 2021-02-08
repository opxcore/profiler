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
    /** @var Profiler instance */
    protected static Profiler $profiler;

    /** @var array Profiling application */
    protected array $profiling = [];

    /** @var int External timestamp of profiling start */
    protected int $profilingStartTime;

    /** @var int External memory usage of profiling start */
    protected int $profilingStartMem;

    /** @var array Timestamp of application start */
    protected array $profilingStopWatches = [];

    /** @var bool Is profiling enabled */
    protected bool $profilingEnabled = true;

    /**
     * Create og get profiler singleton.
     *
     * @param int|null $startTime
     * @param int|null $startMem
     *
     * @return  Profiler
     */
    public static function getProfiler(?int $startTime = null, ?int $startMem = null): Profiler
    {
        if (self::$profiler === null) {
            self::$profiler = new self($startTime, $startMem);
        }

        return self::$profiler;
    }

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
        $this->profilingStartTime = $startTime ?? hrtime(true);
        $this->profilingStartMem = $startMem ?? memory_get_usage();
        self::$profiler = $this;
    }

    /**
     * Start profiling stopwatch.
     *
     * @param string $action
     *
     * @return  void
     */
    public function profilingStart(string $action): void
    {
        if (!$this->profilingEnabled) {
            return;
        }

        $this->profilingStopWatches[$action] = hrtime(true);
    }

    /**
     * Write action to profiling or get whole profiling list.
     *
     * @param string|null $action
     * @param int|null $timestamp
     * @param int|null $memory
     *
     * @return  void
     */
    public function profilingStop(?string $action = null, ?int $timestamp = null, ?int $memory = null): void
    {
        if ($this->profilingEnabled === false) {
            return;
        }
        $now = (int)hrtime(true);
        $executionTime = array_key_exists($action, $this->profilingStopWatches) ? ($now - $this->profilingStopWatches[$action]) : null;
        $timeStamp = $timestamp ?? ($now - $this->profilingStartTime - $executionTime ?? 0);
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

        unset($this->profilingStopWatches[$action]);
    }

    /**
     * Returns profiling list or set profiling mode enabled or disabled.
     *
     * @param bool|null $enable
     *
     * @return  array[]|null
     */
    public function profiling(?bool $enable = null): ?array
    {
        // If parameter is bool set profiling mode
        if (is_bool($enable)) {
            $this->profilingEnabled = $enable;

            return null;
        }

        if (!$this->profilingEnabled) {
            return null;
        }

        // Order profiled items by timestamp first
        usort($this->profiling, static function ($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });

        return $this->profiling;
    }
}