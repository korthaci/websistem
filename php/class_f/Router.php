<?php if (!defined('otoban')) exit('Veriyolu açık değil.');

/**
 * Router Class
 * 
 * Available Routes:
 * - / : Homepage (kolon1)
 * - /sayfa/{id} : Page display
 * - /s/{id} : Alias for sayfa
 * - /sekme/{id} : Tab display
 * - /uis : User system operations
 * - /ui : User interface operations
 * - /u/{id} : User profile display
 * - /ara : Search functionality
 * - /form_tablolar
 */
class Router {
	private PDO $db;
	private $routes = [];
	private $params = [];
	private $n;
	private $do_;
	private $error_log = false;
	private static $instance = null;

	private function __construct(PDO $db, $do_) {
		$this->db = $db;
		$this->do_ = $do_;
		$this->n = (isset($_GET['n']) && is_numeric($_GET['n'])) ? intval($_GET['n']) : 0;
		$this->setupRoutes();
	}

	public static function getInstance(PDO $db, $do_) {
		if (self::$instance === null) {
			self::$instance = new self($db, $do_);
		}
		return self::$instance;
	}	
	private function logError($message) {
		if (!$this->error_log) {
			return;
		}
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
		$caller = isset($backtrace[1]) ? $backtrace[1]['function'] : 'unknown';
		$timestamp = date('Y-m-d H:i:s');
		$logMessage = "[{$timestamp}] [{$caller}] {$message}" . PHP_EOL;
		error_log($logMessage);
	}
	
	public function setErrorLog($enabled) {
		$this->error_log = (bool)$enabled;
	}

