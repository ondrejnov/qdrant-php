<?php
/**
 * SparseVectorStruct
 *
 * @since     Nov 2024
 * @author    Ondrej Novotny
 */

namespace Qdrant\Models;

use Qdrant\Models\Traits\ProtectedPropertyAccessor;

class SparseVectorStruct implements VectorStructInterface
{
    use ProtectedPropertyAccessor;

    /**
     * @param array $indices Array of indices where non-zero values exist
     * @param array $values Array of values at corresponding indices
     * @param string|null $name Optional name for named sparse vectors
     */
    public function __construct(
        protected array $indices,
        protected array $values,
        protected ?string $name = null
    ) {
    }

    public function isNamed(): bool
    {
        return $this->name !== null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getIndices(): array
    {
        return $this->indices;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function toSearchArray(?string $name = null): array
    {
        $vectorData = [
            'indices' => $this->indices,
            'values' => $this->values,
        ];

        if ($this->isNamed()) {
            return [
                'name' => $this->name,
                'vector' => $vectorData,
            ];
        }

        return $vectorData;
    }

    public function toArray(): array
    {
        $vectorData = [
            'indices' => $this->indices,
            'values' => $this->values,
        ];

        if ($this->isNamed()) {
            return [
                $this->name => $vectorData
            ];
        }

        return $vectorData;
    }
}
