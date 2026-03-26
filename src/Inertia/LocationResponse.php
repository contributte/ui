<?php declare(strict_types = 1);

namespace Contributte\UI\Inertia;

use Nette\Application\Response;
use Nette\Http\IRequest;
use Nette\Http\IResponse;

final class LocationResponse implements Response
{

	public function __construct(
		private readonly string $url,
	)
	{
	}

	public function send(IRequest $httpRequest, IResponse $httpResponse): void
	{
		$httpResponse->setCode(IResponse::S409_Conflict);
		$httpResponse->setHeader('X-Inertia-Location', $this->url);
	}

}
