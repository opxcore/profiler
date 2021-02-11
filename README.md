# Profiler

## Creating

Just create new profiler class instance, and it is ready to use. You can pass externally captured profiling start time
and memory usage. Otherwise, it will be automatically captured at profiler creation time.

Example:

```php
use OpxCore\Profiler\Profiler;

$startTime = hrtime(true);
$startMemory = memory_get_usage();

$profiler = new Profiler($startTime, $startMemory);
```

To disable profiling you should call `$profiler->enable(false)` or `$profiler->enable()` to enable it again. By default,
profiling is enabled.

## Profiling

There are two methods: `start($action)` and `stop($action)`. `$action` is a name for profiling entry. `start` captures
current time and memory usage (or externally captured can be used same as in a constructor). `stop` also captures time
and memory usage (or use externally captured) and writes profiling entry.

## Results

`$profiler->profiling()` returns full array with entries (or null if profiling is disabled) sorted by time when action
was fired.

Each entry is an array having next keys:

- `action_name` - Name of action you passed into `start()` and `stop()` function;
- `started_at` - Time when `start()` was fired related to start time passed to constructor (or captured there), or time
  of `stop()` was fired if there was no start fired for this action;
- `execution_time` - Time between start and stop, or null if there was no start;
- `used_memory` - Memory difference between start and stop, or null if there was no start;
- `total_memory` - Total memory usage at the action stop;
- `trace` - Stack trace of function calls, calling of profiler methods excluded (or externally captured and passed
  to `stop()` function as fourth argument).