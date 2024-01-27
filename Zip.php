<?php
/**
 * 郵便番号検索クラス
 *
 * @version 1.0.1
 * @create 2011/09/09
 * @update 2014/12/25
 * @charset UTF-8
 * @author ntft
 * @copylight ntft
 * @licence MIT
 * @caution PHP 5 以上必須。
 *			郵便番号検索APIはリンクウェアのため、サイトにリンクを貼る必要がある。
 * @reference
 *	http://zip.cgis.biz/
 */

// 変換元文字コード
define('SRC_CHARSET', 'EUC-JP');
// 変換先文字コード
define('DESC_CHARSET', 'UTF-8');

// 置換文字列(郵便番号)
define('REPLACE_ZIP', '__ZIP__');
// 置換文字列(リクエストver)
define('REPLACE_VER', '__VER__');

// 本サーバ
define('TARGET_URL', 'http://zip.cgis.biz/csv/zip.php?zn=' . REPLACE_ZIP . '&ver=' . REPLACE_VER);
// 予備サーバ
define('TARGET_URL2', 'http://zip2.cgis.biz/csv/zip.php?zn=' . REPLACE_ZIP . '&ver=' . REPLACE_VER);
// 最大試行回数(サーバ毎)
define('TRY_MAX_CNT', 3);

// 成功時の配列数
define('ARY_OK_CNT', 9);

// 郵便番号検索クラス
class Zip
{
	/**
	 * 郵便番号検索を行う
	 *
	 * @param string $zip 郵便番号
	 * @param boolean $isNew true	:現行郵便番号(7桁)
	 *						 false	:旧式郵便番号(3桁 or 5桁)
	 * @return boolean 結果オブジェクト配列(OK) / FALSE(NG)
	 */
	public static function search($zip, $isNew = TRUE)
	{
		$zip = trim($zip);

		// 現行郵便番号
		if ($isNew) {
			// 郵便番号の形式をチェックする
			if (! Zip::_checkFormatNew($zip)) {
				return FALSE;
			}
			$ver = '0';
		}
		// 旧式郵便番号
		else {
			// 郵便番号の形式をチェックする
			if (! Zip::_checkFormatOld($zip)) {
				return FALSE;
			}
			$ver = '1';
		}

		// 読み込み時間計測用
		$past = microtime(true);

		// 「__ZIP__」を対象郵便番号で置換(本サーバ)
		$url = str_replace(array(REPLACE_ZIP, REPLACE_VER), array($zip, $ver), TARGET_URL);
		// 最大試行回数 * 2(サーバ毎)回
		for ($ii = 1, $max = TRY_MAX_CNT * 2; $ii <= $max; $ii++) {
			// 最大試行回数を超えたら予備サーバに切り替える
			if ($ii == TRY_MAX_CNT + 1) {
				// 「__ZIP__」を対象郵便番号で置換(予備サーバ)
				$url = str_replace(array(REPLACE_ZIP, REPLACE_VER), array($zip, $ver), TARGET_URL2);
			}

			// CSVファイルの取得
			$aryObjCsv = Zip::_getCsvAll($url);
			// データが取得出来た場合
			if ($aryObjCsv !== FALSE) {
				// ループ脱出
				break;
			}
		}
		// 読み込み時間計測用
		$now = microtime(true);

		// データが取得出来なかった場合
		if ($aryObjCsv === FALSE) {
			return $aryObjCsv;
		}

		// 追加情報
		// tryCnt：試行回数
		$aryObjCsv[0]->tryCnt = $ii;
		// loadTime：読み込み時間(マイクロ秒)
		$aryObjCsv[0]->loadTime = $now - $past;

		return $aryObjCsv;
	}

	/**
	 * 郵便番号(現行)の形式をチェックする
	 *
	 * @param string &$zip 郵便番号
	 * @return boolean true(OK) / false(NG)
	 * @memo 郵便番号の形式は「半角数字7桁、または4文字目が区切り文字の8桁」
	 */
	private static function _checkFormatNew(&$zip)
	{
		// 文字列長の取得
		$len = strlen($zip);

		// 7文字の場合
		if ($len === 7) {
			// 何もしない
		}
		// 8文字の場合
		elseif ($len > 7) {
			// 区切り文字を除いた郵便番号にする
			$zip = preg_replace('/[^0-9]/', '', $zip);
		}
		// それ以外の場合
		else {
			// フォーマットエラー
			return false;
		}

		// 半角数字7桁以外
		if (! preg_match('/^\d{7}$/', $zip)) {
			// フォーマットエラー
			return false;
		}

		return true;
	}

