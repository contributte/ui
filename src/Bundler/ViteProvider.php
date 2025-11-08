<?php declare(strict_types = 1);

namespace Contributte\UI\Bundler;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;

class ViteProvider
{

	private const TYPE_JS = 'js';
	private const TYPE_CSS = 'css';

	protected string $file;

	protected string $base;

	protected ?string $nonce;

	/** @var array<string, array{file: string, src: string, isEntry: bool, css: string[]}> */
	protected array $manifest = [];

	public function __construct(string $file, string $base = '/', ?string $nonce = null)
	{
		$this->file = $file;
		$this->base = $base;
		$this->nonce = $nonce;
	}

	public function getNonce(): ?string
	{
		return $this->nonce;
	}

	/**
	 * @return string[]
	 */
	public function js(string $asset): array
	{
		return $this->vite($asset, self::TYPE_JS);
	}

	/**
	 * @return string[]
	 */
	public function css(string $asset): array
	{
		return $this->vite($asset, self::TYPE_CSS);
	}

	/**
	 * @return string[]
	 */
	protected function vite(string $asset, string $type): array
	{
		$manifest = $this->load();

		if (!isset($manifest[$asset])) {
			throw new \LogicException(sprintf('Asset "%s" not found', $asset));
		}

		if ($type === self::TYPE_JS) {
			return [$this->base . '/' . $manifest[$asset]['file']];
		}

		if ($type === self::TYPE_CSS) {
			return array_map(fn ($css) => $this->base . '/' . $css, $manifest[$asset]['css']);
		}

		throw new \LogicException(sprintf('Invalid type "%s" ', $type));
	}

	/**
	 * @return array<string, array{file: string, src: string, isEntry: bool, css: string[]}>
	 */
	protected function load(): array
	{
		if ($this->manifest === []) {
			/** @var array<string, array{file: string, src: string, isEntry: bool, css: string[]}> $manifest */
			$manifest = Json::decode(FileSystem::read($this->file), JSON_OBJECT_AS_ARRAY);

			$this->manifest = $manifest;
		}

		return $this->manifest;
	}

}
