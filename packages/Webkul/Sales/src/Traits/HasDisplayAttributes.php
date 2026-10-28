<?php

namespace Webkul\Sales\Traits;

trait HasDisplayAttributes
{
    /**
     * Decode the stored additional payload without throwing on null or invalid JSON.
     */
    public function getAdditionalAttribute(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Normalize stored option rows so order views never print a non-scalar value.
     *
     * @return array<int, array{attribute_type: ?string, attribute_name: string, option_label: string}>
     */
    public function displayAttributes(): array
    {
        $attributes = data_get($this->additional, 'attributes', []);

        if (! is_array($attributes)) {
            return [];
        }

        $rows = [];

        foreach ($attributes as $attribute) {
            if (! is_array($attribute)) {
                continue;
            }

            $rows[] = [
                'attribute_type' => is_scalar($attribute['attribute_type'] ?? null)
                    ? (string) $attribute['attribute_type']
                    : null,
                'attribute_name' => $this->stringifyDisplayAttribute($attribute['attribute_name'] ?? ''),
                'option_label' => $this->stringifyDisplayAttribute($attribute['option_label'] ?? ''),
            ];
        }

        return $rows;
    }

    /**
     * Flatten a stored option name or label to a printable string.
     */
    protected function stringifyDisplayAttribute(mixed $value): string
    {
        if (is_array($value)) {
            $value = implode(', ', array_filter($value, 'is_scalar'));
        }

        if (! is_scalar($value)) {
            return '';
        }

        return (string) $value;
    }
}
