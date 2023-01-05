<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


//使うClassを宣言:自分で追加
use App\Book;   //Bookモデルを使えるようにする
use Validator;  //バリデーションを使えるようにする
use Auth;       //認証モデルを使用する
use SplFileObject;
use Sukohi\CsvValidator\Rules\Csv;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\LexerConfig;

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
        $date = $request->input('date');
        $enddate = $request->input('enddate');
        if (!empty($keyword)) {
            $books = Book::where('item_name', 'LIKE', "%{$keyword}%")->orwhere('item_text', 'LIKE', "%{$keyword}%")->orwhereBetween('published', [$date,$enddate])->where('user_id',Auth::user()->id)->orderBy('created_at', 'asc')->paginate(4);
        }
        elseif (empty($keyword)){
            $books = Book::whereBetween('published', [$date,$enddate])->where('user_id',Auth::user()->id)->orderBy('created_at', 'asc')->paginate(4);
        }elseif (empty($date)){
            $books = Book::where('item_name', 'LIKE', "%{$keyword}%")->orwhere('item_text', 'LIKE', "%{$keyword}%")->where('user_id',Auth::user()->id)->orderBy('created_at', 'asc')->paginate(4);
        }else {
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
    //CSV処理
        //・公開メソッドpostCsv
        public function postCsv(Request $request) {
        //戻り値　返答()->streamDownload
        return response()->streamDownload(
            function () {
                // 出力バッファをopen
                $stream = fopen('php://output', 'w');
                // 文字コードをShift-JISに変換
                stream_filter_prepend($stream,'convert.iconv.utf-8/cp932//TRANSLIT');
                // ヘッダー
                fputcsv($stream, [
                    'user_id',
                    'item_name',
                    'item_text',
                    'item_number',
                    'item_amount',
                    'item_img',
                    'published',
                    'created_at',
                    'updated_at'
                ]);
                // データ
                foreach (Book::cursor() as $customer) {
                    fputcsv($stream, [
                        $customer->user_id,
                        $customer->item_name,
                        $customer->item_text,
                        $customer->item_number,
                        $customer->item_amount,
                        $customer->item_img,
                        $customer->published,
                        $customer->created_at,
                        $customer->updated_at
                        
                    ]);
                }
                fclose($stream);
            }, 
            'customers.csv',
            [
                'Content-Type' => 'application/octet-stream',
            ]
        );
    }
    

    
//公開　関数　importCSV(受け取った要求)
  public function importCSV(Request $request)
  {
    $validator = $this->validateUploadFile($request);
     //postで受け取ったcsvファイルデータ
     //$file = 受け取った要求->file（'CSVアップロード時の渡されたデータ'）
     $file = $request->file('csvdata');
     //もし($fileにデータが渡されていなかったら)
     if ($validator->fails() === true){
            return redirect('/')->with('message', $validator->errors()->first('csvdata'));
        }
     

     //Goodby CSVのconfig設定
     //設定値 = 新しい 語彙設定();
     $config = new LexerConfig();
     //通訳者 = 新しい 通訳者();
     $interpreter = new Interpreter();
     //語彙 = 新しい 語彙(設定値);
     $lexer = new Lexer($config);

     //CharsetをUTF-8に変換
     $config->setToCharset("UTF-8");
     //設定値->設定された文字セット("UTF-8");
     $config->setFromCharset("sjis-win");
     //設定値->文字セットから設定("sjis-win")
     $config->setIgnoreHeaderLine(true);
     //設定値->設定されたヘッダー行を無視する(true);
     //列 = 配列
     $rows = array();
     //通訳者->オブザーバーを追加する(関数(配列　列) 使う(&列)
     $interpreter->addObserver(function(array $row) use (&$rows) {
         //配列[] = 列
         $rows[] = $row;
     });

     // CSVデータをパース
     //語彙->解析($file,通訳者);
     $lexer->parse($file, $interpreter);
     //データ = 配列();$data変数にCSVデータを入れている
     $data = array();
     
    
     // CSVのデータを配列化
     //foreach(列 as 変数key => 変数value)
     foreach ($rows as $key => $value) {
        //変数 = 配列();
        $arr = array();
        //カウント = 0;
        $count = 0;
        //foreach(変数value as 変数k => 変数v)
        foreach ($value as $k => $v) {
            //switch (変数k)
            switch ($k) {
            //0ならuser_idに値$vを入れる
        	case 0:
        	$arr['user_id'] = $v;
        	break;
        	//1ならitem_textに値$vを入れる
        	case 1:
        	$arr['item_name'] = $v;
        	break;
        	//2ならitem_textに値$vを入れる
        	case 2:
        	$arr['item_text'] = $v;
        	break;
        	//3ならitem_numberに値$vを入れる
        	case 3:
        	$arr['item_number'] = $v;
        	break;
        	//4ならitem_amountに値$vを入れる
        	case 4:
        	$arr['item_amount'] = $v;
        	break;
        	//5ならitem_imgに値$vを入れる
        	case 5:
        	$arr['item_img'] = $v;
        	break;
        	//6ならpublishedに値$vを入れる
        	case 6:
        	$arr['published'] = $v;
        	break;
        	

        	
            //デフォルトではbreakを行う。
        	default:
        	break;
            }

        }
        //dd($arr);
        //・配列error_listを作成
       //$error_list = [];
       //・カウントを1にする
        //$count = 1;
        //$validator = Validator::make($this->defineValidationRules($arr),
        //$this->defineValidationMessages($arr)
        //);
                        //　バリデーション処理
        $validator = Validator::make($arr,[
            //item_nameがDBのbooksテーブルに既に存在しているのか、空欄ではないか、3文字以下、255文字を超えていないか確認
            'item_name' => 'unique:books,item_name|required|min:3|max:255',
           //item_textがDBのbooksテーブルに既に存在しているのか、空欄ではないか、3文字以下、255文字を超えていないか確認
          'item_text' => 'unique:books|required|min:3|max:255',
           //user_idが空欄ではないか
           'user_id' => 'required',
           //item_amountが空欄ではないか
          'item_amount' => 'required',
           //publishedが空欄ではないか
          'published' => 'required'
        ]);
            //$validated = $validator->validated();
            //dd($arr);
            //$count++;
            
        //・バリデーションがあるなら
        if ($validator->fails()=== true) {
            dd($arr);
            $validator->errors()->add('line', $key);
           //／の画面に行きバリデーションメッセージを出す
               return redirect('/')->with('message','CSVデータを読み込みました')
        //・セッション(_old_input)に入力値すべてを入れる
        ->withInput()
        //・セッション(errors)にエラーの情報を入れる
        ->withErrors($validator);
        }
        
        //dataに変数$arrを入れる（変数をからdata配列に変更）
        $data[] = $arr;
        

    }

    // DBに一括保存
    Book::insert($data);
    //dataの内容をDBにインサート
    //／の画面に戻りCSVのデータを読み込みましたと表示
    return redirect('/')->with('message','CSVのデータを読み込みました');

     }
    
    //・CSVファイルバリデーションチェック用のメソッド
    public function validateUploadFile(Request $request)
    {
        //CSVファイルが正しいかどうかバリデーションを行う
        return Validator::make($request->all(), [
                'csvdata' => 'required|file|mimetypes:text/plain|mimes:csv,txt',
            ], [
                'csvdata.required'  => 'ファイルを選択してください。',
                'csvdata.file'      => 'ファイルアップロードに失敗しました。',
                'csvdata.mimetypes' => 'ファイル形式が不正です。',
                'csvdata.mimes'     => 'ファイル拡張子が異なります。',
            ]
        );
    }
    //・公開メソッド（バリデーションルール設定用）
    public function defineValidationRules()
    {
        return [
            // CSVデータ用バリデーションルール
           'item_name' => 'unique:books,item_name|required|min:3|max:255',
           //item_textがDBのbooksテーブルに既に存在しているのか、空欄ではないか、3文字以下、255文字を超えていないか確認
          'item_text' => 'unique:books|required|min:3|max:255',
           //user_idが空欄ではないか
           'user_id' => 'required',
           //item_amountが空欄ではないか
          'item_amount' => 'required',
           //publishedが空欄ではないか
          'published' => 'required'
        ];
    }
    //・公開メソッド（バリデーションエラーメッセージ専用）
    public function defineValidationMessages()
    {
        return [
            // CSVデータ用バリデーションエラーメッセージ
            'item_name.unique:books,item_name' => '値が重複しています。',
            'item_name.required' => '内容を入力してください。',
            'item_name.min:3' => '3文字以上で入力してください。',
            'item_name.max:255' => '255文字以内で入力してください。',
            'item_text.unique:books,item_text' => '値が重複しています。',
            'item_text.required' => '内容を入力してください。',
            'item_text.min:3' => '3文字以上で入力してください。',
            'item_text.max:255' => '255文字以内で入力してください。',
            'user_id.required' => '内容を入力してください。',
            'item_amount.required' => '内容を入力してください。',
            'published.required' => '内容を入力してください。',
        ];
    }





    
    
}

