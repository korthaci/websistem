<?php

/**
 * Modül asset'lerini filtreler; bileşen (araba vb.) asset'leri bilinçli olarak
 * filtrelenmez, çünkü kullanım kayıtları AJAX ve bootstrap nedeniyle güvenilir değildir.
 * AKTIF filtre/kaydı, DEBUG yalnızca AKTIF iken tanılama yorumunu yönetir.
 */
class asset_kayit {
	public const AKTIF = true;
	public const DEBUG = false;
	private static array $kaldirilanAssetler = [];

	private static function kayitlar(): array {
		return Global_::get('kullanilan_assetler', ['modul' => [], 'bilesen' => []]);
	}

	public static function kaydet(string $tur, string $url): void {
		if (!self::etkin_mi()) {
			return;
		}

		if (!in_array($tur, ['modul', 'bilesen'], true) || $url === '') {
			return;
		}

		$kayitlar = self::kayitlar();
		$kayitlar[$tur][$url] = true;
		Global_::set('kullanilan_assetler', $kayitlar);
	}

	public static function liste(?string $tur = null): array {
		$kayitlar = self::kayitlar();

		if ($tur !== null) {
			return isset($kayitlar[$tur]) ? array_keys($kayitlar[$tur]) : [];
		}

		return [
			'modul' => array_keys($kayitlar['modul'] ?? []),
			'bilesen' => array_keys($kayitlar['bilesen'] ?? []),
		];
	}

	public static function cikti_duzenle(string $html): string {
		if (!self::etkin_mi()) {
			return $html;
		}
		$html = self::modul_asset_etiketlerini_kaldir($html);
		if (self::DEBUG) {
			$html .= self::debug_yorumu_olustur();
		}
		return $html;
	}

	/** Filtre şu an çalışsın mı: kod sabiti ve panel ayarı birlikte belirler. */
	private static function etkin_mi(): bool {
		return self::AKTIF && self::panel_acik_mi();
	}

	private static function panel_acik_mi(): bool {
		global $so_;
		return isset($so_) && $so_->d('modul_css_js_asset_duzenle') == 1;
	}

	/** Yalnızca modül asset'lerini filtreler; bileşen asset'lerine dokunmaz. */
	private static function modul_asset_etiketlerini_kaldir(string $html): string {
		self::$kaldirilanAssetler = [];
		$html_filtre_oncesi = $html;

		try {
			$kullanilan_moduller = array_fill_keys(self::liste('modul'), true);
			$modul_asset_on_eki = rtrim(str_replace(ROOT, LOCAL, MODUL_DIR), '/') . '/';

			$html = preg_replace_callback(
				'~^[\t ]*(?:<link\b[^>\r\n]*>|<script\b[^>\r\n]*>[\s\S]*?</script\s*>)[\t ]*(?:\r?\n|$)~im',
				static function ($matches) use ($kullanilan_moduller, $modul_asset_on_eki) {
					if (!preg_match('~^[\t ]*<(?:link|script)\b[^>]*>~i', $matches[0], $etiket)) {
						return $matches[0];
					}
					// (?<![\w-]) ile data-src gibi öznitelikler eşleşmez.
					if (!preg_match('~(?<![\w-])(?:href|src)\s*=\s*(["\'])(.*?)\1~i', $etiket[0], $attribute)) {
						return $matches[0];
					}

					$asset_url = html_entity_decode($attribute[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');

					if (strncmp($asset_url, $modul_asset_on_eki, strlen($modul_asset_on_eki)) !== 0) {
						return $matches[0];
					}

					$modul_yolu = substr($asset_url, strlen($modul_asset_on_eki));
					$modul_url = explode('/', $modul_yolu, 2)[0];
					if ($modul_url === '' || isset($kullanilan_moduller[$modul_url])) {
						return $matches[0];
					}

					self::$kaldirilanAssetler[] = $asset_url;
					return '';
				},
				$html
			);

			if ($html === null) {
				throw new RuntimeException('Modül asset etiketleri filtrelenemedi.');
			}

			return $html;
		} catch (Throwable $e) {
			error_log('[module_asset_filter] ' . $e->getMessage());
			self::$kaldirilanAssetler = [];
			return $html_filtre_oncesi;
		}
	}

	private static function debug_yorumu_olustur(): string {
		$assetler = [];
		foreach (self::liste() as $tur => $url_liste) {
			foreach ($url_liste as $url) {
				$assetler[] = $tur . ':' . $url;
			}
		}

		$yorum = PHP_EOL . '<!-- DEBUG_ASSET ' . self::json_yoruma_guvenli(json_encode($assetler, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . ' -->';
		if (self::$kaldirilanAssetler !== []) {
			$yorum .= PHP_EOL . '<!-- DEBUG_ASSET_REMOVED ' . self::json_yoruma_guvenli(json_encode(self::$kaldirilanAssetler, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . ' -->';
		}

		return $yorum;
	}

	private static function json_yoruma_guvenli($json): string {
		return htmlspecialchars(str_replace('--', '- -', (string)$json), ENT_QUOTES, 'UTF-8');
	}
}

