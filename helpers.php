<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Timer\ResourceUsageFormatter;
use SebastianBergmann\Timer\Timer;

if (! function_exists('environment')) {
    function environment(): string
    {
        if (defined('STDIN')) {
            return 'cli';
        }

        if ('cli' === PHP_SAPI) {
            return 'cli';
        }

        if (stripos(PHP_SAPI, 'cgi') !== false && getenv('TERM')) {
            return 'cli';
        }

        if (empty($_SERVER['REMOTE_ADDR']) && ! isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0) {
            return 'cli';
        }

        return 'web';
    }
}

if (! function_exists('format_bits')) {
    function format_bits(int $bits, $precision = 2, $suffix = true)
    {
        if ($bits > 0) {
            $i = floor(log($bits) / log(1000));

            if (! $suffix) {
                return round($bits / (1000 ** $i), $precision);
            }

            $sizes = ['B', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'];

            return sprintf('%.02F', round($bits / (1000 ** $i), $precision)) * 1 .' '.@$sizes[$i];
        }

        return 0;
    }
}

if (! function_exists('format_bytes')) {
    function format_bytes(int $bytes, $precision = 2)
    {
        if ($bytes > 0) {
            $i = floor(log($bytes) / log(1024));

            $sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

            return sprintf('%.02F', round($bytes / (1024 ** $i), $precision)) * 1 .' '.@$sizes[$i];
        }

        return 0;
    }
}

if (! function_exists('bytes_to_bits')) {
    function bytes_to_bits(int $bytes)
    {
        if ($bytes > 0) {
            return $bytes * 8;
        }

        return 0;
    }
}

if (! function_exists('memoize')) {
    function memoize(callable $function): callable
    {
        return function () use ($function) {
            static $cache = [];

            $args = func_get_args();
            $key = serialize($args);
            $cached = true;

            if (! isset($cache[$key])) {
                $cache[$key] = $function(...$args);
                $cached = false;
            }

            return ['result' => $cache[$key], 'cached' => $cached];
        };
    }
}

if (! function_exists('once')) {
    function once(callable $function): callable
    {
        return function (...$args) use ($function) {
            static $called = false;
            if ($called) {
                return;
            }
            $called = true;

            return $function(...$args);
        };
    }
}

if (! function_exists('is_json')) {
    /**
     * If the string is valid JSON, return true, otherwise return false
     *
     * @param  string  $str the string to check
     * @return bool the function is_json() is returning a boolean value
     */
    function is_json(string $str): bool
    {
        json_decode($str);

        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (! function_exists('validate')) {
    function validate(array $data = [], array $rules = [], array $messages = [], array $customAttributes = []): array
    {
        return validator($data, $rules, $messages, $customAttributes)->validate();
    }
}

if (! function_exists('array_filter_filled')) {
    function array_filter_filled(array $array): array
    {
        return array_filter($array, fn ($item) => filled($item));
    }
}

if (! function_exists('call')) {
    function call($callback, array $parameters = [], string $defaultMethod = null): void
    {
        app()->call($callback, $parameters, $defaultMethod);
    }
}

if (! function_exists('catch_resource_usage')) {
    function catch_resource_usage($callback, ...$parameter): string
    {
        $timer = new Timer();
        $timer->start();

        app()->call($callback, $parameter);

        return (new ResourceUsageFormatter())->resourceUsage($timer->stop());
    }
}

if (! function_exists('resolve_class_from')) {
    function resolve_class_from(string $path, string $vendorPath = null, string $vendorNamespace = null): string
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $vendorPath = $vendorPath ? realpath($vendorPath) : app_path();
        $vendorNamespace ??= config('modules.namespace'); // App\

        return str(realpath($path))
            ->replaceFirst($vendorPath, $vendorNamespace)
            ->replaceLast('.php', '')
            ->replace(DIRECTORY_SEPARATOR, '\\')
            ->replace('\\\\', '\\')
            ->start('\\')
            ->toString();
    }
}

/*
 * 写入日志
 *
 * @note single 创建单个日志文件 daily 定期清理日志文件
 */
if (! function_exists('write_log')) {
    function write_log($path, $data, $context = [], $config = []): void
    {
        if (empty($config['log_file'])) {
            $log_file = Carbon\Carbon::now()->format($config['format'] ?? 'Ymd').'.log';
        } else {
            $log_file = $config['log_file'];
        }
        $path = storage_path('logs'.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$log_file);

        if (! is_string($data)) {
            $data = (string) json_encode(to_transform_array($data), JSON_UNESCAPED_UNICODE);
        }

        Log::build(array_merge(['driver' => 'single', 'path' => $path], $config))->info($data, $context);
    }
}

if (! function_exists('to_transform_array')) {
    function to_transform_array($value, bool $filter = true): array
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }

        if ($value instanceof \Closure) {
            $value = $value();
        }

        if (is_array($value)) {
        } elseif ($value instanceof Jsonable) {
            $value = json_decode($value->toJson(), true);
        } elseif ($value instanceof Arrayable) {
            $value = $value->toArray();
        } elseif (is_string($value)) {
            $array = null;

            try {
                $array = json_decode($value, true);
            } catch (\Throwable $e) {
            }

            $value = is_array($array) ? $array : explode(',', $value);
        } else {
            $value = (array) $value;
        }

        return $filter ? array_filter($value, function ($v) {
            return $v !== '' && $v !== null;
        }) : $value;
    }
}

