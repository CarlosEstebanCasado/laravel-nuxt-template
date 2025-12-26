<?php

namespace App\Src\Shared\Domain;

use App\Src\Shared\Domain\Assert as DomainAssert;
use ArrayIterator;
use Countable;
use IteratorAggregate;

abstract class Collection implements Countable, IteratorAggregate
{
    /**
     * @param array<int, object> $items
     */
    public function __construct(protected array $items)
    {
        DomainAssert::arrayOf($this->type(), $items);
    }

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
     * @return array<int, object>
     */
    public function sort(string $field = 'created_at', string $order = 'desc'): array
    {
        usort($this->items, function ($a, $b) use ($field, $order) {
            $result = $a->{$field} <=> $b->{$field};

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

            foreach ($fieldParts as $index => $part) {
                $currentValue = (array_key_last($fieldParts) === $index)
                    ? $currentValue->{$part}()
                    : $currentValue->{$part};
            }

            $groupKey = (string) $currentValue;

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
}
