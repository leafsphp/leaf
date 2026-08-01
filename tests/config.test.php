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

test('_env does not see runtime environment changes', function () {
    putenv('LEAF_ENV_CACHE_PROBE=cached-miss');

    expect(_env('LEAF_ENV_CACHE_PROBE', 'fallback'))->toBe('fallback');
});

test('_envUncached reads the environment live with _env casting', function () {
    putenv('LEAF_ENV_LIVE_PROBE=true');

    expect(_envUncached('LEAF_ENV_LIVE_PROBE'))->toBe(true);

    putenv('LEAF_ENV_LIVE_PROBE=hello');

    expect(_envUncached('LEAF_ENV_LIVE_PROBE'))->toBe('hello');
    expect(_envUncached('LEAF_ENV_MISSING_PROBE', 'fallback'))->toBe('fallback');
});
