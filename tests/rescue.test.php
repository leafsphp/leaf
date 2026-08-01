<?php

test('rescue returns the callback result when nothing throws', function () {
    expect(rescue(fn () => 'value'))->toBe('value');
});

test('rescue returns the default when the callback throws', function () {
    expect(rescue(function () {
        throw new RuntimeException('boom');
    }, 'default'))->toBe('default');
});

test('rescue passes the exception to a closure default', function () {
    $result = rescue(function () {
        throw new LogicException('nope');
    }, fn ($e) => 'caught: ' . $e->getMessage());

    expect($result)->toBe('caught: nope');
});

test('rescue reports handled exceptions to crash', function () {
    $before = count(crash()->breadcrumbs()->trail());

    rescue(function () {
        throw new RuntimeException('reported');
    });

    $trail = crash()->breadcrumbs()->trail();

    expect(count($trail))->toBe($before + 1);
    expect(end($trail)['message'])->toContain('rescued RuntimeException: reported');
});

test('rescue can skip reporting', function () {
    $before = count(crash()->breadcrumbs()->trail());

    rescue(function () {
        throw new RuntimeException('silent');
    }, null, false);

    expect(count(crash()->breadcrumbs()->trail()))->toBe($before);
});
