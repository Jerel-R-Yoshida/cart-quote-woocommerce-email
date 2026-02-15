<?php
/**
 * Query Logger - Monitors database query performance
 *
 * @package CartQuoteWooCommerce\Core
 * @author Jerel Yoshida
 * @since 1.0.9
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Core;

class Query_Logger
{
    private static $instance = null;
    private static $enabled = false;
    private static $queries = [];
    private static $start_time = 0;
    private static $slow_query_threshold = 100;

    private function __construct()
    {
    }

    public static function get_instance(): Query_Logger
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init(): void
    {
        $save_queries = defined('SAVEQUERIES') ? constant('SAVEQUERIES') : false;
        if (self::$enabled && $save_queries) {
            $hook = 'shutdown';
            $callback = [self::class, 'log_slow_queries'];
            $priority = 999;
            if (function_exists('\\add_action')) {
                \add_action($hook, $callback, $priority);
            }
        }
    }

    public static function enable(): void
    {
        self::$enabled = true;
        self::$queries = [];
        self::$start_time = microtime(true);
    }

    public static function disable(): void
    {
        self::$enabled = false;
    }

    public static function is_enabled(): bool
    {
        return self::$enabled;
    }

    public static function log_query(\wpdb $wpdb, string $query, float $time): void
    {
        if (!self::$enabled) {
            return;
        }

        $is_slow = $time * 1000 >= self::$slow_query_threshold;

        self::$queries[] = [
            'sql' => $query,
            'time' => $time,
            'time_ms' => $time * 1000,
            'is_slow' => $is_slow,
            'rows_affected' => property_exists($wpdb, 'rows_affected') ? $wpdb->rows_affected : 0,
            'last_error' => property_exists($wpdb, 'last_error') ? $wpdb->last_error : '',
        ];

        if ($is_slow) {
            self::log_to_file($wpdb, $query, $time);
        }
    }

    public static function is_slow_query(float $time_ms): bool
    {
        return $time_ms >= self::$slow_query_threshold;
    }

    public static function get_statistics(): array
    {
        if (empty(self::$queries)) {
            return [
                'total' => 0,
                'total_time' => 0,
                'avg_time' => 0,
                'slow_queries' => 0,
                'slowest_query' => null,
            ];
        }

        $total_queries = count(self::$queries);
        $total_time = array_sum(array_column(self::$queries, 'time'));
        $slow_queries = array_filter(self::$queries, function($q) {
            return $q['is_slow'];
        });

        usort($slow_queries, function($a, $b) {
            return $b['time_ms'] <=> $a['time_ms'];
        });

        return [
            'total' => $total_queries,
            'total_time' => $total_time,
            'total_time_ms' => $total_time * 1000,
            'avg_time' => $total_time / $total_queries,
            'avg_time_ms' => ($total_time / $total_queries) * 1000,
            'slow_queries' => count($slow_queries),
            'slowest_query' => !empty($slow_queries) ? $slow_queries[0] : null,
            'slow_queries_list' => array_slice($slow_queries, 0, 10),
        ];
    }

    public static function identify_n_plus_one(): array
    {
        $query_patterns = [];
        $n_plus_one_candidates = [];

        foreach (self::$queries as $i => $query) {
            $sql = $query['sql'];
            $pattern = self::extract_query_pattern($sql);

            if (!isset($query_patterns[$pattern])) {
                $query_patterns[$pattern] = [
                    'pattern' => $pattern,
                    'count' => 0,
                    'total_time' => 0,
                    'indices' => [],
                ];
            }

            $query_patterns[$pattern]['count']++;
            $query_patterns[$pattern]['total_time'] += $query['time'];
            $query_patterns[$pattern]['indices'][] = $i;
        }

        foreach ($query_patterns as $pattern_data) {
            if ($pattern_data['count'] >= 3) {
                $avg_time = $pattern_data['total_time'] / $pattern_data['count'];

                $n_plus_one_candidates[] = [
                    'pattern' => $pattern_data['pattern'],
                    'count' => $pattern_data['count'],
                    'avg_time_ms' => $avg_time * 1000,
                    'total_time_ms' => $pattern_data['total_time'] * 1000,
                    'indices' => $pattern_data['indices'],
                    'likelihood' => self::calculate_n_plus_one_likelihood($pattern_data['count']),
                ];
            }
        }

        usort($n_plus_one_candidates, function($a, $b) {
            return $b['likelihood'] <=> $a['likelihood'];
        });

        return array_slice($n_plus_one_candidates, 0, 10);
    }

    private static function extract_query_pattern(string $sql): string
    {
        $pattern = preg_replace('/\b\d+\b/', '?', $sql);
        $pattern = preg_replace('/\'[^\']*\'/', '?', $pattern);
        $pattern = preg_replace('/"[^"]*"/', '?', $pattern);
        $pattern = preg_replace('/\s+/', ' ', $pattern);
        $pattern = trim($pattern);

        return $pattern;
    }

    private function calculate_n_plus_one_likelihood(int $count): float
    {
        if ($count < 3) {
            return 0.0;
        }

        $base_score = min($count / 10, 1.0);

        return $base_score;
    }

    public static function generate_report(): array
    {
        $stats = self::get_statistics();
        $n_plus_one = self::identify_n_plus_one();

        return [
            'enabled' => self::$enabled,
            'duration' => microtime(true) - self::$start_time,
            'statistics' => $stats,
            'n_plus_one_candidates' => $n_plus_one,
            'queries' => self::$queries,
            'slow_query_threshold' => self::$slow_query_threshold,
        ];
    }

    public static function get_slow_queries(): array
    {
        return array_filter(self::$queries, function($q) {
            return $q['is_slow'];
        });
    }

    public static function clear_logs(): void
    {
        self::$queries = [];
        self::$start_time = microtime(true);
    }

    public static function set_slow_query_threshold(int $milliseconds): void
    {
        self::$slow_query_threshold = max(1, $milliseconds);
    }

    public static function get_slow_query_threshold(): int
    {
        return self::$slow_query_threshold;
    }

    private static function log_to_file(\wpdb $wpdb, string $query, float $time): void
    {
        $log_dir = WP_CONTENT_DIR . '/uploads/cart-quote-debug';
        $log_file = $log_dir . '/slow-queries.log';

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        $entry = sprintf(
            "[%s] Query took %.2fms: %s\n",
            date('Y-m-d H:i:s'),
            $time * 1000,
            $query
        );

        file_put_contents($log_file, $entry, FILE_APPEND);
    }

    public function log_slow_queries(): void
    {
        if (!self::$enabled || empty(self::$queries)) {
            return;
        }

        $slow_queries = self::get_slow_queries();

        if (empty($slow_queries)) {
            return;
        }

        $logger = Debug_Logger::get_instance();

        $logger->warning('Slow queries detected', [
            'count' => count($slow_queries),
            'threshold_ms' => self::$slow_query_threshold,
            'slowest_query_time_ms' => $slow_queries[0]['time_ms'],
        ]);

        foreach ($slow_queries as $slow_query) {
            $logger->warning('Slow query executed', [
                'sql' => $slow_query['sql'],
                'time_ms' => $slow_query['time_ms'],
                'rows_affected' => $slow_query['rows_affected'],
                'last_error' => $slow_query['last_error'],
            ]);
        }
    }

    public static function export_to_csv(): string
    {
        $stats = self::get_statistics();
        $n_plus_one = self::identify_n_plus_one();

        $csv = [];
        $csv[] = [
            'Query Index',
            'SQL Query',
            'Time (ms)',
            'Is Slow',
            'Rows Affected',
            'Last Error',
        ];

        foreach (self::$queries as $i => $query) {
            $csv[] = [
                $i,
                $query['sql'],
                number_format($query['time_ms'], 2),
                $query['is_slow'] ? 'Yes' : 'No',
                $query['rows_affected'],
                $query['last_error'],
            ];
        }

        $csv_data = '';
        foreach ($csv as $row) {
            $csv_data .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }

        return $csv_data;
    }
}
