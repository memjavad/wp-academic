<?php

define('ABSPATH', __DIR__ . '/../');
require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/field-news/repo-admin.php';

function test_parse_ris_entry_basic() {
    $repo = new WPA_Study_Repo_Page();
    $reflection = new ReflectionClass($repo);
    $method = $reflection->getMethod('parse_ris_entry');
    $method->setAccessible(true);

    $ris_data = "TY  - JOUR\nTI  - The testing title\nAU  - Smith, J.\nJO  - Journal of Testing\nPY  - 2024\nDO  - 10.1234/test.123\nAB  - A fake abstract.\nER  -";

    $result = $method->invokeArgs($repo, [$ris_data]);

    $expected = [
        'id' => '',
        'title' => 'The testing title',
        'creator' => 'Smith, J.',
        'publication' => 'Journal of Testing',
        'date' => '2024',
        'doi' => '10.1234/test.123',
        'abstract' => 'A fake abstract.',
        'citations' => 0,
        'openaccess' => false,
        'type' => 'Article',
        'links' => []
    ];

    foreach ($expected as $key => $value) {
        if ($result[$key] !== $value) {
            echo "FAIL (basic): Expected $key = '$value', got '{$result[$key]}'\n";
            exit(1);
        }
    }
}

function test_parse_ris_entry_alternative_tags() {
    $repo = new WPA_Study_Repo_Page();
    $reflection = new ReflectionClass($repo);
    $method = $reflection->getMethod('parse_ris_entry');
    $method->setAccessible(true);

    // Using alternative tags T1, A1, JF, Y1, N2
    $ris_data = "TY  - JOUR\nT1  - Another title\nA1  - Doe, Jane\nJF  - Journal Full Name\nY1  - 2023\nN2  - Another abstract.\nER  -";

    $result = $method->invokeArgs($repo, [$ris_data]);

    if ($result['title'] !== 'Another title') { echo "FAIL (alt): Title mismatch\n"; exit(1); }
    if ($result['creator'] !== 'Doe, Jane') { echo "FAIL (alt): Creator mismatch\n"; exit(1); }
    if ($result['publication'] !== 'Journal Full Name') { echo "FAIL (alt): Publication mismatch\n"; exit(1); }
    if ($result['date'] !== '2023') { echo "FAIL (alt): Date mismatch\n"; exit(1); }
    if ($result['abstract'] !== 'Another abstract.') { echo "FAIL (alt): Abstract mismatch\n"; exit(1); }
}

function test_parse_ris_entry_first_author_only() {
    $repo = new WPA_Study_Repo_Page();
    $reflection = new ReflectionClass($repo);
    $method = $reflection->getMethod('parse_ris_entry');
    $method->setAccessible(true);

    // Multiple authors, it should only pick the first one since it checks empty()
    $ris_data = "TY  - JOUR\nAU  - First, A.\nAU  - Second, B.\nAU  - Third, C.\nER  -";

    $result = $method->invokeArgs($repo, [$ris_data]);

    if ($result['creator'] !== 'First, A.') {
        echo "FAIL (authors): Expected First, A. but got {$result['creator']}\n";
        exit(1);
    }
}

function test_parse_ris_entry_empty_lines() {
    $repo = new WPA_Study_Repo_Page();
    $reflection = new ReflectionClass($repo);
    $method = $reflection->getMethod('parse_ris_entry');
    $method->setAccessible(true);

    // Missing space after dash, empty lines
    $ris_data = "\n\nTY  - JOUR\n\nTI  -   Spaced Title  \n\n\nER  -";

    $result = $method->invokeArgs($repo, [$ris_data]);

    if ($result['title'] !== 'Spaced Title') {
        echo "FAIL (empty lines): Expected 'Spaced Title' but got '{$result['title']}'\n";
        exit(1);
    }
}

function test_parse_ris_entry_short_lines() {
    $repo = new WPA_Study_Repo_Page();
    $reflection = new ReflectionClass($repo);
    $method = $reflection->getMethod('parse_ris_entry');
    $method->setAccessible(true);

    // Short line < 6 chars should be skipped
    $ris_data = "TY\nTI  - Valid\nBAD\nER  -";

    $result = $method->invokeArgs($repo, [$ris_data]);

    if ($result['title'] !== 'Valid') {
        echo "FAIL (short lines): Expected 'Valid' but got '{$result['title']}'\n";
        exit(1);
    }
}

echo "Running parse_ris_entry tests...\n";
test_parse_ris_entry_basic();
test_parse_ris_entry_alternative_tags();
test_parse_ris_entry_first_author_only();
test_parse_ris_entry_empty_lines();
test_parse_ris_entry_short_lines();
echo "PASS: All tests for parse_ris_entry passed.\n";
