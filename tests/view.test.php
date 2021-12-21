<?php

class TView {
	static $num = 1;

	static function test()
	{
		return static::$num;
	}
};

test('view attach', function () {
	Leaf\View::attach(TView::class);

	expect(Leaf\View::tview())->toBeInstanceOf(TView::class);
});

test('view attach with name', function () {
	Leaf\View::attach(TView::class, 'named');

	expect(Leaf\View::named())->toBeInstanceOf(TView::class);
});

test('access attached view props', function () {
	Leaf\View::attach(TView::class, 'named');

	expect(Leaf\View::named()::$num)->toBe(TView::$num);
});

test('access attached view methods', function () {
	Leaf\View::attach(TView::class, 'named');

	expect(Leaf\View::named()->test())->toBe(TView::test());
});
