<?php declare(strict_types = 1);

namespace Tests\Fixtures\Inertia;

use Nette\Http\IResponse;

final class TestHttpResponse implements IResponse
{

	public string $cookieDomain = '';

	public string $cookiePath = '/';

	public bool $cookieSecure = false;

	private int $code = self::S200_OK;

	/** @var array<string, string> */
	private array $headers = [];

	/** @var array<string, list<string>> */
	private array $addedHeaders = [];

	public function setCode(int $code, ?string $reason = null): static
	{
		$this->code = $code;

		return $this;
	}

	public function getCode(): int
	{
		return $this->code;
	}

	public function setHeader(string $name, ?string $value): static
	{
		if ($value === null) {
			unset($this->headers[$name]);

			return $this;
		}

		$this->headers[$name] = $value;

		return $this;
	}

	public function addHeader(string $name, string $value): static
	{
		$this->addedHeaders[$name] ??= [];
		$this->addedHeaders[$name][] = $value;

		return $this;
	}

	public function deleteHeader(string $name): static
	{
		unset($this->headers[$name], $this->addedHeaders[$name]);

		return $this;
	}

	public function getHeader(string $header): ?string
	{
		return $this->headers[$header] ?? null;
	}

	/**
	 * @return array<string, string>
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	public function setContentType(string $type, ?string $charset = null): static
	{
		return $this->setHeader('Content-Type', $type . ($charset !== null ? '; charset=' . $charset : ''));
	}

	public function redirect(string $url, int $code = self::S302_Found): void
	{
		$this->setCode($code);
		$this->setHeader('Location', $url);
	}

	public function setExpiration(?string $expire): static
	{
		return $this;
	}

	public function isSent(): bool
	{
		return false;
	}

	public function setCookie(string $name, string $value, mixed $expire, ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httpOnly = null, ?string $sameSite = null): static
	{
		return $this;
	}

	public function deleteCookie(string $name, ?string $path = null, ?string $domain = null, ?bool $secure = null): static
	{
		return $this;
	}

	public function getCookie(string $name): ?string
	{
		return null;
	}

	public function sendAsFile(string $fileName): static
	{
		return $this;
	}

	public function write(string $s): void
	{
		// noop
	}

	public function flush(): void
	{
		// noop
	}

}
