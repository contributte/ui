<?php declare(strict_types = 1);

namespace Contributte\UI\Bundler;

use Latte\Compiler\Node;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\Extension;

class ViteExtension extends Extension
{

	protected string $file;

	protected string $base;

	protected ?string $nonce;

	public function __construct(string $file, string $base = '/', ?string $nonce = null)
	{
		$this->file = $file;
		$this->base = $base;
		$this->nonce = $nonce;
	}

	/**
	 * @inheritDoc
	 */
	public function getTags(): array
	{
		return [
			'vitejs' => [$this, 'tagVitejs'],
			'vitecss' => [$this, 'tagVitecss'],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getProviders(): array
	{
		return [
			'vite' => new ViteProvider($this->file, $this->base, $this->nonce),
		];
	}

	public function tagVitejs(Tag $tag): Node
	{
		$expression = $tag->parser->parseUnquotedStringOrExpression();

		return new AuxiliaryNode(fn (PrintContext $context) => $context->format(
			<<<'LATTE'
				array_map(function($a) use ($basePath) {
					$nonce = $this->global->vite->getNonce();
					$nonceAttr = $nonce ? ' nonce="' . htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') . '"' : '';
					echo '<script type="module" src="' . $basePath . $a. '"' . $nonceAttr . '></script>';
				}, $this->global->vite->js(%node));
				%line
				echo "\n";
			LATTE,
			$expression,
			$tag->position,
		));
	}

	public function tagVitecss(Tag $tag): Node
	{
		$expression = $tag->parser->parseUnquotedStringOrExpression();

		return new AuxiliaryNode(fn (PrintContext $context) => $context->format(
			<<<'LATTE'
				array_map(function($a) use ($basePath) {
					$nonce = $this->global->vite->getNonce();
					$nonceAttr = $nonce ? ' nonce="' . htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') . '"' : '';
					echo '<link rel="stylesheet" href="' . $basePath . $a. '"' . $nonceAttr . '>';
				}, $this->global->vite->css(%node));
				%line
				echo "\n";
			LATTE,
			$expression,
			$tag->position,
		));
	}

}
