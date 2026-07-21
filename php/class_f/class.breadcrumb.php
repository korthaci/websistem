<?php

class Breadcrumb {

	private PDO $pdo;
	private string $do_;

	public function __construct(PDO $pdo, string $do_) {
		$this->pdo = $pdo;
		$this->do_ = $do_;
	}

	public function getItems(array $routeParams): array {

		$type = (string)($routeParams['type'] ?? '');
		$id = (int)($routeParams['id'] ?? 0);

		switch ($type) {

			case 'sayfa':
				$items = $this->getSayfa($id);
				break;

			case 'sekme':
				$items = $this->getSekmeChain($id);
				$this->deactivateLast($items);
				break;

			case 'uye':
				$items = $this->getUye($id);
				break;

			case 'ara':
				$items = [
					$this->createItem(yc('Arama'))
				];
				break;

			case 'bilesen':

				$bilesen = (string)($routeParams['bilesen'] ?? '');
				$dosya = BILESEN_DIR . '/' . $bilesen . '/php/breadcrumb.php';

				if ($bilesen && is_file($dosya)) {

					$pdo = $this->pdo;
					$do_ = $this->do_;

					$items = include $dosya;

					if (!is_array($items)) {
						$items = [];
					}

				} else {
					$items = [];
				}

				break;

			default:
				return [];

		}

		if (!empty($items)) {
			$this->addHome($items);
		}

		return $items;

	}

	private function createItem(string $title, string $url = ''): stdClass {

		$item = new stdClass();

		$item->title = $title;
		$item->url = $url;
		$item->url_var = (int)($url !== '');

		return $item;

	}

	private function addHome(array &$items): void {

		array_unshift(
			$items,
			$this->createItem(
				yc('Ana Sayfa'),
				LOCAL . '/'
			)
		);

	}

	private function getSekmeChain(int $id, int $depth = 0): array {

		if ($depth >= 4 || $id <= 0) {
			return [];
		}

		$sql = "SELECT no, adi, url, ust_s_no
				FROM {$this->do_}sekme
				WHERE no = :no AND yayin = 1
				LIMIT 1";

		$s = $this->pdo->prepare($sql);
		$s->execute([':no' => $id]);

		$sekme = $s->fetch(PDO::FETCH_OBJ);

		if (!$sekme) {
			return [];
		}

		$items = [];

		if ((int)$sekme->ust_s_no > 0) {
			$items = $this->getSekmeChain(
				(int)$sekme->ust_s_no,
				$depth + 1
			);
		}

		$items[] = $this->createItem(
			cc($sekme->adi, $sekme->no, 'sekme', 'adi'),
			href('index', 'sekme=' . $sekme->url . '.' . $sekme->no)
		);

		return $items;

	}

	private function getSayfa(int $id): array {

		if ($id <= 0) {
			return [];
		}

		$sql = "SELECT no, adi, url, ms_no
				FROM {$this->do_}sayfa
				WHERE no = :no AND yayin = 1
				LIMIT 1";

		$s = $this->pdo->prepare($sql);
		$s->execute([':no' => $id]);

		$sayfa = $s->fetch(PDO::FETCH_OBJ);

		if (!$sayfa) {
			return [];
		}

		$items = [];

		if ((int)$sayfa->ms_no > 0) {
			$items = $this->getSekmeChain((int)$sayfa->ms_no);
		}

		$items[] = $this->createItem(
			cc($sayfa->adi, $sayfa->no, 'sayfa', 'adi')
		);

		return $items;

	}

	private function getUye(int $id): array {

		if ($id <= 0) {
			return [];
		}

		$sql = "SELECT no, adi
				FROM {$this->do_}uyeler
				WHERE no = :no AND yayin = 1
				LIMIT 1";

		$s = $this->pdo->prepare($sql);
		$s->execute([':no' => $id]);

		$uye = $s->fetch(PDO::FETCH_OBJ);

		if (!$uye) {
			return [];
		}

		return [
			$this->createItem($uye->adi)
		];

	}

	private function deactivateLast(array &$items): void {

		if (empty($items)) {
			return;
		}

		$last = array_key_last($items);

		$items[$last]->url = '';
		$items[$last]->url_var = 0;

	}

}