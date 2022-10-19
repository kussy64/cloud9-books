
@extends('layouts.v-app')
@section('content')
<v-app>
<div class="card-body">
    <v-card-title class="font-weight-bold">
            ～書籍更新画面～
            </v-card-title>
    <v-row
  class="lighten-4" style="height: 900px;"
  justify="center" align-content="center"
>
        
<div class="row container">
    <div class="col-md-12">
 <!-- バリデーションエラーの表示に使用-->
        @include('common.errors')
        <!-- バリデーションエラーの表示に使用-->

        
        
　@if (session('message'))
      <div class="alert alert-success">
          {{ session('message') }}
      </div>
  @endif
    

       
　　@if (session('message'))
      <div class="alert alert-success">
          {{ session('message') }}
      </div>
  @endif
    <form enctype="multipart/form-data" action="{{ url('books/update') }}" method="POST">
        
        <!-- item_name -->
        
        <div class="form-group">
           <label for="item_name">書籍名</label>
           <input type="text" name="item_name" class="form-control @error('item_name') is-invalid @enderror" value="{{$book->item_name}}">
           @error('item_name')
  　　　　　　　　　　　　　　<div class="text-danger">{{ $message }}</div>
　　　　　　　　　　@enderror
        </div>
                <v-combobox
          v-model="select"
          :items="items"
          label=""
          multiple
          outlined
          dense
        >
        </v-combobox>
        <!--/ item_name -->
        <div class="form-group">
           <label for="item_text">詳細情報</label>
        <input type="text" name="item_text" class="form-control" value="{{$book->item_text}}">
        </div>
        <!-- item_number -->
        <div class="form-group">
           <label for="item_number">在庫</label>
        <input type="text" name="item_number" class="form-control" value="{{$book->item_number}}">
        </div>
        <!--/ item_number -->

        <!-- item_amount -->
        <div class="form-group">
           <label for="item_amount">金額</label>
        <input type="text" name="item_amount" class="form-control" value="{{$book->item_amount}}">
        </div>
        <!--/ item_amount -->
        
        <!-- published -->
        <div class="form-group">
           <label for="published">公開日</label>
            <input type="text" name="published" class="form-control" value="{{$book->published}}"/>
        </div>
        <!--/ published -->
        
         <!-- item_img -->
         @isset ($book)
         <div><img src="{{ asset('upload/' . $book->item_img) }}"width="200"></div>
         @endisset
         <div class="form-group">
             <label for="item_img">画像</label>
         <input type="file" name="item_img" class="form-control" value="{{$book->item_img}}">
         </div>
        <!--/ item_img -->
        
        <!-- Saveボタン/Backボタン -->
        <div class="well well-sm">
            <button type="submit" class="btn btn-primary" onClick="move_check(event);return false;">保存</button>
            <a class="btn btn-link pull-right" href="{{ url('/') }}">
                戻る
            </a>
        </div>
        <!--/ Saveボタン/Backボタン -->
         
         <!-- id値を送信 -->
        

         <input type="hidden" name="id" value="{{$book->id}}">
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