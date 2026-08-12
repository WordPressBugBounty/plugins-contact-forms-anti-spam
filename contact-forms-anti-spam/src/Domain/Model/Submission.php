<?php

declare(strict_types=1);

namespace Maspik\Domain\Model;

/**
 * A normalized form submission: what every integration adapter produces and
 * the only thing the engine ever sees. Immutable by convention.
 */
final class Submission
{
    /** @var Field[] */
    public $fields;

    /** @var string integration id, e.g. 'cf7', 'elementor' */
    public $source;

    /** @var string human label logged as spam_source, e.g. 'Contact form 7' */
    public $sourceLabel;

    /** @var string */
    public $ip;

    /** @var array<string, string> raw protective fields (honeypot, spam key) */
    public $hidden;

    /** @var string|null */
    public $referrer;

    /**
     * @param Field[]               $fields
     * @param array<string, string> $hidden
     */
    public function __construct(
        array $fields,
        string $source,
        string $sourceLabel,
        string $ip,
        array $hidden = [],
        ?string $referrer = null
    ) {
        $this->fields = $fields;
        $this->source = $source;
        $this->sourceLabel = $sourceLabel;
        $this->ip = $ip;
        $this->hidden = $hidden;
        $this->referrer = $referrer;
    }

    public function hiddenField(string $name): ?string
    {
        return isset($this->hidden[$name]) ? $this->hidden[$name] : null;
    }

    /**
     * @param string $type one of FieldType::ALL
     * @return Field[]
     */
    public function fieldsOfType(string $type): array
    {
        return array_values(array_filter(
            $this->fields,
            static function (Field $f) use ($type): bool {
                return $f->type === $type;
            }
        ));
    }
}
