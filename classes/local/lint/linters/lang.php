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

use local_devtools\local\lint\issue;
use local_devtools\local\lint\severity;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use function array_key_exists;
use function array_merge;
use function in_array;
use function strlen;

/**
 * The lang dir linter.
 *
 * // phpcs:disable moodle.Commenting.ValidTags.Invalid
 * @phpstan-import-type FileWithIssues from base
 *
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
 * @phpstan-type NormalisedComponentData array{locales:Locale[],identifiers:NormalisedIdentifiers}
 * @phpstan-type NormalisedComponents array<Component, NormalisedComponentData>
 * @phpstan-type NormalisedLangdirs array<LangDir, NormalisedComponents>
 * // phpcs:enable moodle.Commenting.ValidTags.Invalid
 *
 * @package   local_devtools
 * @copyright 2026 Felix Yeung
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lang extends base {
    #[\Override]
    public static function get_description(): ?string {
        return 'executes language string consistency checking against lang/*/*.php files';
    }

    #[\Override]
    public static function get_include_patterns(): array {
        return [
            ...parent::get_include_patterns(),
            ...['**/lang/*/*.php'],
        ];
    }

    #[\Override]
    public function lint_file(string $filepath): array {
        $segments = self::split_lang_filepath($filepath);
        if (!$segments) {
            return [];
        }

        // Let's be really cheeky and just filter the lint_directory results for the current file.
        [$langdir] = $segments;
        $results = $this->lint_directory($langdir);
        $results = array_filter(
            $results,
            fn(/** @var FileWithIssues $result */ $result) => $result['file'] === $filepath
        );
        return $results;
    }

    #[\Override]
    public function lint_directory(string $directorypath): array {
        $nearestlangdir = self::find_nearest_langdir_up($directorypath);
        $rawstringdata = $this->load_strings($nearestlangdir);
        $stringdata = $this->normalise_strings($rawstringdata);

        $results = $this->validate($stringdata);
        $results = array_filter(
            $results,
            fn(/** @var FileWithIssues $result */ $result) => str_starts_with($result['file'], $directorypath)
        );
        return $results;
    }

    /**
     * Walks up the directory tree and find the nearest lang directory.
     * @param string $directorypath
     * @return string
     */
    private static function find_nearest_langdir_up(string $directorypath): string {
        global $CFG;

        $root = realpath($CFG->root);
        if ($root === false) {
            return $directorypath;
        }

        if (!str_starts_with($directorypath, $root)) {
            return $directorypath;
        }

        // Prevent spilling out of the Moodle root.
        $relativepath = substr($directorypath, strlen($root));
        $segments = explode(DIRECTORY_SEPARATOR, $relativepath);

        while ($segments) {
            $currdir = array_pop($segments);
            if ($currdir !== 'lang') {
                continue;
            }

            $segments[] = 'lang';
            break;
        }

        if (!$segments) {
            return $directorypath;
        }

        $directorypath = $root . implode(DIRECTORY_SEPARATOR, $segments);

        return $directorypath;
    }

    /**
     * Loads all strings in a given directory.
     *
     * Loading strings via the manager will fallback on undefined strings,
     * so we add an option to disable and load directly from file.
     *
     * @param string $directorypath
     * @param bool $usestringmanager
     * @return RawLangdirs
     */
    private function load_strings(string $directorypath, bool $usestringmanager = false): array {
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

            $segments = self::split_lang_filepath($path);
            if (!$segments) {
                continue;
            }

            [$langdir, $locale, $component] = $segments;

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

        $manager = $usestringmanager ? get_string_manager() : null;
        foreach ($langdirdata as $langdir => $components) {
            foreach ($components as $component => $locales) {
                foreach ($locales as $locale => $strings) {
                    $langdirdata[$langdir][$component][$locale] = $manager
                        ? $manager->load_component_strings($component, $locale)
                        : self::load_component_strings(
                            $this->compose_lang_filepath($langdir, $component, $locale)
                        );
                }
            }
        }

        return $langdirdata;
    }

    /**
     * Loads language file strings.
     * @return RawStrings
     */
    private static function load_component_strings(string $filepath): array {
        try {
            $string = [];
            include($filepath);
            return $string;
        } catch (\Throwable $th) {
            return [];
        }
    }

    /**
     * Normalises strings for validation.
     * @param RawLangdirs $langdirdata
     * @return NormalisedLangdirs
     */
    private function normalise_strings(array $langdirdata): array {
        /** @var NormalisedLangdirs $normalised */
        $normalised = [];

        foreach ($langdirdata as $langdir => $components) {
            foreach ($components as $component => $locales) {
                foreach ($locales as $locale => $strings) {
                    foreach ($strings as $identifier => $string) {
                        if (!array_key_exists($langdir, $normalised)) {
                            $normalised[$langdir] = [];
                        }

                        if (!array_key_exists($component, $normalised[$langdir])) {
                            /** @var NormalisedComponentData $componentdata */
                            $componentdata = [
                                'locales' => array_keys($locales),
                                'identifiers' => [],
                            ];
                            $normalised[$langdir][$component] = $componentdata;
                        }

                        if (!array_key_exists($identifier, $normalised[$langdir][$component]['identifiers'])) {
                            $normalised[$langdir][$component]['identifiers'][$identifier] = [];
                        }

                        $normalised[$langdir][$component]['identifiers'][$identifier][$locale] = $string;
                    }
                }
            }
        }

        return $normalised;
    }

    /**
     * Validates all strings.
     * @param NormalisedLangdirs $langdirdata
     * @return FileWithIssues[]
     */
    private function validate(array $langdirdata): array {
        $results = [];

        foreach ($langdirdata as $langdir => $components) {
            $results[] = $this->validate_components($langdir, $components);
        }

        return array_merge(...$results);
    }

    /**
     * Validates all strings.
     * @param LangDir $langdir
     * @param NormalisedComponents $components
     * @return FileWithIssues[]
     */
    private function validate_components(string $langdir, array $components): array {
        $results = [];

        foreach ($components as $component => $componentdata) {
            $results[] = $this->validate_component($langdir, $component, $componentdata);
        }

        return array_merge(...$results);
    }

    /**
     * Validates all strings.
     * @param LangDir $langdir
     * @param Component $component
     * @param NormalisedComponentData $componentdata
     * @return FileWithIssues[]
     */
    private function validate_component(string $langdir, string $component, array $componentdata): array {
        $results = [];
        ['locales' => $locales, 'identifiers' => $identifiers] = $componentdata;

        $englishlocaleid = 'en';
        $englishlangfilepath = self::compose_lang_filepath($langdir, $component, $englishlocaleid);

        if (!in_array($englishlocaleid, $locales)) {
            $results[] = self::single_file_issue(
                $englishlangfilepath,
                "Missing required '$englishlocaleid' locale",
                "linting-requires-$englishlocaleid-locale"
            );
            return $results;
        }

        foreach ($identifiers as $identifier => $localesdata) {
            $identifierlocales = array_keys($localesdata);

            // Validate that all strings have the 'en' locale.
            if (!in_array($englishlocaleid, $identifierlocales)) {
                $results[] = self::single_file_issue(
                    $englishlangfilepath,
                    "Identifier '$identifier' is not present in the '$englishlocaleid' locale",
                    'identifier-safely-missing',
                    severity: severity::warning
                );
                continue;
            }

            // Validate that if a string has the 'en' locale, it should also have all other locales.
            $missinglocales = array_diff($locales, $identifierlocales);
            foreach ($missinglocales as $missinglocale) {
                $results[] = self::single_file_issue(
                    self::compose_lang_filepath($langdir, $component, $missinglocale),
                    "Identifier '$identifier' missing from '$missinglocale' locale",
                    'identifier-missing'
                );
            }

            // Validate that there are no extra locales.
            // Validate that if a string has the 'en' locale, it should also have all other locales.
            $extralocales = array_diff($identifierlocales, $locales);
            foreach ($extralocales as $extralocale) {
                $results[] = self::single_file_issue(
                    self::compose_lang_filepath($langdir, $component, $extralocale),
                    "Identifier '$identifier' has extra '$extralocale' locale",
                    'identifier-extra'
                );
            }

            $englishstring = $localesdata[$englishlocaleid];
            $requiredplaceholders = self::extract_placeholders($englishstring);

            foreach ($localesdata as $locale => $string) {
                $placeholders = self::extract_placeholders($string);
                $missingplaceholders = array_diff($requiredplaceholders, $placeholders);
                if ($missingplaceholders) {
                    $placeholdersmsg = self::placeholders_to_string($missingplaceholders);
                    $results[] = self::single_file_issue(
                        self::compose_lang_filepath($langdir, $component, $locale),
                        "Identifier '$identifier' is missing placeholders $placeholdersmsg in the '$locale' locale",
                        'identifier-placeholders-missing'
                    );
                }

                $extraplaceholders = array_diff($placeholders, $requiredplaceholders);
                if ($extraplaceholders) {
                    $placeholdersmsg = self::placeholders_to_string($extraplaceholders);
                    $results[] = self::single_file_issue(
                        self::compose_lang_filepath($langdir, $component, $locale),
                        "Identifier '$identifier' has extra placeholders $placeholdersmsg in the '$locale' locale",
                        'identifier-placeholders-extra'
                    );
                }

                continue;
            }

            continue;
        }

        return $results;
    }

    /**
     * Splits the lang file into the /lang dir, locale code, and component name.
     * Can use {@see self::split_lang_filepath} to reconstruct the file path from parts.
     * @param string $filepath
     * @return array{string, string, string} - lang dir, locale, component
     */
    private static function split_lang_filepath(string $filepath): ?array {
        $segments = explode(DIRECTORY_SEPARATOR, $filepath);
        $component = array_pop($segments);
        $component = str_replace('.php', '', $component);
        $locale = array_pop($segments);
        $langdir = implode(DIRECTORY_SEPARATOR, $segments);

        if (!$langdir || !$locale || !$component) {
            return null;
        }

        return [$langdir, $locale, $component];
    }

    /**
     * Utility function to recreate the language file path.
     * Can use {@see self::split_lang_filepath} to split the file path back into parts.
     * @param string $langdir
     * @param string $component
     * @param string $locale
     * @return string
     */
    private static function compose_lang_filepath(string $langdir, string $component, string $locale): string {
        return implode(DIRECTORY_SEPARATOR, [$langdir, $locale, "$component.php"]);
    }

    /**
     * Helper function to create a simple issue.
     * @param string $message
     * @param string|null $rule
     * @param string $source
     * @param severity $severity
     * @return FileWithIssues
     */
    private static function single_file_issue(
        string $path,
        string $message,
        ?string $rule,
        string $source = 'lang',
        severity $severity = severity::error,
    ): array {
        return [
            'file' => $path,
            'issues' => [
                issue::simple($message, $rule, $source, $severity),
            ],
        ];
    }

    /**
     * Extracts {$a} / {$a->key} placeholders from a given string.
     * @param string $string
     * @return string[]
     */
    private static function extract_placeholders(string $string): array {
        static $regex = '/{\$a(?:->\w+)?}/';
        $success = preg_match_all($regex, $string, $matches);
        if ($success === false) {
            return [];
        }

        return $matches[0];
    }

    /**
     * Converts a set of placeholders into string suitable to use in the issue message.
     * @param string[] $placeholders
     * @return string
     */
    private static function placeholders_to_string(array $placeholders): string {
        $placeholders = array_map(fn($placeholder) => "`$placeholder`", $placeholders);
        $string = implode(',', $placeholders);
        return "($string)";
    }
}