	private function fetchByUrl(string $table, string $url) {
		$sql = "SELECT no FROM {$this->do_}{$table} WHERE url = :url";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':url', $url, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	private function get_sayfa_sekme_id_by_url($url) {
		// Önce sayfa tablosunu kontrol et
		$id = $this->fetchByUrl('sayfa', $url);
		if ($id) {
			$_GET['sayfa'] = 'url';
			return ['id' => $id, 'type' => 'sayfa'];
		}
		
		$id = $this->fetchByUrl('sekme', $url);
		if ($id) {
			$_GET['sekme'] = 'url';
			return ['id' => $id, 'type' => 'sekme'];
		}
		
		return false;
	}

	private function getAktifBilesenler(): array {
		static $cache = null;
		if ($cache !== null) {
			return $cache;
		}
		$stmt = $this->db->query("SELECT url FROM {$this->do_}bilesen WHERE yayin = 1");
		return $cache = $stmt->fetchAll(PDO::FETCH_COLUMN);
	}

	private function tableExists($table) {
		try {
			$this->db->query("SELECT 1 FROM {$this->do_}$table LIMIT 1");
			return true;
		} catch (PDOException $e) {
			$this->logError("Table check failed for {$this->do_}$table: " . $e->getMessage());
			return false;
		}
	}

	private function safeQuery($query, $params = [], $default = false) {
		try {
			$stmt = $this->db->prepare($query);
			$stmt->execute($params);
			return $stmt->fetchColumn();
		} catch (PDOException $e) {
			$this->logError("Database query failed: " . $e->getMessage() . " Query: " . $query);
			return $default;
		}
	}

	private function setupRoutes() {
		// Example of how to add a new page route
		$this->addRoute('sayfa', function($id) {
			if (!$id) {
				return ['file' => YONLENDIR_D.'/404', 'params' => ['type' => '404']];
			}
			if (!$this->tableExists('sayfa')) {
				$this->logError("Attempted to access non-existent table 'sayfa'");
				return ['file' => YONLENDIR_D.'/404', 'params' => ['type' => '404']];
			}
			
			$exists = $this->safeQuery(
				"SELECT no FROM {$this->do_}sayfa WHERE no = :id AND yayin = 1",
				['id' => $id],
				false
			);
			
			return [
				'file' => $exists ? YONLENDIR_D.'/sayfa' : YONLENDIR_D.'/404',
				'params' => ['id' => $id, 'type' => 'sayfa']
			];
		});
		// Example of how to add a tab route
		$this->addRoute('sekme', function($id) {
			if (!$id) {
				return ['file' => YONLENDIR_D.'/404', 'params' => ['type' => '404']];
			}
			if (!$this->tableExists('sekme')) {
				$this->logError("Attempted to access non-existent table 'sekme'");
				return ['file' => YONLENDIR_D.'/404', 'params' => ['type' => '404']];
			}
			
			$exists = $this->safeQuery(
				"SELECT no FROM {$this->do_}sekme WHERE no = :id AND yayin = 1",
				['id' => $id],
				false
			);
			
			return [
				'file' => $exists ? YONLENDIR_D.'/sekme' : YONLENDIR_D.'/404',
				'params' => ['id' => $id, 'type' => 'sekme']
			];
		});

		// User related routes (kept from original)
		$this->addRoute('uis', function() {
			return [
				'file' => R_PHP.'/uyelik/islemler',
				'params' => ['type' => 'uis']
			];
		});

		$this->addRoute('ui', function() {
			return [
				'file' => R_PHP.'/uis2/islemler',
				'params' => ['type' => 'ui']
			];
		});

		$this->addRoute('u', function($id) {
			if (!$this->tableExists('uyeler')) {
				$this->logError("Attempted to access uyeler");
				return ['file' => YONLENDIR_D.'/404', 'params' => ['type' => '404']];
			}

			$count = $this->safeQuery(
				"SELECT COUNT(no) FROM {$this->do_}uyeler WHERE no = :id",
				['id' => $id],
				0
			);
			
			return [
				'file' => $count > 0 ? YONLENDIR_D.'/uye' : YONLENDIR_D.'/404',
				'params' => ['id' => $id, 'type' => 'uye']
			];
		});

		// Search route
		$this->addRoute('ara', function() {
			return [
				'file' => YONLENDIR_D.'/ara',
				'params' => ['type' => 'ara']
			];
		});

		// Add custom route handler for homepage
		$this->addRoute('', function() {
			return [
				'file' => YONLENDIR_D.'/kolon1',
				'params' => ['type' => '']//home
			];
		});

	}

	public function addRoute($path, $callback) {
		$this->routes[$path] = $callback;
	}

	public function dispatch($uri) {
		$this->params = $_GET;

		foreach ($this->getAktifBilesenler() as $bilesen) {
			if (isset($_GET[$bilesen])) {

				$bilesenDir = BILESEN_DIR . '/' . $bilesen;
				if (!is_dir($bilesenDir)) {
					continue;
				}

				$this->params = array_merge($this->params, [
					'bilesen' => $bilesen,
					'islem'   => $_GET[$bilesen],
					'type'    => 'bilesen'
				]);

				return $bilesenDir . '/islemler';
			}
		}

		$uri = explode('?', $uri)[0];
		$segments = explode('/', trim($uri, '/'));

		$routeKey = $segments[0] ?? '';
		$param    = $segments[1] ?? null;

		if (isset($this->routes[$routeKey])) {
			$result = $this->routes[$routeKey]($param);
			$this->params = array_merge($this->params, $result['params'] ?? []);
			return $result['file'];
		}

		if ($routeKey === '') {
			return YONLENDIR_D . '/kolon1';
		}

		if (
			$routeKey !== '' &&
			$param !== null &&
			in_array($routeKey, $this->getAktifBilesenler(), true) &&
			defined('BILESEN_DIR') &&
			is_dir(BILESEN_DIR . '/' . $routeKey)
		) {
			$this->params = array_merge($this->params, [
				'bilesen' => $routeKey,
				'islem'   => $param,
				'type'    => 'bilesen'
			]);

			return BILESEN_DIR . '/' . $routeKey . '/islemler';
		}

		if ($param === null && $routeKey !== '') {

			/*
			if (preg_match('/^([a-z0-9\-]+)-araba-kiralama$/i', $routeKey, $m)) {
				$this->params = array_merge($this->params, [
					'araba'  => 'liste',
					'bolge1' => $m[1],
					'type'   => 'araba'
				]);
				return YONLENDIR_D . '/araba';
			}
			*/

			$result = $this->get_sayfa_sekme_id_by_url($routeKey);

			if ($result) {

				$seoId     = $result['id'];
				$foundType = $result['type'];

				if ($foundType === 'sayfa') {

					$exists = $this->safeQuery(
						"SELECT no FROM {$this->do_}sayfa WHERE no = :id AND yayin = 1",
						['id' => $seoId],
						false
					);

					if ($exists) {
						$this->params = array_merge($this->params, [
							'id'   => $seoId,
							'type' => 'sayfa'
						]);

						return YONLENDIR_D . '/sayfa';
					}

				} elseif ($foundType === 'sekme') {

					$exists = $this->safeQuery(
						"SELECT no FROM {$this->do_}sekme WHERE no = :id AND yayin = 1",
						['id' => $seoId],
						false
					);

					if ($exists) {
						$this->params = array_merge($this->params, [
							'id'   => $seoId,
							'type' => 'sekme'
						]);

						return YONLENDIR_D . '/sekme';
					}
				}
			}
		}

		return YONLENDIR_D . '/404';
	}


	public function getParams() {
		return $this->params;
	}

	public function getN() {
		return $this->n;
	}
}
?>