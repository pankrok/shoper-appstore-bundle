<?php

namespace PanKrok\ShoperAppstoreBundle\Model;

/**
 * Fluent builder for the Shoper REST "filters" parameter.
 *
 *   $api->product->setFilters(
 *       Filter::create()
 *           ->eq('translations.pl_PL.active', true)
 *           ->like('translations.pl_PL.name', 'z%')
 *           ->between('stock.price', 10, 20)
 *           ->in('category_id', [3, 4])
 *   )->get();
 *
 * Produces the documented array syntax, e.g.
 *   ['field' => 'v', 'other' => ['>' => 10, '<' => 20], 'cat' => ['IN' => [3, 4]]]
 *
 * @see https://developers.shoper.pl/developers/api/filters
 */
final class Filter
{
    public const EQ       = '=';
    public const NEQ      = '!=';
    public const GT       = '>';
    public const GTE      = '>=';
    public const LT       = '<';
    public const LTE      = '<=';
    public const LIKE     = 'LIKE';
    public const NOT_LIKE = 'NOT LIKE';
    public const IN       = 'IN';
    public const NOT_IN   = 'NOT IN';

    private const OPERATORS = [
        self::EQ, self::NEQ, self::GT, self::GTE, self::LT, self::LTE,
        self::LIKE, self::NOT_LIKE, self::IN, self::NOT_IN,
    ];

    /** @var array<string, array<string, mixed>> field => [operator => value] */
    private array $conditions = [];

    public static function create(): self
    {
        return new self();
    }

    /**
     * Builds from an already-shaped filters array (scalar or [operator => value] per field).
     */
    public static function fromArray(array $filters): self
    {
        $filter = new self();
        foreach ($filters as $field => $value) {
            if (\is_array($value)) {
                foreach ($value as $operator => $operand) {
                    $filter->where($field, (string) $operator, $operand);
                }
            } else {
                $filter->eq($field, $value);
            }
        }

        return $filter;
    }

    public function eq(string $field, mixed $value): self
    {
        return $this->where($field, self::EQ, $value);
    }

    public function neq(string $field, mixed $value): self
    {
        return $this->where($field, self::NEQ, $value);
    }

    public function gt(string $field, int|float|string $value): self
    {
        return $this->where($field, self::GT, $value);
    }

    public function gte(string $field, int|float|string $value): self
    {
        return $this->where($field, self::GTE, $value);
    }

    public function lt(string $field, int|float|string $value): self
    {
        return $this->where($field, self::LT, $value);
    }

    public function lte(string $field, int|float|string $value): self
    {
        return $this->where($field, self::LTE, $value);
    }

    /**
     * Inclusive range: field >= $min AND field <= $max.
     */
    public function between(string $field, int|float|string $min, int|float|string $max): self
    {
        return $this->gte($field, $min)->lte($field, $max);
    }

    /**
     * "%" is the wildcard, e.g. like('name', 'z%') for names starting with "z".
     */
    public function like(string $field, string $pattern): self
    {
        return $this->where($field, self::LIKE, $pattern);
    }

    public function notLike(string $field, string $pattern): self
    {
        return $this->where($field, self::NOT_LIKE, $pattern);
    }

    public function in(string $field, array $values): self
    {
        return $this->where($field, self::IN, array_values($values));
    }

    public function notIn(string $field, array $values): self
    {
        return $this->where($field, self::NOT_IN, array_values($values));
    }

    /**
     * Adds a raw condition. Operators are case-insensitive; "~" and "!~" are
     * accepted as aliases for LIKE / NOT LIKE.
     *
     * @throws \InvalidArgumentException for an unknown operator
     */
    public function where(string $field, string $operator, mixed $value): self
    {
        $operator = self::normalizeOperator($operator);

        if (\in_array($operator, [self::IN, self::NOT_IN], true) && !\is_array($value)) {
            throw new \InvalidArgumentException(sprintf('Operator %s requires an array value.', $operator));
        }

        $this->conditions[$field][$operator] = $value;

        return $this;
    }

    public function isEmpty(): bool
    {
        return [] === $this->conditions;
    }

    /**
     * Array in the shape expected by ResourceModel::setFilters() / the API.
     * A lone "=" condition is emitted as a scalar, the documented short form.
     */
    public function toArray(): array
    {
        $out = [];
        foreach ($this->conditions as $field => $ops) {
            $out[$field] = (1 === \count($ops) && \array_key_exists(self::EQ, $ops)) ? $ops[self::EQ] : $ops;
        }

        return $out;
    }

    private static function normalizeOperator(string $operator): string
    {
        $normalized = strtoupper(trim($operator));
        $normalized = match ($normalized) {
            '~'  => self::LIKE,
            '!~' => self::NOT_LIKE,
            default => $normalized,
        };

        if (!\in_array($normalized, self::OPERATORS, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown filter operator "%s". Supported: %s, ~, !~.',
                $operator,
                implode(', ', self::OPERATORS)
            ));
        }

        return $normalized;
    }
}
