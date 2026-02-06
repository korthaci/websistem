<?php if (!defined('otoban')) exit('Veriyolu açık değil.');

class pdo_db_helper {
	private PDO $pdo;
	private bool $display_errors = false;

	public function __construct(PDO $pdo, bool $display_errors = false) {
		$this->pdo = $pdo;
		$this->display_errors = $display_errors;
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Tek satır veri döndürür (object olarak)
	 * Örnek: $db->satir("SELECT * FROM r_araba WHERE no = ?", [5]);
	 */
	public function satir(string $query, array $params = []): ?object {
		$stmt = $this->calistir($query, $params);
		$row = ($stmt) ? $stmt->fetch() : false;
		return ($row !== false) ? $row : null;
	}

	/**
	 * Tek bir değişken/kolon döndürür
	 * Örnek: $db->var("SELECT adi FROM r_araba WHERE no = ?", [5]);
	 */
	public function var(string $query, array $params = []): mixed {
		$stmt = $this->calistir($query, $params);
		return $stmt ? $stmt->fetchColumn() : null;
	}
	
	/**
	 * Sayı değeri döndürür (int olarak)
	 * Örnek: $db->sayi("SELECT COUNT(*) FROM r_araba WHERE yayin = ?", [1]);
	 */
	public function sayi(string $query, array $params = []): int {
		$stmt = $this->calistir($query, $params);
		return $stmt ? (int)$stmt->fetchColumn() : 0;
	}

	/**
	 * Çoklu satır listesi döndürür (array of objects)
	 * Örnek: $db->liste("SELECT * FROM r_araba WHERE yayin = ?", [1]);
	 */
	public function liste(string $query, array $params = []): array {
		$stmt = $this->calistir($query, $params);
		return $stmt ? $stmt->fetchAll() : [];
	}

	/**
	 * Tabloya veri ekler
	 * Örnek: $db->insert('r_araba', ['adi' => 'Araç 1', 'yayin' => 1]);
	 */
	public function insert(string $table, array $data): bool {
		if (empty($data)) return false;

		$columns = implode(', ', array_keys($data));
		$placeholders = ':' . implode(', :', array_keys($data));

		$query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

		$stmt = $this->calistir($query, $data);
		return $stmt !== false;
	}

	/**
	 * Tablodaki veriyi günceller
	 * Örnek: $db->update('r_araba', ['adi' => 'Yeni Ad'], ['no' => 5]);
	 */
	public function update(string $table, array $data, array $where): bool {
		if (empty($data) || empty($where)) return false;

		$setParts = [];
		foreach ($data as $key => $value) {
			$setParts[] = "{$key} = :data_{$key}";
		}
		$whereParts = [];
		foreach ($where as $key => $value) {
			$whereParts[] = "{$key} = :where_{$key}";
		}

		$query = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE " . implode(' AND ', $whereParts);

		$params = [];
		foreach ($data as $key => $value) {
			$params[":data_{$key}"] = $value;
		}
		foreach ($where as $key => $value) {
			$params[":where_{$key}"] = $value;
		}

		$stmt = $this->calistir($query, $params);
		return $stmt !== false;
	}

	/**
	 * Tablodan veri siler
	 * Örnek: $db->delete('r_araba', ['no' => 5]);
	 */
	public function delete(string $table, array $where): bool {
		if (empty($where)) return false;

		$whereParts = [];
		foreach ($where as $key => $value) {
			$whereParts[] = "{$key} = :where_{$key}";
		}

		$query = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereParts);

		$params = [];
		foreach ($where as $key => $value) {
			$params[":where_{$key}"] = $value;
		}

		$stmt = $this->calistir($query, $params);
		return $stmt !== false;
	}

	/**
	 * Sorgu çalıştırır (private method)
	 */
	private function calistir(string $query, array $params = []): ?PDOStatement {
		try {
			$stmt = $this->pdo->prepare($query);
			$stmt->execute($params);
			return $stmt;
		} catch (PDOException $e) {
			error_log("DB Error: " . $e->getMessage());

			if ($this->display_errors) {
				echo "Veritabanı hatası: " . htmlspecialchars($e->getMessage());
			}

			return null;
		}
	}

	/**
	 * Belirli bir kolonu getirir (genelde 'adi' kolonu)
	 * Örnek: $db->get('r_araba', ['no' => 5], 'adi');
	 * Örnek: $db->get('r_araba', 'no=5', 'model_yili');
	 * Örnek: $db->get('r_araba', '5'); // no=5 için 'adi' kolonunu getirir
	 */
	public function get(string $table, array|string $where, string $column = 'adi'): mixed {
		if ($where === null) return null;

		$whereParts = [];
		$params = [];

		if (is_array($where)) {
			foreach ($where as $key => $value) {
				$param_name = "where_value_{$key}";
				$whereParts[] = "{$key} = :{$param_name}";
				$params[$param_name] = $value;
			}
		} elseif (is_string($where)) {
			$where_key = 'no';
			$where_value = $where;

			if (strpos($where, '=') !== false) {
				[$where_key, $where_value] = explode('=', $where, 2);
			}

			$whereParts[] = "{$where_key} = :where_value";
			$params['where_value'] = $where_value;
		}

		$where_clause = implode(' AND ', $whereParts);
		$query = "SELECT {$column} FROM {$table} WHERE {$where_clause}";

		return $this->var($query, $params);
	}

}
