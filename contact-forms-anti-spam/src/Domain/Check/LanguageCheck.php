<?php

declare(strict_types=1);

namespace Maspik\Domain\Check;

use Maspik\Domain\Model\Field;
use Maspik\Domain\Model\FieldType;
use Maspik\Domain\Model\Violation;

/**
 * Required / forbidden language (Unicode script) rules for textarea fields (Pro).
 * Each rule is a regex fragment like \p{Latin} or \p{Han}, matched with /u.
 */
final class LanguageCheck implements FieldCheck
{
    /** @var string[] at least one must match, if non-empty */
    private $required;

    /** @var string[] none may match */
    private $forbidden;

    /**
     * @param string[] $required
     * @param string[] $forbidden
     */
    public function __construct(array $required, array $forbidden)
    {
        $this->required = $required;
        $this->forbidden = $forbidden;
    }

    public function id(): string
    {
        return 'lang_needed';
    }

    public function supports(string $type): bool
    {
        return $type === FieldType::TEXTAREA;
    }

    public function check(Field $field): ?Violation
    {
        $value = $field->value;

        if ($this->required !== [] && $this->detect($this->required, $value) === '') {
            $list = implode(', ', $this->required);

            return new Violation(
                'lang_needed',
                "Needed language is missing ($list)",
                $list,
                $value,
                $field->name
            );
        }

        if ($this->forbidden !== []) {
            $found = $this->detect($this->forbidden, $value);
            if ($found !== '') {
                return new Violation(
                    'lang_forbidden',
                    "Forbidden language *!$found!* exists",
                    $found,
                    $value,
                    $field->name
                );
            }
        }

        return null;
    }

    /** @param string[] $langs */
    private function detect(array $langs, string $value): string
    {
        if ($value === '') {
            return '';
        }
        foreach ($langs as $lang) {
            if ($lang !== '' && @preg_match("/$lang/u", $value) === 1) {
                return $lang;
            }
        }

        return '';
    }
}
