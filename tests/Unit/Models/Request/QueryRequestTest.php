<?php
/**
 * @since     Nov 2024
 * @author    Ondrej Novotny
 */

namespace Qdrant\Tests\Unit\Models\Request;

use PHPUnit\Framework\TestCase;
use Qdrant\Models\Filter\Condition\MatchString;
use Qdrant\Models\Filter\Filter;
use Qdrant\Models\Request\Prefetch;
use Qdrant\Models\Request\QueryRequest;
use Qdrant\Models\SparseVectorStruct;
use Qdrant\Models\VectorStruct;

class QueryRequestTest extends TestCase
{
    public function testQueryRequestWithDenseVector(): void
    {
        $vector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $queryRequest = (new QueryRequest($vector))
            ->setLimit(10);

        $this->assertEquals(
            [
                'query' => [
                    'name' => 'dense',
                    'vector' => [0.01, 0.45, 0.67],
                ],
                'limit' => 10,
            ],
            $queryRequest->toArray()
        );
    }

    public function testQueryRequestWithSparseVector(): void
    {
        $vector = new SparseVectorStruct([1, 42], [0.22, 0.8], 'sparse');

        $queryRequest = (new QueryRequest($vector))
            ->setUsing('sparse')
            ->setLimit(10);

        $this->assertEquals(
            [
                'query' => [
                    'name' => 'sparse',
                    'vector' => [
                        'indices' => [1, 42],
                        'values' => [0.22, 0.8],
                    ],
                ],
                'limit' => 10,
                'using' => 'sparse',
            ],
            $queryRequest->toArray()
        );
    }

    public function testQueryRequestWithFusion(): void
    {
        $sparseVector = new SparseVectorStruct([1, 42], [0.22, 0.8], 'sparse');
        $denseVector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $sparsePrefetch = (new Prefetch($sparseVector))
            ->setUsing('sparse')
            ->setLimit(20);

        $densePrefetch = (new Prefetch($denseVector))
            ->setUsing('dense')
            ->setLimit(20);

        $queryRequest = (new QueryRequest(['fusion' => 'rrf']))
            ->setPrefetch([$sparsePrefetch, $densePrefetch])
            ->setLimit(10);

        $result = $queryRequest->toArray();

        $this->assertArrayHasKey('prefetch', $result);
        $this->assertCount(2, $result['prefetch']);
        $this->assertEquals(['fusion' => 'rrf'], $result['query']);
        $this->assertEquals(10, $result['limit']);
    }

    public function testQueryRequestWithFilter(): void
    {
        $vector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $queryRequest = (new QueryRequest($vector))
            ->setLimit(10)
            ->setFilter(
                (new Filter())->addMust(
                    new MatchString('city', 'Berlin')
                )
            );

        $this->assertEquals(
            [
                'query' => [
                    'name' => 'dense',
                    'vector' => [0.01, 0.45, 0.67],
                ],
                'filter' => [
                    'must' => [
                        ['key' => 'city', 'match' => ['value' => 'Berlin']]
                    ]
                ],
                'limit' => 10,
            ],
            $queryRequest->toArray()
        );
    }

    public function testQueryRequestWithOffsetAndPayload(): void
    {
        $vector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $queryRequest = (new QueryRequest($vector))
            ->setLimit(10)
            ->setOffset(5)
            ->setWithPayload(true)
            ->setWithVector(true);

        $this->assertEquals(
            [
                'query' => [
                    'name' => 'dense',
                    'vector' => [0.01, 0.45, 0.67],
                ],
                'limit' => 10,
                'offset' => 5,
                'with_vector' => true,
                'with_payload' => true,
            ],
            $queryRequest->toArray()
        );
    }

    public function testQueryRequestWithScoreThreshold(): void
    {
        $vector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $queryRequest = (new QueryRequest($vector))
            ->setLimit(10)
            ->setScoreThreshold(0.5);

        $this->assertEquals(
            [
                'query' => [
                    'name' => 'dense',
                    'vector' => [0.01, 0.45, 0.67],
                ],
                'score_threshold' => 0.5,
                'limit' => 10,
            ],
            $queryRequest->toArray()
        );
    }

    public function testQueryRequestWithParams(): void
    {
        $vector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $queryRequest = (new QueryRequest($vector))
            ->setLimit(10)
            ->setParams(['hnsw_ef' => 128, 'exact' => false]);

        $this->assertEquals(
            [
                'query' => [
                    'name' => 'dense',
                    'vector' => [0.01, 0.45, 0.67],
                ],
                'params' => [
                    'hnsw_ef' => 128,
                    'exact' => false,
                ],
                'limit' => 10,
            ],
            $queryRequest->toArray()
        );
    }

    public function testHybridSearchWithPrefetch(): void
    {
        // Create sparse and dense vectors
        $sparseVector = new SparseVectorStruct([1, 42], [0.22, 0.8], 'sparse');
        $denseVector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        // Create prefetch for sparse search
        $sparsePrefetch = (new Prefetch($sparseVector))
            ->setUsing('sparse')
            ->setLimit(20);

        // Create prefetch for dense search
        $densePrefetch = (new Prefetch($denseVector))
            ->setUsing('dense')
            ->setLimit(20);

        // Create hybrid query with RRF fusion
        $queryRequest = (new QueryRequest(['fusion' => 'rrf']))
            ->addPrefetch($sparsePrefetch)
            ->addPrefetch($densePrefetch)
            ->setLimit(10)
            ->setWithPayload(true);

        $result = $queryRequest->toArray();

        // Verify the structure
        $this->assertArrayHasKey('prefetch', $result);
        $this->assertCount(2, $result['prefetch']);
        
        // Verify sparse prefetch
        $this->assertEquals('sparse', $result['prefetch'][0]['using']);
        $this->assertArrayHasKey('query', $result['prefetch'][0]);
        
        // Verify dense prefetch
        $this->assertEquals('dense', $result['prefetch'][1]['using']);
        $this->assertArrayHasKey('query', $result['prefetch'][1]);
        
        // Verify fusion query
        $this->assertEquals(['fusion' => 'rrf'], $result['query']);
        $this->assertEquals(10, $result['limit']);
        $this->assertTrue($result['with_payload']);
    }

    public function testQueryRequestWithSelectedPayloadFields(): void
    {
        $vector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $queryRequest = (new QueryRequest($vector))
            ->setLimit(10)
            ->setWithPayload(['title', 'description']);

        $this->assertEquals(
            [
                'query' => [
                    'name' => 'dense',
                    'vector' => [0.01, 0.45, 0.67],
                ],
                'limit' => 10,
                'with_payload' => ['title', 'description'],
            ],
            $queryRequest->toArray()
        );
    }

    public function testQueryRequestWithSelectedVectorFields(): void
    {
        $vector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $queryRequest = (new QueryRequest($vector))
            ->setLimit(10)
            ->setWithVector(['dense', 'sparse']);

        $this->assertEquals(
            [
                'query' => [
                    'name' => 'dense',
                    'vector' => [0.01, 0.45, 0.67],
                ],
                'limit' => 10,
                'with_vector' => ['dense', 'sparse'],
            ],
            $queryRequest->toArray()
        );
    }
}
