<?php
// 郵便番号検索クラスファイルを読み込む
require_once(dirname(__FILE__) . '/Zip.php');

// 現行の郵便番号検索(7桁)
$aryObjCsv = Zip::search('8660823');
pre_var_dump($aryObjCsv);

// (4桁目に区切り文字が入っていても検索可能：862-0950)
$aryObjCsv = Zip::search('862-0950');
pre_var_dump($aryObjCsv);

// 旧式の郵便番号検索(3桁 or 5桁)
$aryObjCsv = Zip::search('866', FALSE);
pre_var_dump($aryObjCsv);

// searchメソッドの戻り値はオブジェクト配列。
// 0番目のデータに検索結果情報が入っており、
// 郵便番号がヒットした場合、1番目以降にデータが入る。
// エラーが発生した場合、検索結果情報の内容が正常時と変わる。
// 詳しくは、Zip.phpの_getCsvAllメソッドの関数コメントを参照。

/**
 * var_dump()の出力内容を見やすく表示
 *
 * @param いくつでも好きなように
 * @return 無し
 * @memo preタグのfont-sizeは好きな大きさに変更してください
 */
function pre_var_dump() {
	// 引数の取得
	$args = func_get_args();
	// 引数が1つの場合は引数自体を配列自体にする
	if (func_num_args() == 1) {
		$args = $args[0];
	}
	echo '<pre style="font-size:12px">';
	var_dump($args);
	echo '</pre>';
}