<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;


//使うClassを宣言:自分で追加
use App\Book;   //Bookモデルを使えるようにする
use Validator;  //バリデーションを使えるようにする
use Auth;       //認証モデルを使用する

class BooksController extends Controller
{
    
    //コンストラクタ （このクラスが呼ばれたら最初に処理をする）
    public function __construct()
    {
        $this->middleware('auth');
    }
    


        
    //本ダッシュボード表示
public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        if (!empty($keyword)) {
            $books = Book::where('item_name', 'LIKE', "%{$keyword}%")->orwhere('item_text', 'LIKE', "%{$keyword}%")->where('user_id',Auth::user()->id)->orderBy('created_at', 'asc')->paginate(4);
        }
        else {
            $books = Book::where('user_id',Auth::user()->id)->orderBy('created_at', 'asc')->paginate(4);
        }
        return view('books', compact('books','keyword'));
    }
    
    //更新画面
    public function edit($book_id) {
        $books = Book::where('user_id',Auth::user()->id)->find($book_id);
        return view('booksedit', [
            'book' => $books
        ]);
    }
    
    //更新
    public function update(Request $request) {
        //バリデーション
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'item_name' => 'required|min:3|max:255',
            'item_number' => 'required|regex:/\A([0-9]{1,})+\z/u|max:8',
            'item_amount' => 'required|regex:/\A([0-9]{1,})+\z/u',
            'item_text' => 'required|min:10|max:255',
            //'item_img' => 'required',
            'published' => 'required',
        ]); 
        //バリデーション:エラー 
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($validator);
        }
        $file = $request->file('item_img'); //file取得
        if(!empty($file)){                //fileが空かチェック
            $filename = $file->getClientOriginalName();   //ファイル名を取得
            $move = $file->move('./upload/',$filename);  //ファイルを移動：パスが“./upload/”の場合もあるCloud9
        }else{
            $filename = "";
        }
        
        
        if( !is_null( $file ) ) {
        //データ更新
        $books = Book::find($request->id);
        $books->item_name   = $request->item_name;
        $books->item_text   = $request->item_text;
        $books->item_number = $request->item_number;
        $books->item_amount = $request->item_amount;
        $books->item_img = $filename;
        $books->published   = $request->published;
        $books->save();
        return redirect('/')->with('message', '本登録が完了しました');
    }else{
        $books = Book::find($request->id);
        $books->item_name   = $request->item_name;
        $books->item_text   = $request->item_text;
        $books->item_number = $request->item_number;
        $books->item_amount = $request->item_amount;
        //$books->item_img = $filename;
        $books->published   = $request->published;
        $books->save();
        return redirect('/')->with('message', '本登録が完了しました');
    }
    }
    //登録画面
    public function register(Book $book_id) {
        $books = Book::where('user_id',Auth::user()->id)->find($book_id);
        return view('booksregister', [
            'book' => $books
        ]);
    }
    //登録
    public function store(Request $request) {
        //バリデーション
        $validator = Validator::make($request->all(), [
                'item_name' => 'required|min:3|max:255',
                'item_number' => 'required|regex:/\A([0-9]{1,})+\z/u|max:8',
                'item_amount' => 'required|regex:/\A([0-9]{1,})+\z/u',
                'published' => 'required',
        ]);
        //バリデーション:エラー 
        if ($validator->fails()) { 
                return redirect('/')
                    ->withInput()
                    ->withErrors($validator);
        }
        $file = $request->file('item_img'); //file取得
        if(!empty($file)){                //fileが空かチェック
            $filename = $file->getClientOriginalName();   //ファイル名を取得
            $move = $file->move('./upload/',$filename);  //ファイルを移動：パスが“./upload/”の場合もあるCloud9
        }else{
            $filename = "";
        }

        
        //Eloquentモデル（登録処理）
        $books = new Book;
        $books->user_id  = Auth::user()->id; //追加のコード
        $books->item_name = $request->item_name;
        $books->item_number = $request->item_number;
        $books->item_amount = $request->item_amount;
        $books->item_text = $request->item_text;
        $books->item_img = $filename;
        $books->published = $request->published;
        $books->save();
        return redirect('/')->with('message', '本登録が完了しました');
    }
        
    //削除処理
    public function destroy(Book $book) {
        $book->delete();
        return redirect('/')->with('message', '削除が完了しました');
    }
    
    
}