<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"></script>
    <script src="{{ asset('js/app.js') }}" defer>

        </script>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    
    <!-- Styles -->
    <style>
.container--fluid {
    max-width: 90% !important;
}
.col {
    max-width: 85% !important;
}
.v-application--wrap{
  min-height: 50px !important;
}
.v-card__subtitle, {
    padding: 60px !important;
}
</style>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div id="app">
<v-app>
          <header-component></header-component>
          
          @yield('content')
          
</v-app>
</div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"></script>
    <script>

        new Vue({
            el: '#app',
            data: {
                csvFile: null,
                csvErrors: []
            },
            methods: {
                onFileChange(e) {

                    this.csvFile = e.target.files[0];

                },
                onSubmit() {

                    this.csvErrors = [];

                    const url = '/ajax/csv_import';
                    let formData = new FormData();
                    formData.append('csv_file', this.csvFile);
                    axios.post(url, formData)
                        .then(response => {

                            if(response.data.result) {

                                document.getElementById('file').value = '';
                                alert('インポートが完了しました。');

                            }

                        })
                        .catch(error => {

                            this.csvErrors = error.response.data.errors.csv_file;

                        });

                }
            }
        });

    </script>
</body>
</html>
