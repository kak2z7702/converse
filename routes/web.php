<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index')->name('index');
Route::get('/consent', 'HomeController@consent')->name('consent');

Route::get('/welcome', 'InstallController@welcome')->name('welcome');
Route::match(['get', 'post'], '/install', 'InstallController@install')->name('install');
Route::match(['get', 'post'], '/options', 'InstallController@options')->middleware('auth', 'admin')->name('options');

Route::prefix('user')->group(function () {
    Route::name('user.')->group(function () {
        // laravel auth routes
        Auth::routes();

        // custom auth routes
        Route::get('result', 'UserController@result')->name('result');
        Route::get('{user}/subscriptions', 'UserController@subscriptions')->middleware('auth')->where(['user' => '[0-9]+'])->name('subscriptions');
        Route::get('{user}/favorites', 'UserController@favorites')->middleware('auth')->where(['user' => '[0-9]+'])->name('favorites');
        Route::match(['get', 'post'], '{user}/profile', 'UserController@profile')->middleware('auth')->where(['user' => '[0-9]+'])->name('profile');
        Route::get('/{user}', 'UserController@show')->where(['user' => '[0-9]+'])->name('show');

        Route::middleware('auth', 'admin')->group(function () {
            Route::match(['get', 'post'], 'create', 'UserController@create')->name('create');
            Route::match(['get', 'post'], '{user}/update', 'UserController@update')->where(['user' => '[0-9]+'])->name('update');
            Route::match(['get', 'post'], '{user?}/delete', 'UserController@delete')->where(['user' => '[0-9]+'])->name('delete');
            Route::get('{user}/ban', 'UserController@ban')->where(['user' => '[0-9]+'])->name('ban');
            Route::get('{user}/unban', 'UserController@unban')->where(['user' => '[0-9]+'])->name('unban');
            Route::get('/', 'UserController@index')->name('index');
        });
    });
});

Route::prefix('role')->group(function () {
    Route::name('role.')->group(function () {
        Route::middleware('auth', 'admin')->group(function () {
            Route::match(['get', 'post'], 'create', 'RoleController@create')->name('create');
            Route::match(['get', 'post'], '{role}/update', 'RoleController@update')->where(['role' => '[0-9]+'])->name('update');
            Route::match(['get', 'post'], '{role?}/delete', 'RoleController@delete')->where(['role' => '[0-9]+'])->name('delete');
            Route::get('/', 'RoleController@index')->name('index');
        });
    });
});

Route::prefix('menu')->group(function () {
    Route::name('menu.')->group(function () {
        Route::middleware('auth', 'admin')->group(function () {
            Route::match(['get', 'post'], 'create', 'MenuController@create')->name('create');
            Route::match(['get', 'post'], '{menu}/update', 'MenuController@update')->where(['menu' => '[0-9]+'])->name('update');
            Route::match(['get', 'post'], '{menu?}/delete', 'MenuController@delete')->where(['menu' => '[0-9]+'])->name('delete');
            Route::get('{menu}/move/{dir}', 'MenuController@move')->where(['menu' => '[0-9]+', 'dir' => 'up|down'])->name('move');
            Route::get('/', 'MenuController@index')->name('index');
        });
    });
});

Route::prefix('page')->group(function () {
    Route::name('page.')->group(function () {
        Route::middleware('auth', 'admin')->group(function () {
            Route::match(['get', 'post'], 'create', 'PageController@create')->name('create');
            Route::match(['get', 'post'], '{page}/update', 'PageController@update')->where(['page' => '[0-9]+'])->name('update');
            Route::match(['get', 'post'], '{page?}/delete', 'PageController@delete')->where(['page' => '[0-9]+'])->name('delete');
            Route::get('/', 'PageController@index')->name('index');
        });
    });
});

Route::prefix('message')->group(function () {
    Route::name('message.')->group(function () {
        Route::middleware('auth')->group(function () {
            Route::match(['get', 'post'], 'create', 'MessageController@create')->name('create');
            Route::match(['get', 'post'], '{message}/update', 'MessageController@update')->where(['message' => '[0-9]+'])->name('update');
            Route::match(['get', 'post'], '{message?}/delete', 'MessageController@delete')->where(['message' => '[0-9]+'])->name('delete');
            Route::get('/{message}', 'MessageController@show')->where(['message' => '[0-9]+'])->name('show');
            Route::get('/', 'MessageController@index')->name('index');
        });
    });
});

