郵便番号クラス

■概要
郵便番号検索API(http://zip.cgis.biz/)を用いて郵便番号から住所情報を取得します。

■構成
ReadMe.txt	本ファイル
Zip.php		郵便番号クラス
index.php	サンプル

documentフォルダ内にhtmlドキュメントが入っています。
document/html/index.html をWebブラウザにドラッグ＆ドロップして閲覧してください。
(Doxygenにて作成)

■使い方
[searchメソッド]
第1引数：郵便番号
第2引数：true = 現行郵便番号(デフォルト), false = 旧式郵便番号
※現行郵便番号の場合、4桁目に区切り文字を含んでもよい(例：862-0950)。

■注意点
・PHP 5以上必須。
・郵便番号検索APIはリンクウェアであるため、サイトにリンクを貼る必要がある。

■メモ
・郵便番号には2種類ある。
　現行の郵便番号(7桁)と旧式の郵便番号(3桁 or 5桁)。
　本クラスはどちらにも対応している。

・searchメソッドの返り値
array(2) {
  [0]=>	// 検索結果情報
  object(stdClass)#2 (11) {
    ["name"]=>
    string(12) "ZipSearchXML"
    ["version"]=>
    string(4) "1.01"
    ["request_url"]=>
    string(48) "http://zip.cgis.biz/csv/zip.php?zn=8620950&ver=0"
    ["request_zip_num"]=>
    string(7) "8620950"
    ["request_zip_version"]=>
    NULL
    ["result_code"]=>
    string(1) "1"
    ["result_zip_num"]=>
    string(7) "8620950"
    ["result_zip_version"]=>
    string(1) "0"
    ["result_values_count"]=>
    string(1) "1"
    ["tryCnt"]=>
    int(1)
    ["loadTime"]=>
    float(0.62327408790588)
  }
  [1]=>	// 住所情報
  object(stdClass)#3 (8) {
    ["state_kana"]=>
    string(6) "ｸﾏﾓﾄｹﾝ"
    ["city_kana"]=>
    string(5) "ｸﾏﾓﾄｼ"
    ["address_kana"]=>
    string(7) "ｽｲｾﾞﾝｼﾞ"
    ["company_kana"]=>
    NULL
    ["state"]=>
    string(6) "熊本県"
    ["city"]=>
    string(6) "熊本市"
    ["address"]=>
    string(6) "水前寺"
    ["company"]=>
    NULL
  }
}