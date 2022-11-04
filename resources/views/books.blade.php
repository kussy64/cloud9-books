<!-- resources/views/books.blade.php -->



@extends('layouts.app')
@section('content')


<div id="app">
    <v-app>
    <v-container fluid fill-height>
    <!-- Bootstrapの定形コード… -->
<div class="col-xs-sm-md-lg-12">
    <v-row justify="center">
    <v-col>
    <div class="card-body">
        
                        <v-card-title class="font-weight-bold">
            CSVインポート機能：
<v-form action="{{ url('books/upload')}}" method="POST" enctype="multipart/form-data">
     @csrf
     <input type="file" name="csvdata" />
               <v-btn color="primary" type="submit" class="btn btn-primary">
    CSVアップロード
    </v-btn> 
               </v-form>

        </v-card-title>
        
                <v-card-title class="font-weight-bold">
            CSV出力機能：
<v-form action="{{ route('books.postCsv')}}" method="GET">
     @csrf
               <v-btn color="primary" type="submit" class="btn btn-primary">
    CSVダウンロード
    </v-btn> 
               </v-form>

        </v-card-title>
        
        <v-card-title class="font-weight-bold">
            ～登録した書籍一覧～　：
                    <form action="{{ url('/booksregister') }}" method="POST">
           @csrf
                <v-btn color="primary" type="submit" class="btn btn-primary">
                    新規登録
                </v-btn>
        </form>

        </v-card-title>

        <!-- バリデーションエラーの表示に使用-->
        @include('common.errors')
        <!-- バリデーションエラーの表示に使用-->

    

       
　　@if (session('message'))
      <div class="alert alert-success">
          {{ session('message') }}
      </div>
  @endif
  

      <v-row justify="center">
           
        
    
        <div class="card-body">

               

 <div class="form-inline mx-12 my-lg-12">

        
<template>       
  <v-form action="{{route('books.index')}}" method="GET">
      @csrf
    <example-component></example-component>
      
      <v-btn color="primary" type="submit" class="btn btn-primary mb-2">
    検索
    </v-btn>
    
  

      
        

                      
      </v-col>

        
    

    
    
    
    
  </v-form>
                
</template>
 </div>
 </div>
 </div>
    <!-- Book: 既に登録されてる本のリスト -->
     <!-- 現在の本 -->
     <!-- <thead>
                        <h3>本一覧</h3>
                        </thead> -->
        <div class="card-body">
            
                
                    <!-- テーブルヘッダ -->
                    

                    
                    <!-- テーブル本体 -->
        

<div class="card-deck">
    <div class="row row-cols md-3">
        <v-row dense>
                        @foreach ($books as $book)
                          
                        <template>
                            <v-app>
                            <v-row
  class="lighten-4" style="height: 700px;"
  justify="center" align-content="center"
>
                            
                                <v-col class="px-2 ma-2">
 <v-card class="ma-2 mx-5" outlined>
      
    
<div> <img src="upload/{{$book->item_img}}" width="234" height="180"></div>
    <v-card-title><div class="h3">{{ $book->item_name }}</div></v-card-title>
    <v-card-text class="text--primary">
    　
    　
      <div class="display-5">公開日：{{ $book->published }}</div>

      <div class="display-5">詳細更新日：{{ $book->updated_at }}</div>
    </v-card-text>

    <v-card-actions>
      <v-form action="{{ url('booksedit/'.$book->id) }}" method="POST">
                                        @csrf
                                        <v-row
                                            align="center"
                                            justify="space-around"
                                        >
                                        <v-btn color="primary" type="submit" class="btn btn-primary mx-3 mb-3">
                                            更新
                                        </v-btn>
           <v-form action="{{ url('book/'.$book->id) }}" method="POST">
           @csrf
           @method('DELETE')
                <v-btn
        text
        class="btn btn-danger mx-8 mb-3" type="button" data-category="{{ $book->id }}" data-toggle="modal" data-target="#testModal"
      >
        削除
      </v-btn>
      </v-row>
      </v-form>
    </v-card-actions>
  </v-card>
        </v-col>
        </v-row>
       
        </v-app>
</template>
                
                                    
                            <div class="modal fade" id="testModal" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                  <div class="modal-content">
                                    <div class="modal-header">
                                      <h4><div class="modal-title" id="myModalLabel">削除確認画面</div></h4>
                                    </div>
                                            <div class="modal-body">
                                                <label>データを削除しますか？</label>
                                            </div>
                                    <div class="modal-footer">
                                        
                                        <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                
                                        
                                        
                                <form action="{{ url('book/'.$book->id) }}" method="POST">
                                   @csrf
                                   @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                        削除
                                        </button>
                                        </form>
                                    </div>
                                </div>
                                </div>
                            </div>
                                

                                <!-- 本: 削除ボタン -->

                        @endforeach
                        </v-row>
                    </div>
                
            </div>

        </div>

       

</v-row>
</div>
        
                <div class="row align-items-center">
            <div class="col-md-4 offset-md-4">
                {{ $books->links()}}
            </div>
       </div>
</v-col>
</v-row>
</div>
</v-container>
</v-app>
</div>

@endsection
