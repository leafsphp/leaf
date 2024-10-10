<?php

class TView
{
    public static $num = 1;

    public static function test()
    {
        return static::$num;
    }
};

beforeEach(function () {
    Leaf\Config::clear();
});

test('view attach', function () {
    Leaf\Config::attachView(TView::class);

    $view = app()->tview();

    expect($view)->toBeInstanceOf(TView::class);
});

test('view attach with name', function () {
    Leaf\Config::attachView(TView::class, 'named');

    $view = Leaf\Config::get('views.named');

    expect($view)->toBeInstanceOf(TView::class);
});

test('access attached view props', function () {
    Leaf\Config::attachView(TView::class, 'named');

    $view = Leaf\Config::get('views.named');

    expect($view::$num)->toBe(TView::$num);
});

test('access attached view methods', function () {
    Leaf\Config::attachView(TView::class, 'named');

    $view = Leaf\Config::get('views.named');

    expect($view->test())->toBe(TView::test());
});

test('access attached view using the view command', function () {
    Leaf\Config::attachView(TView::class, 'named2');

    $view = Leaf\Config::view('named2');

    expect($view->test())->toBe(TView::test());
});
