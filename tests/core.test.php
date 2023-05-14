<?php

class MyScript
{
    public static $called = false;
}

test('attach a script to the leaf instance', function () {
    app()->attach(function () {
        MyScript::$called = true;
    });

    app()->run();

    expect(MyScript::$called)->toBe(true);
});

test('execute a script in a particular app environment', function () {
    MyScript::$called = false;

    app()->config('mode', 'production');

    app()->environment('development', function () {
        MyScript::$called = true;
    });

    expect(MyScript::$called)->toBe(false);
});
