<?php
/**
 * QueryRequest
 *
 * @since     Nov 2024
 * @author    Ondrej Novotny
 */

namespace Qdrant\Models\Request;

use Qdrant\Models\Filter\Filter;
use Qdrant\Models\Traits\ProtectedPropertyAccessor;
use Qdrant\Models\VectorStructInterface;

class QueryRequest implements RequestModel
{
    use ProtectedPropertyAccessor;

    protected ?Filter $filter = null;

    protected array $params = [];

    protected ?int $limit = null;

    protected ?int $offset = null;

    protected bool|array|null $withVector = null;

    protected bool|array|null $withPayload = null;

    protected ?float $scoreThreshold = null;

    protected ?string $using = null;

    /** @var Prefetch[]|null */
    protected ?array $prefetch = null;

    /**
     * @param VectorStructInterface|array|null $query Query can be:
     *   - VectorStructInterface for dense/sparse vector search
     *   - array like ['fusion' => 'rrf'] for fusion queries
     *   - null when using prefetch only
     */
    public function __construct(protected VectorStructInterface|array|null $query = null)
    {
    }

    public function setFilter(Filter $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    public function setScoreThreshold(float $scoreThreshold): static
    {
        $this->scoreThreshold = $scoreThreshold;

        return $this;
    }

    public function setParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function setLimit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOffset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    public function setWithPayload(bool|array|null $withPayload): static
    {
        $this->withPayload = $withPayload;

        return $this;
    }

    public function setWithVector(bool|array|null $withVector): static
    {
        $this->withVector = $withVector;

        return $this;
    }

    public function setUsing(string $using): static
    {
        $this->using = $using;

        return $this;
    }

    /**
     * @param Prefetch[] $prefetch
     */
    public function setPrefetch(array $prefetch): static
    {
        $this->prefetch = $prefetch;

        return $this;
    }

    public function addPrefetch(Prefetch $prefetch): static
    {
        $this->prefetch[] = $prefetch;

        return $this;
    }

    public function toArray(): array
    {
        $body = [];

        if ($this->prefetch !== null && count($this->prefetch) > 0) {
            $body['prefetch'] = array_map(fn(Prefetch $p) => $p->toArray(), $this->prefetch);
        }

        if ($this->query !== null) {
            if ($this->query instanceof VectorStructInterface) {
                $body['query'] = $this->query->toSearchArray($this->using ?? $this->query->getName());
            } else {
                $body['query'] = $this->query;
            }
        }

        if ($this->filter !== null && $this->filter->toArray()) {
            $body['filter'] = $this->filter->toArray();
        }

        if ($this->scoreThreshold !== null) {
            $body['score_threshold'] = $this->scoreThreshold;
        }

        if ($this->params) {
            $body['params'] = $this->params;
        }

        if ($this->limit !== null) {
            $body['limit'] = $this->limit;
        }

        if ($this->offset !== null) {
            $body['offset'] = $this->offset;
        }

        if ($this->withVector !== null) {
            $body['with_vector'] = $this->withVector;
        }

        if ($this->withPayload !== null) {
            $body['with_payload'] = $this->withPayload;
        }

        if ($this->using !== null) {
            $body['using'] = $this->using;
        }

        return $body;
    }
}
