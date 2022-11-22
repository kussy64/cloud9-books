<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;


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
        public function postCsv(Request $request) {
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

  public function importCSV(Request $request)
  {

     //postで受け取ったcsvファイルデータ
     $file = $request->file('csvdata');
    if(is_null($file)){
        return redirect('/')->with('message', 'ファイルを選択してください');
    }
     //Goodby CSVのconfig設定
     $config = new LexerConfig();
     $interpreter = new Interpreter();
     $lexer = new Lexer($config);

     //CharsetをUTF-8に変換
     $config->setToCharset("UTF-8");
     $config->setFromCharset("sjis-win");
     $config->setIgnoreHeaderLine(true);

     $rows = array();
     

     $interpreter->addObserver(function(array $row) use (&$rows) {
         $rows[] = $row;
     });

     // CSVデータをパース
     $lexer->parse($file, $interpreter);

     $data = array();
     

     // CSVのデータを配列化
     foreach ($rows as $key => $value) {

        $arr = array();
        
        foreach ($value as $k => $v) {

            switch ($k) {

        	case 0:
        	$arr['user_id'] = $v;
        	break;
        	
        	case 1:
        	$arr['item_name'] = $v;
        	break;

        	case 2:
        	$arr['item_text'] = $v;
        	break;
        	
        	case 3:
        	$arr['item_number'] = $v;
        	break;
        	
        	case 4:
        	$arr['item_amount'] = $v;
        	break;
        	
        	case 5:
        	$arr['item_img'] = $v;
        	break;
        	
        	case 6:
        	$arr['published'] = $v;
        	break;
        	

        	

        	default:
        	break;
            }

        }
        //dd($arr);
                        //　バリデーション処理
        $validator = Validator::make($arr,[
           'item_name' => 'unique:books|required|min:3|max:255',
           'item_text' => 'unique:books|required|min:3|max:255',
           'user_id' => 'required',
           'item_amount' => 'required',
           'published' => 'required'
        ]);

        if ($validator->fails()) {
           return redirect('/')->withErrors($validator)->withInput();
        }

        $data[] = $arr;

    }

    // DBに一括保存
    Book::insert($data);

    return redirect('/')->with('save_message', 'CSVのデータを読み込みました');

  }
    public function import(Request $request)
    {

    // ロケールを設定(日本語に設定)
    setlocale(LC_ALL, 'ja_JP.UTF-8');

    // アップロードしたファイルを取得
    // 'csv_file' はビューの inputタグのname属性
    $uploaded_file = $request->file('csvdata');

    // アップロードしたファイルの絶対パスを取得
    $file_path = $request->file('csvdata')->path($uploaded_file);

    //SplFileObjectを生成
    $file = new SplFileObject($file_path);

    //SplFileObject::READ_CSV が最速らしい
    $file->setFlags(SplFileObject::READ_CSV);


    $row_count = 1;
    
    //取得したオブジェクトを読み込み
    foreach ($file as $row)
    {
        // 最終行の処理(最終行が空っぽの場合の対策
        if ($row === [null]) continue; 
        
        // 1行目のヘッダーは取り込まない
        if ($row_count > 1)
        {
            // CSVの文字コードがSJISなのでUTF-8に変更
            $item_name = mb_convert_encoding($row[0], 'UTF-8', 'SJIS');
            $item_text = mb_convert_encoding($row[1], 'UTF-8', 'SJIS');
            $item_number = mb_convert_encoding($row[2], 'UTF-8', 'SJIS');
            $item_amount = mb_convert_encoding($row[3], 'UTF-8', 'SJIS');
            $item_img = mb_convert_encoding($row[4], 'UTF-8', 'SJIS');
            $published = mb_convert_encoding($row[5], 'UTF-8', 'SJIS');
            
            //1件ずつインポート
                CSVimport::insert(array(
                    'item_name' => $item_name, 
                    'item_text' => $item_text, 
                    'item_number' => $item_number, 
                    'item_amount' => $item_amount,
                    'item_img' => $item_img,
                    'published' => $published
                ));
        }
        $row_count++;
    }
    
    return view('welcome');

    }

}

