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

test('attached views resolve through the app instance', function () {
    Leaf\Config::attachView(TView::class, 'named3');

    expect(app()->named3()->test())->toBe(TView::test());
});

test('undefined app methods throw instead of returning null', function () {
    expect(fn () => app()->definitelyNotAMethod())
        ->toThrow(BadMethodCallException::class);
});

class TRenderEngine
{
    public function render(string $view, array $data = [])
    {
        return 'rendered:' . $view . ':' . ($data['name'] ?? '');
    }
}

// Regression for leafsphp/leaf#331: response()->render() used to call app()->blade()
// unconditionally, which throws when Blade is not attached, so BareUI (attached as
// "template") and any custom engine could never be reached.
test('response render works with a non-blade engine attached as template', function () {
    Leaf\Config::attachView(TRenderEngine::class, 'template');

    ob_start();
    response()->render('sample/index', ['name' => 'nicolas']);
    $output = ob_get_clean();

    expect($output)->toContain('rendered:sample/index:nicolas');
});

test('response render resolves a custom engine attached under its own name', function () {
    Leaf\Config::attachView(TRenderEngine::class);

    ob_start();
    response()->render('home');
    $output = ob_get_clean();

    expect($output)->toContain('rendered:home');
});
