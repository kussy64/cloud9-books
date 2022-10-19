@extends('layouts.app')

@section('content')
<template>
<v-app>
<v-main>
    <v-container>
        <h1 class="h4 text-center pt-10 font-weight-bold">
            {{ __('ログイン') }}
        </h1>
        <v-row justify="center">
            <v-col cols="12" lg="8" md="8">
                <v-card elevation="2">
                            <v-card-title class="justify-center">
            ログイン入力画面
        </v-card-title>
                    <div class="pa-8">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('eメールアドレス') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('パスワード') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                           <v-row class="justify-center">
                        
                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <v-btn color="primary" type="submit" class="btn btn-primary">
                                    {{ __('ログイン') }}
                                </v-btn>

                            @if (Route::has('password.request'))
                                
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('パスワード再設定しますか?') }}
                                    </a>
                                
                            @endif
                            </v-row>
                        </form>
                    </div>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</v-main>
</v-app>
</template>
@endsection