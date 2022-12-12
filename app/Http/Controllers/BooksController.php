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
       $error_list = [];
        $count = 1;

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
        //もしバリデーションが失敗したら
            if ($validator->fails() === true) {
                $error_list[$count] = $validator->errors()->all();
            }

            $count++;
        
        
        if (count($error_list) > 0) {
           //／の画面に行きバリデーションメッセージを出す
           return redirect('/')->withErrors($validator)->withInput()->with($error_list . '件の項目を読み込みました');
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






    public function ajax_store(Request $request) {

        $csv_rules = [
            0 => 'unique:books,item_name|required|min:3|max:255',
            1 => 'required|string',
            2 => 'required|string|min:6'
            
        ];
        $request->validate([
            'csv_file' => [
                'required',
                'file',
                new Csv($csv_rules, 'sjis-win')
            ]
        ]);

        $csv_data = $request->csv_file_data; // パッケージが作成したCSVデータ

        foreach($csv_data as $row_data) {

            $user = new \App\User();
            $user->email = $row_data[0];
            $user->name = $row_data[1];
            $user->password = bcrypt($row_data[2]);
            $user->save();

        }

        return ['result' => true];

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
    
     //・公開関数 stores(Request $request)
    public function stores(Request $request)
    {
        // アップロードファイルに対してのバリデート
        //・バリデーション = 擬似変数->validateUploadFile($request)
        $validator = $this->validateUploadFile($request);
        //・もしバリデーションが失敗したら
        if ($validator->fails() === true){
        //・ホーム画面に戻り、メッセージを表示。
            return redirect('/form')->with('message', $validator->errors()->first('csv_file'));
        }
        
        // CSVファイルをサーバーに保存
        $temporary_csv_file = $request->file('csv_file')->store('csv');

        $fp = fopen(storage_path('app/') . $temporary_csv_file, 'r');
        //・一行目のヘッダーを読み込む
        // 一行目（ヘッダ）読み込み
        $headers = fgetcsv($fp);
        //・配列column_names = [];
        $column_names = [];

        // CSVヘッダ確認
        //・for文(変数ヘッダー as ヘッダー)
        foreach ($headers as $header) {
            //・変数result = テストModelのretrieveTestColumnsByValue($header,'SJIS-win');
            $result = Test::retrieveTestColumnsByValue($header, 'SJIS-win');
            //・もしresultがnullなら
            if ($result === null) {
                //・fclose($fp)
                fclose($fp);
                //・Storageに保存してある$temporary_csv_fileを削除
                Storage::delete($temporary_csv_file);
                //・ホーム画面に戻り、メッセージを表示。
                return redirect('/form')
                    ->with('message', '登録に失敗しました。CSVファイルのフォーマットが正しいことを確認してださい。');
            }
            //・$column_names[]に$resultを入れる
            $column_names[] = $result;
        }
        //・registration_errors_list = [];(配列)
        $registration_errors_list = [];
        $update_errors_list       = [];
        $i = 0;

        // TODO:サイズが大きいCSVファイルを読み込む場合、この処理ではメモリ不足になる可能性がある為改修が必要になる
        while ($row = fgetcsv($fp)) {

            // Excelで編集されるのが多いと思うのでSJIS-win→UTF-8へエンコード
            mb_convert_variables('UTF-8', 'SJIS-win', $row);
            $is_registration_row = false;
            //・for文($column_names as $column_no => $column_name)
            foreach ($column_names as $column_no => $column_name) {

                // idがなければ登録、あれば更新と判断
                //・もしidがなかったら、登録、あれば更新削除
                if ($column_name === 'id' && $row[$column_no] === '') {
                    $is_registration_row = true;
                }

                // 新規登録か更新かのチェック
                //・もしis_registration_rowがあれば
                if($is_registration_row === true){
                    //・もし($column_nameがかぶっていなかったら)
                    if ($column_name !== 'id') {
                        //・$registration_csv_id_list[$i][$column_name] = $row[$column_no] === '' ? null : $row[$column_no];
                        $registration_csv_list[$i][$column_name] = $row[$column_no] === '' ? null : $row[$column_no];
                    }
                } else {
                    $update_csv_list[$i][$column_name] = $row[$column_no] === '' ? null : $row[$column_no];
                }

            }

            // バリデーションチェック
            //・バリデーション = バリデーション作成
            $validator = \Validator::make(
                //・$is_registration_rowが
                $is_registration_row === true ? $registration_csv_list[$i] : $update_csv_list[$i],
                $this->defineValidationRules(),
                $this->defineValidationMessages()
            );
            //・もしバリデーションが失敗したら、
            if ($validator->fails() === true) {
                //・もしis_registration_rowがあれば
                if ($is_registration_row === true) {
                    $registration_errors_list[$i + 2] = $validator->errors()->all();
                } else {
                    $update_errors_list[$i + 2] = $validator->errors()->all();
                }
            }

            $i++;
        }

        // バリデーションエラーチェック
        if (count($registration_errors_list) > 0 || count($update_errors_list) > 0) {
            return redirect('/form')
                ->with('errors', ['registration_errors' => $registration_errors_list, 'update_errors' => $update_errors_list]);
        }

        // 既存更新処理
        if (isset($update_csv_list) === true) {
            foreach ($update_csv_list as $update_csv) {
                // ～更新用の処理～
                if ($this->fill($update_csv)->save() === false) {
                    return redirect('/form')
                        ->with('message', '既存データの更新に失敗しました。（新規登録処理は行われずに終了しました）');
                }
            }
        }

        // 新規登録処理
        if (isset($registration_csv_list) === true) {
            foreach ($registration_csv_list as $registration_csv) {
                // ～登録用の処理～
                if ($this->fill($registration_csv)->save() === false) {
                    return redirect('/form')->with('message', '新規登録処理に失敗しました。');
                }
            }
        }

        return redirect('/form')->with('message', 'CSV登録が完了しました。' );
    }

    /**
     * アップロードファイルのバリデート
     * （※本来はFormRequestClassで行うべき）
     *
     * @param Request $request
     * @return Illuminate\Validation\Validator
     */
    private function validateUploadFile(Request $request)
    {
        return \Validator::make($request->all(), [
                'csv_file' => 'required|file|mimetypes:text/plain|mimes:csv,txt',
            ], [
                'csv_file.required'  => 'ファイルを選択してください。',
                'csv_file.file'      => 'ファイルアップロードに失敗しました。',
                'csv_file.mimetypes' => 'ファイル形式が不正です。',
                'csv_file.mimes'     => 'ファイル拡張子が異なります。',
            ]
        );
    }

    /**
     * バリデーションの定義
     *
     * @return array
     */
    private function defineValidationRules()
    {
        return [
            // CSVデータ用バリデーションルール
            'content' => 'required',
        ];
    }

    /**
     * バリデーションメッセージの定義
     *
     * @return array
     */
    private function defineValidationMessages()
    {
        return [
            // CSVデータ用バリデーションエラーメッセージ
            'content.required' => '内容を入力してください。',
        ];
    }
}

