<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_devtools\local\lint\linters;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use function array_key_exists;

/**
 * The lang dir linter.
 *
 * // phpcs:disable moodle.Commenting.ValidTags.Invalid
 * @phpstan-type LangString string
 * @phpstan-type StringIdentifier string
 * @phpstan-type Locale string
 * @phpstan-type Component string
 * @phpstan-type LangDir string
 *
 * @phpstan-type RawStrings array<StringIdentifier, LangString>
 * @phpstan-type RawLocales array<Locale, RawStrings>
 * @phpstan-type RawComponents array<Component, RawLocales>
 * @phpstan-type RawLangdirs array<LangDir, RawComponents>
 *
 * @phpstan-type NormalisedLocaleStrings array<Locale, LangString>
 * @phpstan-type NormalisedIdentifiers array<StringIdentifier, NormalisedLocaleStrings>
 * @phpstan-type NormalisedComponents array<Component, NormalisedIdentifiers>
 * @phpstan-type NormalisedLangdirs array<LangDir, NormalisedComponents>
 * // phpcs:enable moodle.Commenting.ValidTags.Invalid
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lang extends base {
    #[\Override]
    public static function get_include_patterns(): array {
        return [
            ...parent::get_include_patterns(),
            ...['**/lang/*/*.php'],
        ];
    }

    #[\Override]
    public function lint_file(string $filepath): array {
        return [];
    }

    #[\Override]
    public function lint_directory(string $directorypath): array {
        $rawstringdata = $this->load_strings($directorypath);
        $stringdata = $this->normalise_strings($rawstringdata);

        return [];
    }

    /**
     * Loads all strings in a given directory.
     * @param string $directorypath
     * @return RawLangdirs
     */
    private function load_strings(string $directorypath): array {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directorypath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $locales = [];
        $components = [];

        $langdirdata = [];

        foreach ($iterator as $path) {
            if (!$this->can_lint_file($path)) {
                continue;
            }
            $segments = explode(DIRECTORY_SEPARATOR, $path);
            $component = array_pop($segments);
            $component = str_replace('.php', '', $component);
            $locale = array_pop($segments);
            $langdir = implode(DIRECTORY_SEPARATOR, $segments);

            if (!$component || !$locale || !$langdir) {
                continue;
            }

            if (!array_key_exists($langdir, $langdirdata)) {
                $langdirdata[$langdir] = [];
            }

            if (!array_key_exists($component, $langdirdata[$langdir])) {
                $langdirdata[$langdir][$component] = [];
            }

            if (!array_key_exists($locale, $langdirdata[$langdir][$component])) {
                $langdirdata[$langdir][$component][$locale] = [];
            }
        }

        $manager = get_string_manager();
        foreach ($langdirdata as $langdir => $components) {
            foreach ($components as $component => $locales) {
                foreach ($locales as $locale => $strings) {
                    $langdirdata[$langdir][$component][$locale] = $manager->load_component_strings(
                        $component,
                        $locale,
                        disablecache: true,
                        disablelocal: true
                    );
                }
            }
        }

        return $langdirdata;
    }

    /**
     * Normalises strings for validation.
     * @param RawLangdirs $langdirdata
     * @return NormalisedLangdirs
     */
    private function normalise_strings(array $langdirdata): array {
        $normalised = [];

        foreach ($langdirdata as $langdir => $components) {
            foreach ($components as $component => $locales) {
                foreach ($locales as $locale => $strings) {
                    foreach ($strings as $identifier => $string) {
                        if (!array_key_exists($langdir, $normalised)) {
                            $normalised[$langdir] = [];
                        }

                        if (!array_key_exists($component, $normalised[$langdir])) {
                            $normalised[$langdir][$component] = [];
                        }

                        if (!array_key_exists($identifier, $normalised[$langdir][$component])) {
                            $normalised[$langdir][$component][$identifier] = [];
                        }

                        $normalised[$langdir][$component][$identifier][$locale] = $string;
                    }
                }
            }
        }

        return $normalised;
    }
}
