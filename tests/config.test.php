<?php

test('centralized config before init', function () {
    $testMode = 'down';

    \Leaf\Config::set('mode', $testMode);

    $appMode = app()->config('mode');

    expect($appMode)->toBe($testMode);
});

test('centralized config after init', function () {
    $testMode = 'down';

    app()->config('mode', $testMode);

    $appMode = \Leaf\Config::get('mode');

    expect($appMode)->toBe($testMode);
});

test('Env is successfully retrieved', function () {
    expect(_env('USER', false))->toBe(getenv('USER'));
});
