<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
        <link rel="stylesheet" href="{{ elixir('css/app.css') }}" />
    </head>
    <body>
        <div class="flex-center position-ref">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>
                        <a href="{{ route('register') }}">Register</a>
                    @endauth
                </div>
            @endif

            <div id="app" class="content">
                <div class="title m-b-md">
                    Vue Grid
                </div>  
                <crypto-grid :columns="gridColumns" :hidden="hidden" :page-list="pages" :filterable="filterable" :widths="widths" :end-point="endPoint" :limit="limit">
                </crypto-grid>
            </div>
        </div>
        <script src="{{ elixir('js/app.js') }}"></script>
        <script>
            var grid = new Vue({
                el: '#app',
                data: {
                    gridColumns: ['id', 'name', 'start_date', 'end_date', 'size', 'weight'],
                    filterable: ['name', 'start_date', 'end_date', 'size', 'weight'],
                    widths: ["30%", "20%", "20%", "15%", "15%"],
                    pages: 10,
                    hidden: ['id'],
                    endPoint: "http://local.vuegrid.com:8000/",
                    limit: 10,
                }
            })
        </script>
    </body>
</html>
