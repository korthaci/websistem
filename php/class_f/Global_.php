<?php
class Global_ {
	public static $u__b__;
	public static $phpmailer;
	public static $yonlendir_index_body_class;
	public static $u_no__;
	public static $u_yetki__;
	public static $dil_yonlendir;
	public static $d;    
	public static $dil_no;
	public static $routeParams;
	public static $arama_;
	public static $bilesenler_url_dizi;
	public static $pageMetadata;
	public static $structuredData;

	/*
	üstteki değişkenlere direkt olarak ulaşılabilir. setter ve getter ile ilgili olanlar $data'nın birer elemanıdır ve her yerden her zaman oluşturulabilir.
	Global_::set('abc','def'); -> $data['abc'] = 'def';
	Global_::get('abc'); -> 'def';
	*/
	private static $data = [];
	public static function set(string $key, $value): void {
		self::$data[$key] = $value;
	}
	public static function get(string $key, $default = null) {
		return self::$data[$key] ?? $default;
	}
	public static function has(string $key): bool {
		return array_key_exists($key, self::$data);
	}
	public static function remove(string $key): void {
		unset(self::$data[$key]);
	}
	public static function all(): array {
		return self::$data;
	}
}