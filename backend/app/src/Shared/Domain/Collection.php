<?php
declare(strict_types=1);

namespace App\Src\Shared\Domain;

use App\Src\Shared\Domain\Assert as DomainAssert;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use RuntimeException;

/**
 * @implements IteratorAggregate<int, object>
 */
abstract class Collection implements Countable, IteratorAggregate
{
    /**
     * @param array<int, object> $items
     */
    public function __construct(protected array $items)
    {
        DomainAssert::arrayOf($this->type(), $items);
    }

    /**
     * @return ArrayIterator<int, object>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items());
    }

    public function count(): int
    {
        return count($this->items());
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function isNotEmpty(): bool
    {
        return $this->count() > 0;
    }

    /**
     * @return array<int, mixed>
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->items);
    }

    /**
     * @return array<int, object>
     */
    public function each(callable $callback): array
    {
        array_walk($this->items, $callback);

        return $this->items;
    }

    /**
     * @return array<int, object>
     */
    public function filterArray(callable $callback): array
    {
        return array_values(array_filter($this->items, $callback));
    }

    /**
     * Sort items by a given field using getters (preferred) or public properties.
     *
     * @return array<int, object>
     */
    public function sort(string $field = 'created_at', string $order = 'desc'): array
    {
        usort($this->items, function ($a, $b) use ($field, $order) {
            $aValue = $this->extractSortableValue($a, $field);
            $bValue = $this->extractSortableValue($b, $field);

            $result = $aValue <=> $bValue;

            return $order === 'asc' ? $result : -$result;
        });

        return $this->items;
    }

    /**
     * @return array<string, array<int, object>>
     */
    public function groupBy(string $field): array
    {
        $fieldParts = explode('.', $field);
        $groupedResult = [];

        foreach ($this->items() as $item) {
            $currentValue = $item;

            foreach ($fieldParts as $part) {
                $currentValue = $this->descendValue($currentValue, $part);
            }

            $groupKey = $this->stringifyGroupKey($currentValue);

            if (! isset($groupedResult[$groupKey])) {
                $groupedResult[$groupKey] = [];
            }

            $groupedResult[$groupKey][] = $item;
        }

        return $groupedResult;
    }

    /**
     * @return array<int, object>
     */
    public function items(): array
    {
        return $this->items;
    }

    abstract protected function type(): string;

    private function extractSortableValue(object $item, string $field): mixed
    {
        $methodCandidates = $this->methodCandidates($field);

        foreach ($methodCandidates as $method) {
            if (method_exists($item, $method)) {
                return $item->{$method}();
            }
        }

        if (property_exists($item, $field)) {
            return $item->{$field};
        }

        throw new RuntimeException(sprintf(
            'Unable to sort collection: field "%s" is not accessible on %s',
            $field,
            $item::class
        ));
    }

    /**
     * @return array<int, string>
     */
    private function methodCandidates(string $field): array
    {
        $candidates = [$field];

        $camel = $this->toCamelCase($field);
        if ($camel !== $field) {
            $candidates[] = $camel;
        }

        $studly = ucfirst($camel);
        $candidates[] = 'get'.$studly;

        return array_unique($candidates);
    }

    private function toCamelCase(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', $value);
        $value = ucwords(strtolower($value));

        $value = str_replace(' ', '', $value);

        return lcfirst($value);
    }

    private function descendValue(mixed $item, string $fieldPart): mixed
    {
        if (! is_object($item)) {
            throw new RuntimeException(sprintf(
                'Unable to access field "%s" on value of type %s when grouping collection',
                $fieldPart,
                get_debug_type($item)
            ));
        }

        $methodCandidates = $this->methodCandidates($fieldPart);

        foreach ($methodCandidates as $method) {
            if (method_exists($item, $method)) {
                return $item->{$method}();
            }
        }

        if (property_exists($item, $fieldPart)) {
            return $item->{$fieldPart};
        }

        throw new RuntimeException(sprintf(
            'Unable to access field "%s" on %s when grouping collection',
            $fieldPart,
            $item::class
        ));
    }

    private function stringifyGroupKey(mixed $value): string
    {
        if (is_scalar($value) || $value === null) {
            return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        throw new RuntimeException(sprintf(
            'Unable to convert group key to string. Received %s',
            get_debug_type($value)
        ));
    }
}
