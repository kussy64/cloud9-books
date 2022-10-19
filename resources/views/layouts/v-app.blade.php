<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
>    
    <style>
.container--fluid {
    max-width: 90% !important;
}
.col {
    max-width: 80% !important;
}
.v-application--wrap{
  min-height: 50px !important;
}
.v-card__subtitle, {
    padding: 60px !important;
}
</style>
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<v-app>
<div id="app">
    <div class="col-xs-md-lg-12">
        
        <v-col
        cols="12"
        xs="6"
        sm="8"
        md="12">
    <v-app style="background-color: #F1F6FB;"
    >
        <v-app-bar color="whi" height="80" app clipped-left>
            <v-toolbar-title>
                書籍管理アプリ
            </v-toolbar-title>
            <v-spacer></v-spacer>
                
                @if (Route::has('register'))
                    <v-btn text href="{{ route('register') }}">{{ __('登録') }}</v-btn>
                @endif

                    <v-list>
                        <v-list-item href="{{ route('logout') }}"
                                     onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                            <v-list-item-title>
                                    {{ __('ログアウト') }}
                            </v-list-item-title>
                        </v-list-item>
                    </v-list>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </v-menu>
           
        </v-app-bar>
        <v-main>
            @yield('content')
        </v-main>
    </v-app>
    </v-col>
</div>
</div>
</v-app>
</body>
</html>