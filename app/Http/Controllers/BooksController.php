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
    
        public function rules()
    {
        return [
            'csv_file' => [
                'required',
                'max:1024', // php.iniのupload_max_filesizeとpost_max_sizeを考慮する必要があるので注意
                'file',
                'mimes:csv,txt', // mimesの都合上text/csvなのでtxtも許可が必要
                'mimetypes:text/plain',
            ],
        ];
    }
    
//公開　関数　importCSV(受け取った要求)
  public function importCSV(Request $request)
  {

     //postで受け取ったcsvファイルデータ
     //$file = 受け取った要求->file（'CSVアップロード時の渡されたデータ'）
     $file = $request->file('csvdata');
     //もし($fileにデータが渡されていなかったら)
    if(is_null($file)){
        //／の画面にもどる->とともに(message,ファイルを選択してください)；
        return redirect('/')->with('message', 'ファイルを選択してください');
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
     //データ = 配列();
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
        	
        	case 6:
        	$arr['published'] = $v;
        	break;
        	

        	

        	default:
        	break;
            }

        }
     $count++;
             $rules = $this->makeCsvValidationRules();
        $csv_errors = [];
        $csv_id_list = [];
        $csv_email_list = [];
        foreach ($rows as $line_num => $line) {
            if (0 === $line_num || 1 === count($line)) {
                // 最初の行または空行など余分な空白がCSVの途中に混ざっている場合は無視
                continue;
            }
            if (isset($line)) {
                $csv_errors = array_merge($csv_errors, ['Line '.$line_num.' カラム数が不正です']);
            }
            // 入力値バリデーション
            $validator = Validator::make($line, $rules, $this->makeCsvValidationMessages($line_num));
            if ($validator->fails()) {
                $csv_errors = array_merge($csv_errors, $validator->errors()->all());
                continue;
            }
                        //　バリデーション処理
        //$validator = Validator::make($arr,[
            //item_nameがDBのbooksテーブルに既に存在しているのか、空欄ではないか、3文字以下、255文字を超えていないか確認
          // 'item_name' => 'unique:books|required|min:3|max:255',
           //item_textがDBのbooksテーブルに既に存在しているのか、空欄ではないか、3文字以下、255文字を超えていないか確認
          // 'item_text' => 'unique:books|required|min:3|max:255',
           //user_idが空欄ではないか
           //'user_id' => 'required',
           //item_amountが空欄ではないか
          // 'item_amount' => 'required',
           //publishedが空欄ではないか
          // 'published' => 'required'
        //]);
        //もしバリデーションが失敗したら
        if ($validator->fails()) {
           //／の画面に行きバリデーションメッセージを出す
           return redirect('/')->withErrors($validator)->withInput()->with('message', $count . '件の項目を読み込みました');
        }
        
        //dataに変数$arrを入れる
        $data[] = $arr;
        

    }

    // DBに一括保存
    Book::insert($data);
    //dataの内容をDBにインサート
    //／の画面に戻りCSVのデータを読み込みましたと表示
    return redirect('/')->with('message', $count . 'CSVのデータを読み込みました');

     }



}

    public function import(CsvRequest $request)
    {
        $path = env('DOCUMENT_ROOT').'/crud/storage/app/';

        // ファイル名を現在時刻で設定
        $filename = 'csv_import_'.date('YmdHis').'.csv';

        // 一時領域保存場所にCSVファイルを配置
        $path .= $request->file('csv_file')->storeAs('csv', $filename);

        // CSV読み込み
        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV);

        // CSVの中身に対するバリデーションを実施
        $csv_errors = $this->csv->validateCsvData($file);
        if (count($csv_errors) >= 1) {
            $file = null;
            unlink($path);
            return redirect()->route('csv.index')->withInput(['csv_errors' => $csv_errors]);
        }

        // 登録、編集、削除ごとに配列整形
        $records = $this->csv->makeCsvRecords($file);

        // 配列整形後は不要なのでファイル削除
        $file = null;
        unlink($path);

        DB::beginTransaction();
        try {
            // 登録
            if (isset($records[config('const.CSV_TYPE.REGISTER')])) {
                $this->csv->createCsvData($records[config('const.CSV_TYPE.REGISTER')]);
            }

            // 編集
            if (isset($records[config('const.CSV_TYPE.EDIT')])) {
                foreach ($records[config('const.CSV_TYPE.EDIT')] as $update_val) {
                    $this->csv->updateCsvData($update_val['id'], $update_val);
                }
            }

            // 削除
            if (isset($records[config('const.CSV_TYPE.DELETE')])) {
                foreach ($records[config('const.CSV_TYPE.DELETE')] as $delete_val) {
                    $this->csv->deleteCsvData($delete_val['id'], $delete_val);
                }
            }
        } catch(\Exception $e) {
            DB::rollback();
            Log::error('アップロードしたCSVのデータの登録・編集・削除中に例外が発生しました:'.$e->getMessage());
            abort(500);
        }
        DB::commit();

        return redirect()->route('user.list');
    }

    private function makeCsvValidationRules()
    {
        $rules = [];
        foreach (config('const.CSV_HEADER_NUM') as $val) {
            switch ($val['INDEX']) {
                case config('const.CSV_HEADER_NUM.TYPE.INDEX'):
                    $rules[$val['INDEX']] = 'required|in:'.implode(config('const.CSV_TYPE'), ',');
                    break;
                case config('const.CSV_HEADER_NUM.ID.INDEX'):
                    $rules[$val['INDEX']] = 'nullable|numeric|digits_between:0,10';
                    break;
                case config('const.CSV_HEADER_NUM.NAME.INDEX'):
                    $rules[$val['INDEX']] = 'required|regex:/^[a-zA-Z]+$/|max:255';
                    break;
                case config('const.CSV_HEADER_NUM.EMAIL.INDEX'):
                    $rules[$val['INDEX']] = 'nullable|email|max:255';
                    break;
                case config('const.CSV_HEADER_NUM.PASSWORD.INDEX'):
                    $rules[$val['INDEX']] = 'nullable|regex:/^[0-9a-zA-Z]+$/|between:8,16';
                    break;
                case config('const.CSV_HEADER_NUM.AGE.INDEX'):
                    $rules[$val['INDEX']] = 'nullable|numeric|digits_between:0,2';
                    break;
                default:
                    break;
            }
        }
        return $rules;
    }

}