	/**
	 * 郵便番号(旧式)の形式をチェックする
	 *
	 * @param string &$zip 郵便番号
	 * @return boolean true(OK) / false(NG)
	 * @memo 郵便番号の形式は「半角数字3桁、または5桁」
	 */
	private static function _checkFormatOld(&$zip)
	{
		// 文字列長の取得
		$len = strlen($zip);

		if ($len > 5) {
			// 区切り文字を除いた郵便番号にする
			$zip = preg_replace('/[^0-9]/', '', $zip);
		}

		// 半角数字3桁、または5桁以外
		if (! (preg_match('/^\d{3}$/', $zip) || preg_match('/^\d{5}$/', $zip))) {
			// フォーマットエラー
			return false;
		}

		return true;
	}

	/**
	 * CSVファイルから全情報を読み取る
	 *
	 * @param string $csvPath CSVファイルパス
	 * @param string $toEnc 変換先の文字コード
	 * @param string $fromEnc 変換元の文字コード
	 * @return array object 結果オブジェクト配列(OK) / FALSE(NG)
	 *
	 * [検索結果情報]
	 * - 成功時
	 *   [name]=>					アプリケーション名称
	 *   [version]=>				アプリケーションのバージョン
	 *   [request_url]=>			リクエストURL
	 *   [request_zip_version]=>	リクエストされた郵便番号
	 *   [result_code]=>			結果コード(正常時 = 1, エラー時 = 0)
	 *   [result_zip_num]=>			検索した郵便番号
	 *   [result_zip_version]=>		検索した郵便番号形式(0 = 7桁, 1 = 3 or 5桁)
	 *   [result_values_count]=>	該当した住所情報の数。
	 *	(追加情報)
	 *   [tryCnt]=>					試行回数
	 *   [loadTime]=>				読み込み時間(マイクロ秒)
	 *
	 * - 失敗時(count:8)
	 *   [name]=>					アプリケーション名称
	 *   [version]=>				アプリケーションのバージョン
	 *   [request_url]=>			リクエストURL
	 *   [request_zip_version]=>	リクエストされた郵便番号
	 *   [result_code]=>			結果コード(正常時 = 1, エラー時 = 0)
	 *   [error_code]=>				エラーコード
	 *   [error_note]=>				エラー内容
	 *
	 * [住所情報]：ヒットした場合
	 *   [state_kana]=>			都道府県カナ
	 *   [city_kana]=>			市区町村カナ
	 *   [address_kana]=>		住所カナ
	 *   [company_kana]=>		事業所カナ
	 *   [state]=> "熊本県"		都道府県
	 *   [city]=> "八代市"		市区町村
	 *   [address]=>			住所
	 *   [company]=>			事業所
	 *	(※該当情報が存在しない場合、「none」がセットされます。)
	 *
	 * ※request_zip_versionに「none」が入って返ってくるのは仕様のようです。
	 */
	private static function _getCsvAll($csvPath, $toEnc = DESC_CHARSET, $fromEnc = SRC_CHARSET)
	{
		// 検索結果配列
		$aryPropResult = array('name', 'version', 'request_url', 'request_zip_num',
							   'request_zip_version', 'result_code', 'result_zip_num',
							   'result_zip_version', 'result_values_count');
		// エラー配列
		$aryPropErr    = array('name', 'version', 'request_url', 'request_zip_num',
							   'request_zip_version', 'result_code', 'error_code',
							   'error_note');

		// 住所情報配列
		$aryPropAdress = array('state_kana', 'city_kana', 'address_kana',
							   'company_kana', 'state', 'city', 'address', 'company');

		// ファイルを読み取りモードでオープン
		$fp = fopen($csvPath, "r");
		// エラー処理
		if ($fp === FALSE) {
			return FALSE;
		}

		// 初期化
		$aryObjRet = array();
		$cnt = 0;

		// CSVファイルが空に行単位で読み込む
		while(($aryCsv = fgetcsv($fp)) !== FALSE) {
			$objRet = new StdClass();

			// 最初のデータ(検索結果情報)
			if ($cnt == 0) {
				// 正常
				if (count($aryCsv) == ARY_OK_CNT) {
					// 検索結果配列を代入
					$aryProp = $aryPropResult;
				}
				// 異常
				else {
					// エラー配列を代入
					$aryProp = $aryPropErr;
				}
			}
			// 住所情報
			else {
				// 住所情報配列を代入
				$aryProp = $aryPropAdress;
			}

			// 代入された配列のキーをインデックスに、値をプロパティとして使用
			foreach ($aryProp as $idx => $prop) {
				// 値が「none」の項目はNULLで置き換える
				if ($aryCsv[$idx] == "none") {
					$aryCsv[$idx] = NULL;
				}
				else {
					// CSVファイルがEUC-JPなので指定の文字コードに変換
					$aryCsv[$idx] = mb_convert_encoding($aryCsv[$idx], $toEnc, $fromEnc);
				}
				// プロパティとして代入
				$objRet->{$prop} = $aryCsv[$idx];
			}
			// 結果用オブジェクト配列に代入
			$aryObjRet[] = $objRet;
			// カウントアップ
			$cnt++;
		}
		// ファイルクローズ
		fclose($fp);

		return $aryObjRet;
	}
}