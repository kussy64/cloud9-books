<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /**
     * CSVヘッダ項目の定義値があれば定義配列のkeyを返す   
     *
     * @param string $header
     * @param string $encoding
     * @return string|null
     */
    public static function retrieveTestColumnsByValue(string $header ,string $encoding)
    {
        // CSVヘッダとテーブルのカラムを関連付けておく
        $list = [
            'user_id' => "ユーザー番号",
            'item_name' => "書籍名",
            'item_text'    => "詳細情報",
            'item_number' => "在庫数",
            'item_amount' => "金額",
            'item_img' => "画像",
            'published' => "公開日",
        ];

        foreach ($list as $key => $value) {
            if ($header === mb_convert_encoding($value, $encoding)) {
                return $key;
            }
        }
        return null;
    }
}
