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

test('nested config', function () {
    app()->config('randomKey.number', '2');

    $randomKey = app()->config('randomKey');

    expect(isset($randomKey['number']))->toBeTrue();
    expect($randomKey['number'])->toBe('2');
    expect(app()->config('randomKey.number'))->toBe('2');
});

test('nested config (array)', function () {
    app()->config(['nestedKey.number' => '2']);

    $nestedKey = app()->config('nestedKey');

    expect(isset($nestedKey['number']))->toBeTrue();
    expect($nestedKey['number'])->toBe('2');
    expect(app()->config('nestedKey.number'))->toBe('2');
});

test('nested config (custom group)', function () {
    app()->config(['home.key' => '2']);

    $homeConfig = app()->config('home');

    expect(isset($homeConfig['key']))->toBeTrue();
    expect($homeConfig['key'])->toBe('2');
    expect(app()->config('home.key'))->toBe('2');
});

test('Env is successfully loaded from .env', function () {
    \Leaf\Helpers\Env::loadEnv(__DIR__ . "/.env");
    expect(_env('MY_APPLICATION_ID'))->toBe('my_application_id');
});

test('Env is successfully retrieved', function () {
    expect(_env('USER', false))->toBe(getenv('USER'));
});
