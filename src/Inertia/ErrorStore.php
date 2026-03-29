<?php declare(strict_types = 1);

namespace Contributte\UI\Inertia;

use Nette\Http\Session;

final class ErrorStore
{

	public function __construct(
		private readonly Session $session,
		private readonly string $section = 'contributte.ui.inertia',
	)
	{
	}

	/**
	 * @param array<string, mixed> $errors
	 */
	public function flash(array $errors, ?string $bag = null): void
	{
		$bag ??= 'default';

		$section = $this->session->getSection($this->section);
		/** @var array<string, array<string, mixed>> $current */
		$current = $section->get('errors') ?? [];
		$current[$bag] = $errors;
		$section->set('errors', $current, '30 seconds');
	}

	/**
	 * @return array<string, mixed>
	 */
	public function pull(?string $bag = null): array
	{
		$section = $this->session->getSection($this->section);
		/** @var array<string, array<string, mixed>> $errors */
		$errors = $section->get('errors') ?? [];
		$section->remove('errors');

		if ($errors === []) {
			return [];
		}

		if ($bag !== null) {
			if (isset($errors['default'])) {
				return [$bag => $errors['default']];
			}

			return isset($errors[$bag]) ? [$bag => $errors[$bag]] : [];
		}

		if (isset($errors['default']) && count($errors) === 1) {
			return $errors['default'];
		}

		return $errors;
	}

}
