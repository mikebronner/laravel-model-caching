<?php

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;

Route::get('pagination-test', function () {
    $books = (new Book)
        ->paginate(10);

    return view('model-caching-tests.pagination')
        ->with(compact(
            'books'
        ));
});

Route::get('pagination-test2', function () {
    $books = (new Book)
        ->paginate(10, ['*'], 'custom-page');

    return view('model-caching-tests.pagination')
        ->with(compact(
            'books'
        ));
});
