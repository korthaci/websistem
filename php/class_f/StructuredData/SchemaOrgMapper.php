<?php

class SchemaOrgMapper {
	public function map(SemanticContext $context): array {
		$nodes = [];

		foreach ($context->getEntities() as $entity) {
			$node = $this->mapEntity($entity);
			if ($node !== null) {
				$nodes[] = $node;
			}
		}

		return $nodes;
	}

	private function mapEntity(SemanticEntity $entity): ?array {
		return match ($entity->type) {
			'organization' => $this->mapOrganization($entity->data),
			'website' => $this->mapWebSite($entity->data),
			'webpage' => $this->mapWebPage($entity->data),
			'breadcrumb' => $this->mapBreadcrumb($entity->data),
			'list' => $this->mapItemList($entity->data),
			default => null,
		};
	}

	private function mapOrganization(array $data): ?array {
		$name = trim((string)($data['name'] ?? ''));
		if ($name === '') {
			return null;
		}

		$node = [
			'@type' => 'Organization',
			'name' => $name,
		];

		if (!empty($data['url'])) {
			$node['url'] = $data['url'];
		}

		if (!empty($data['logo'])) {
			$node['logo'] = $data['logo'];
		}

		if (!empty($data['telephone'])) {
			$node['telephone'] = $data['telephone'];
		}

		return $node;
	}

	private function mapWebSite(array $data): ?array {
		$name = trim((string)($data['name'] ?? ''));
		$url = trim((string)($data['url'] ?? ''));
		if ($name === '' && $url === '') {
			return null;
		}

		$node = ['@type' => 'WebSite'];
		if ($name !== '') {
			$node['name'] = $name;
		}
		if ($url !== '') {
			$node['url'] = $url;
		}

		return $node;
	}

	private function mapWebPage(array $data): ?array {
		$name = trim((string)($data['name'] ?? ''));
		$url = trim((string)($data['url'] ?? ''));
		if ($name === '' && $url === '') {
			return null;
		}

		$node = ['@type' => 'WebPage'];
		if ($name !== '') {
			$node['name'] = $name;
		}
		if ($url !== '') {
			$node['url'] = $url;
		}
		if (!empty($data['description'])) {
			$node['description'] = $data['description'];
		}

		return $node;
	}

	private function mapBreadcrumb(array $data): ?array {
		$items = $data['items'] ?? [];
		if (!is_array($items) || $items === []) {
			return null;
		}

		$elements = [];
		$position = 1;
		foreach ($items as $item) {
			if (!is_array($item)) {
				continue;
			}
			$name = trim((string)($item['name'] ?? ''));
			$url = trim((string)($item['url'] ?? ''));
			if ($name === '' && $url === '') {
				continue;
			}

			$listItem = [
				'@type' => 'ListItem',
				'position' => $position++,
			];
			$thing = [];
			if ($name !== '') {
				$thing['name'] = $name;
			}
			if ($url !== '') {
				$thing['@id'] = $url;
			}
			if ($thing !== []) {
				$listItem['item'] = $thing;
			}
			$elements[] = $listItem;
		}

		if ($elements === []) {
			return null;
		}

		return [
			'@type' => 'BreadcrumbList',
			'itemListElement' => $elements,
		];
	}

	private function mapItemList(array $data): ?array {
		$items = $data['items'] ?? [];
		if (!is_array($items) || $items === []) {
			return null;
		}

		$elements = [];
		foreach ($items as $item) {
			if (!is_array($item)) {
				continue;
			}
			$position = (int)($item['position'] ?? 0);
			$name = trim((string)($item['name'] ?? ''));
			$url = trim((string)($item['url'] ?? ''));
			if ($position < 1 || ($name === '' && $url === '')) {
				continue;
			}

			$listItem = [
				'@type' => 'ListItem',
				'position' => $position,
			];
			$thing = ['@type' => 'Thing'];
			if ($name !== '') {
				$thing['name'] = $name;
			}
			if ($url !== '') {
				$thing['url'] = $url;
			}
			$listItem['item'] = $thing;
			$elements[] = $listItem;
		}

		if ($elements === []) {
			return null;
		}

		$node = [
			'@type' => 'ItemList',
			'numberOfItems' => count($elements),
			'itemListElement' => $elements,
		];

		if (!empty($data['name'])) {
			$node['name'] = $data['name'];
		}

		return $node;
	}
}
