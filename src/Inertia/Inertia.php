<?php declare(strict_types = 1);

namespace Contributte\UI\Inertia;

use Closure;
use JsonSerializable;
use Nette\Http\IRequest;
use Nette\Http\UrlScript;
use UnitEnum;

final class Inertia
{

	private ?string $resolvedVersion = null;

	private bool $versionResolved = false;

	public function __construct(
		private readonly ?string $version = null,
		private readonly ?string $manifest = null,
		private readonly string $rootId = 'app',
		private readonly string $templateFile = __DIR__ . '/Template/page.latte',
	)
	{
	}

	public function getRootId(): string
	{
		return $this->rootId;
	}

	public function getTemplateFile(): string
	{
		return $this->templateFile;
	}

	public function getVersion(): ?string
	{
		if ($this->versionResolved) {
			return $this->resolvedVersion;
		}

		$this->versionResolved = true;

		if ($this->version !== null) {
			return $this->resolvedVersion = $this->version;
		}

		if ($this->manifest !== null && is_file($this->manifest)) {
			$hash = hash_file('xxh128', $this->manifest);

			if ($hash === false) {
				$md5 = md5_file($this->manifest);
				$hash = $md5 === false ? null : $md5;
			}

			return $this->resolvedVersion = $hash;
		}

		return $this->resolvedVersion = null;
	}

	public function isInertiaRequest(IRequest $httpRequest): bool
	{
		return $httpRequest->getHeader('X-Inertia') === 'true';
	}

	public function isVersionMismatch(IRequest $httpRequest): bool
	{
		$version = $this->getVersion();

		if ($version === null) {
			return false;
		}

		return $httpRequest->getHeader('X-Inertia-Version') !== $version;
	}

	/**
	 * @param array<string, mixed> $props
	 */
	public function createPage(IRequest $httpRequest, string $component, array $props): Page
	{
		$props = $this->filterPartialProps($httpRequest, $component, $props);
		$props = $this->resolveProps($props);

		return new Page(
			component: $component,
			props: $props,
			url: $this->resolveUrl($httpRequest->getUrl()),
			version: $this->getVersion(),
		);
	}

	private function resolveUrl(UrlScript $url): string
	{
		$relativeUrl = $url->getRelativeUrl();

		return $relativeUrl !== '' ? '/' . ltrim($relativeUrl, '/') : '/';
	}

	/**
	 * @param array<string, mixed> $props
	 * @return array<string, mixed>
	 */
	private function filterPartialProps(IRequest $httpRequest, string $component, array $props): array
	{
		if ($httpRequest->getHeader('X-Inertia-Partial-Component') !== $component) {
			return $props;
		}

		$only = $this->parseHeaderList($httpRequest->getHeader('X-Inertia-Partial-Data'));
		$except = $this->parseHeaderList($httpRequest->getHeader('X-Inertia-Partial-Except'));

		if ($only !== []) {
			$filtered = [];

			foreach ($only as $key) {
				if (array_key_exists($key, $props)) {
					$filtered[$key] = $props[$key];
				}
			}

			if (array_key_exists('errors', $props)) {
				$filtered['errors'] = $props['errors'];
			}

			$props = $filtered;
		}

		if ($except !== []) {
			foreach ($except as $key) {
				if ($key === 'errors') {
					continue;
				}

				unset($props[$key]);
			}
		}

		return $props;
	}

	/**
	 * @param array<string, mixed> $props
	 * @return array<string, mixed>
	 */
	private function resolveProps(array $props): array
	{
		foreach ($props as $key => $value) {
			$props[$key] = $this->resolveValue($value);
		}

		return $props;
	}

	private function resolveValue(mixed $value): mixed
	{
		if ($value instanceof Closure) {
			$value = $value();
		}

		if ($value instanceof JsonSerializable) {
			$value = $value->jsonSerialize();
		}

		if ($value instanceof UnitEnum) {
			return $value instanceof \BackedEnum ? $value->value : $value->name;
		}

		if (is_array($value)) {
			foreach ($value as $key => $item) {
				$value[$key] = $this->resolveValue($item);
			}

			return $value;
		}

		return $value;
	}

	/**
	 * @return list<string>
	 */
	private function parseHeaderList(?string $value): array
	{
		if ($value === null || trim($value) === '') {
			return [];
		}

		return array_values(array_filter(array_map(static fn (string $item): string => trim($item), explode(',', $value))));
	}

}
