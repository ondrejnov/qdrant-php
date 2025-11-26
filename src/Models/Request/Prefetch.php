<?php
/**
 * Prefetch
 *
 * @since     Nov 2024
 * @author    Ondrej Novotny
 */

namespace Qdrant\Models\Request;

use Qdrant\Models\Filter\Filter;
use Qdrant\Models\VectorStructInterface;

class Prefetch
{
    protected ?Filter $filter = null;

    protected array $params = [];

    protected ?int $limit = null;

    protected ?string $using = null;

    protected ?float $scoreThreshold = null;

    /** @var Prefetch[]|null */
    protected ?array $prefetch = null;

    /**
     * @param VectorStructInterface|array|null $query Query vector, sparse vector, or null for fusion
     */
    public function __construct(protected VectorStructInterface|array|null $query = null)
    {
    }

    public function setFilter(Filter $filter): static
    {
        $this->filter = $filter;

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

    public function setUsing(string $using): static
    {
        $this->using = $using;

        return $this;
    }

    public function setScoreThreshold(float $scoreThreshold): static
    {
        $this->scoreThreshold = $scoreThreshold;

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

        if ($this->params) {
            $body['params'] = $this->params;
        }

        if ($this->limit !== null) {
            $body['limit'] = $this->limit;
        }

        if ($this->using !== null) {
            $body['using'] = $this->using;
        }

        if ($this->scoreThreshold !== null) {
            $body['score_threshold'] = $this->scoreThreshold;
        }

        if ($this->prefetch !== null && count($this->prefetch) > 0) {
            $body['prefetch'] = array_map(fn(Prefetch $p) => $p->toArray(), $this->prefetch);
        }

        return $body;
    }
}
