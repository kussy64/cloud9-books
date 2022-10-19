@extends('layouts.v-app')
@section('content')


<v-app>
    <div class="card-body">
        <v-card-title class="font-weight-bold">
            ～書籍新規登録画面～
            </v-card-title>
    <v-row
  class="lighten-4" style="height: 700px;"
  justify="center" align-content="center"
>
    
<div class="row container">
    <div class="col-md-12">
    @include('common.errors')
 <form enctype="multipart/form-data" action="{{ url('books/store') }}"method="POST" class="form-horizontal">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="book" class="col-sm-3 control-label">書籍</label>
                    <input type="text" name="item_name" class="form-control @error('item_name') is-invalid @enderror" value="{{ old('item_name') }}">
                    @error('item_name')
  　　　　　　　　　　　　　　<div class="text-danger">{{ $message }}</div>
　　　　　　　　　　@enderror
                </div>

                <div class="form-group col-md-6">
                    <label for="amount" class="col-sm-3 control-label">金額</label>
                    <input type="text" name="item_amount" class="form-control @error('item_amount') is-invalid @enderror" value="{{ old('item_amount') }}">
                    @error('item_amount')
  　　　　　　　　　　　　　　<div class="text-danger">{{ $message }}</div>
　　　　　　　　　　@enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="number" class="col-sm-3 control-label">数</label>
                    <input type="text" name="item_number" class="form-control @error('item_number') is-invalid @enderror" value="{{ old('item_number') }}">
                    @error('item_number')
  　　　　　　　　　　　　　　<div class="text-danger">{{ $message }}</div>
　　　　　　　　　　@enderror
                </div>

                  <div class="form-group col-md-12">
                    <label for="published" class="col-sm-3 control-label">公開日</label>
                    <input type="date" name="published" class="form-control @error('published') is-invalid @enderror" value="{{ old('published') }}">
                    @error('published')
  　　　　　　　　　　　　　　<div class="text-danger">{{ $message }}</div>
　　　　　　　　　　@enderror
                </div>
	<div class="form-group col-md-12">
		<label class="control-label" for="text">コメント入力欄</label>
		<input type="text" name="item_text" class="form-control input-lg" value="{{ old('item_text') }}" placeholder="コメント">
	</div>
            </div>
            <!-- file追加 -->
            <div class="col-sm-6">
              <label>画像</label>
              <input type="file" name="item_img">
            </div>
            
            <!-- 本 登録ボタン -->
            <div class="form-row">
                <div class="col-sm-offset-3 col-sm-6">
                    <v-btn color="primary" type="submit" class="btn btn-primary">
                    保存
                    </v-btn>
                    <a class="btn btn-link pull-right" href="{{ url('/') }}">
                戻る
            </a>
                </div>
            </div>
            
         <!--/ id値を送信 -->
         
         <!-- CSRF -->
         @csrf
         <!--/ CSRF -->
         
         
         
        </form>
    </div>

    </div>


</v-row>
</div>
</v-app>
@endsection