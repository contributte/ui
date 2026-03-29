<?php declare(strict_types = 1);

namespace Contributte\UI\Inertia;

use JsonSerializable;

final class Page implements JsonSerializable
{

	/**
	 * @param array<string, mixed> $props
	 */
	public function __construct(
		public readonly string $component,
		public readonly array $props,
		public readonly string $url,
		public readonly ?string $version,
		public readonly bool $clearHistory = false,
		public readonly bool $encryptHistory = false,
	)
	{
	}

	/**
	 * @return array{component: string, props: array<string, mixed>, url: string, version: string|null, clearHistory: bool, encryptHistory: bool}
	 */
	public function toArray(): array
	{
		return [
			'component' => $this->component,
			'props' => $this->props,
			'url' => $this->url,
			'version' => $this->version,
			'clearHistory' => $this->clearHistory,
			'encryptHistory' => $this->encryptHistory,
		];
	}

	/**
	 * @return array{component: string, props: array<string, mixed>, url: string, version: string|null, clearHistory: bool, encryptHistory: bool}
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

}
