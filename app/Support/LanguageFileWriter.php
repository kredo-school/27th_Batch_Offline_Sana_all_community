<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

final class LanguageFileWriter
{
    public function __construct(private readonly ?string $directory = null) {}

    /**
     * @return array<string, string>
     */
    public function read(string $locale): array
    {
        $path = $this->path($locale);
        if (! File::exists($path)) {
            return [];
        }

        $values = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($values)) {
            throw new \RuntimeException("Language file [{$locale}.json] must contain a JSON object.");
        }

        return array_map(static fn ($value): string => (string) $value, $values);
    }

    /**
     * @param  array<string, string>  $values
     */
    public function write(string $locale, array $values): void
    {
        $written = File::put(
            $this->path($locale),
            json_encode($values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
        );

        if ($written === false) {
            throw new \RuntimeException("Unable to write language file [{$locale}.json].");
        }
    }

    /**
     * Adds $key to every supported locale file that does not already contain it.
     * Existing translations are never overwritten.
     *
     * @param  array<string, string>  $translations  Locale code => translated text
     */
    public function addMissingKey(string $key, array $translations): void
    {
        $directory = $this->directory ?? lang_path();

        foreach (array_keys(config('app.supported_locales')) as $locale) {
            $path = $directory.DIRECTORY_SEPARATOR.$locale.'.json';
            $values = File::exists($path) ? json_decode(File::get($path), true) : [];

            if (! is_array($values) || array_key_exists($key, $values) || ! isset($translations[$locale])) {
                continue;
            }

            $values[$key] = $translations[$locale];

            File::put(
                $path,
                json_encode($values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n",
            );
        }
    }

    private function path(string $locale): string
    {
        return ($this->directory ?? lang_path()).DIRECTORY_SEPARATOR.$locale.'.json';
    }
}
