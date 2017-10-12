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

Route::get('/', function () {
    return view('welcome');
});

Route::post('/', function(Illuminate\Http\Request $request) {
    $payload = collect();
    if(isset($request->filter))
    {
        $query = App\Content::where('name', 'LIKE', '%'.$request->filter.'%');
        $total = $query->count();
        $results = $query->orderBy($request->orderBy, 'ASC')
                         ->limit((int)$request->limit)
                         ->get();   
    }
    else
    {
        $total = App\Content::count();
        $results = App\Content::take($request->limit)->get();
    }
    $payload->put('results', $results);
    $payload->put('total', $total);
    return $payload;
});