if (! function_exists('catch_query_log')) {
    function catch_query_log($callback, ...$parameter): array
    {
        return (new Pipeline(app()))
            ->send($callback)
            ->through(function ($callback, Closure $next) {
                DB::enableQueryLog();
                DB::flushQueryLog();

                $queryLog = $next($callback);

                DB::disableQueryLog();

                return $queryLog;
            })
            ->then(function ($callback) use ($parameter) {
                app()->call($callback, $parameter);

                return DB::getQueryLog();
            });
    }
}

if (! function_exists('dump_to_array')) {
    function dump_to_array(...$vars): void
    {
        foreach ($vars as $var) {
            ($var instanceof Arrayable or method_exists($var, 'toArray')) ? dump($var->toArray()) : dump($var);
        }
    }
}

if (! function_exists('generate_random')) {
    function generate_random($type = 'alnum', $len = 8)
    {
        switch ($type) {
            case 'alpha':
            case 'alnum':
            case 'numeric':
            case 'nozero':
                $pool = '';

                switch ($type) {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

                        break;

                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

                        break;

                    case 'numeric':
                        $pool = '0123456789';

                        break;

                    case 'nozero':
                        $pool = '123456789';

                        break;
                }

                return substr(str_shuffle(str_repeat($pool, (int) ceil($len / strlen($pool)))), 0, $len);

            case 'unique':
            case 'md5':
                return md5(uniqid(mt_rand(), true));

            case 'encrypt':
            case 'sha1':
                return sha1(uniqid(mt_rand(), true));
        }

        return '';
    }
}

if (! function_exists('dd_to_array')) {
    function dd_to_array(...$vars): void
    {
        dump_to_array(...$vars);

        exit(1);
    }
}

if (! function_exists('array_reduce_with_keys')) {
    function array_reduce_with_keys(array $array, callable $callback, $carry = null): mixed
    {
        foreach ($array as $key => $value) {
            $carry = $callback($carry, $value, $key);
        }

        return $carry;
    }
}

if (! function_exists('array_map_with_keys')) {
    function array_map_with_keys(callable $callback, array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return $result;
    }
}

if (! function_exists('array2tree')) {
    function array2tree(array $list, int $parentId = 0): array
    {
        $data = [];
        foreach ($list as $key => $item) {
            if ($item['parent_id'] == $parentId) {
                $children = array2tree($list, (int) $item['id']);
                ! empty($children) && $item['children'] = $children;
                $data[] = $item;
                unset($list[$key]);
            }
        }

        return $data;
    }
}

if (! function_exists('amis')) {
    function amis($type = null)
    {
        if (filled($type)) {
            return \Modules\Common\Renderers\Component::make()->setType($type);
        }

        return \Modules\Common\Renderers\Amis::make();
    }
}

if (! function_exists('amisMake')) {
    function amisMake()
    {
        return \Modules\Common\Renderers\Amis::make();
    }
}

if (! function_exists('pd')) {
    function pd(...$vars): void
    {
        pp(...$vars);

        exit(1);
    }
}

if (! function_exists('pp')) {
    function pp(...$vars): void
    {
        foreach ($vars as $var) {
            /** @noinspection DebugFunctionUsageInspection */
            highlight_string(sprintf("\n<?php\n\$var = %s;\n?>\n", var_export($var, true)));
        }
    }
}
