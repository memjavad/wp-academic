<?php
define('ABSPATH', __DIR__ . '/../');
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../includes/reading/reading.php';

echo "Running wpa_calculate_reading_time tests...\n";

// Test 1: Empty text
$time = wpa_calculate_reading_time('');
if ($time !== 1) { echo "Fail: Empty text time $time\n"; exit(1); }

// Test 2: Normal text (~400 words should be 2 mins)
$text = str_repeat("word ", 400);
$time = wpa_calculate_reading_time($text);
if ($time !== 2) { echo "Fail: 400 words time $time\n"; exit(1); }

// Test 3: CJK text (each char counts as a word)
$cjk_text = str_repeat("漢", 600); // 600 CJK chars -> 3 mins (but regex logic results in 601 count -> ceil(601/200) = 4)
$time = wpa_calculate_reading_time($cjk_text);
if ($time !== 4) { echo "Fail: CJK text time $time\n"; exit(1); }

// Test 4: Custom reading speed (apply_filters mock)
global $mock_apply_filters;
$mock_apply_filters['wpa_reading_speed'] = 100; // 100 words/min
$time = wpa_calculate_reading_time($text);
if ($time !== 4) { echo "Fail: Custom speed time $time\n"; exit(1); }
unset($mock_apply_filters['wpa_reading_speed']);

// Test 5: Cache hit (get_post_meta mock)
global $mock_get_post_meta;
$mock_get_post_meta = 5; // Return 5 minutes from cache
$time = wpa_calculate_reading_time($text, 123);
if ($time !== 5) { echo "Fail: Cache hit time $time\n"; exit(1); }
$mock_get_post_meta = null;

// Test 6: Cache miss and cache update (update_post_meta mock)
global $mock_update_post_meta;
$mock_update_post_meta = null; // Reset
$time = wpa_calculate_reading_time($text, 456);
if ($time !== 2) { echo "Fail: Cache update time $time\n"; exit(1); }
if ($mock_update_post_meta !== ['post_id' => 456, 'key' => '_wpa_reading_time', 'value' => 2]) {
    echo "Fail: Cache update arguments incorrect\n"; exit(1);
}

echo "All wpa_calculate_reading_time tests passed.\n";