Route::prefix('category')->group(function () {
    Route::name('category.')->group(function () {
        Route::middleware('auth')->group(function () {
            Route::match(['get', 'post'], 'create', 'CategoryController@create')->name('create');
            Route::match(['get', 'post'], '{category}/update', 'CategoryController@update')->where(['category' => '[0-9]+'])->name('update');
            Route::get('{category}/delete', 'CategoryController@delete')->where(['category' => '[0-9]+'])->name('delete');
            Route::get('{category}/move/{dir}', 'CategoryController@move')->where(['category' => '[0-9]+', 'dir' => 'up|down'])->name('move');
        });
    });
});

Route::prefix('topic')->group(function () {
    Route::name('topic.')->group(function () {
        Route::middleware('auth')->group(function () {
            Route::match(['get', 'post'], 'create', 'TopicController@create')->name('create');
            Route::match(['get', 'post'], '{topic}/update', 'TopicController@update')->where(['topic' => '[0-9]+'])->name('update');
            Route::get('{topic}/delete', 'TopicController@delete')->where(['topic' => '[0-9]+'])->name('delete');
            Route::get('{topic}/move/{dir}', 'TopicController@move')->where(['topic' => '[0-9]+', 'dir' => 'up|down'])->name('move');
        });
    });
});

Route::prefix('thread')->group(function () {
    Route::name('thread.')->group(function () {
        Route::middleware('auth')->group(function () {
            Route::match(['get', 'post'], 'create', 'ThreadController@create')->where(['topic' => '[0-9]+'])->name('create');
            Route::match(['get', 'post'], '{thread}/update', 'ThreadController@update')->where(['thread' => '[0-9]+'])->name('update');
            Route::match(['get', 'post'], '{thread?}/delete', 'ThreadController@delete')->where(['thread' => '[0-9]+'])->name('delete');
            Route::get('{thread}/open', 'ThreadController@open')->where(['thread' => '[0-9]+'])->name('open');
            Route::get('{thread}/close', 'ThreadController@close')->where(['thread' => '[0-9]+'])->name('close');
            Route::get('{thread}/pin', 'ThreadController@pin')->where(['thread' => '[0-9]+'])->name('pin');
            Route::get('{thread}/unpin', 'ThreadController@unpin')->where(['thread' => '[0-9]+'])->name('unpin');
            Route::get('{thread}/subscribe', 'ThreadController@subscribe')->where(['thread' => '[0-9]+'])->name('subscribe');
            Route::match(['get', 'post'], '{thread?}/unsubscribe', 'ThreadController@unsubscribe')->where(['thread' => '[0-9]+'])->name('unsubscribe');
            Route::get('{thread}/favorite', 'ThreadController@favorite')->where(['thread' => '[0-9]+'])->name('favorite');
            Route::match(['get', 'post'], '{thread?}/unfavorite', 'ThreadController@unfavorite')->where(['thread' => '[0-9]+'])->name('unfavorite');
        });
    });
});

Route::prefix('comment')->group(function () {
    Route::name('comment.')->group(function () {
        Route::middleware('auth')->group(function () {
            Route::post('create', 'CommentController@create')->name('create');
            Route::match(['get', 'post'], '{comment}/update', 'CommentController@update')->where(['comment' => '[0-9]+'])->name('update');
            Route::get('{comment}/delete', 'CommentController@delete')->where(['comment' => '[0-9]+'])->name('delete');
        });
    });
});

Route::get('discussion/{category_slug}/topic/{topic_slug}/{thread_slug}', 'ThreadController@show')->where(
    ['category_slug' => '[A-Za-z0-9_\-]+'],
    ['topic_slug' => '[A-Za-z0-9_\-]+'],
    ['thread_slug' => '[A-Za-z0-9_\-]+'],
)->name('thread.show');

Route::get('discussion/{category_slug}/topic/{topic_slug}', 'TopicController@show')->where(
    ['category_slug' => '[A-Za-z0-9_\-]+'],
    ['topic_slug' => '[A-Za-z0-9_\-]+'],
)->name('topic.show');

Route::get('discussion/{category_slug}', 'CategoryController@show')->where(
    ['category_slug' => '[A-Za-z0-9_\-]+']
)->name('category.show');

Route::get('/{slug}', 'PageController@show')->where(['slug' => '.*'])->name('page.show');