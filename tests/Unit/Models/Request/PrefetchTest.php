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
use Qdrant\Models\SparseVectorStruct;
use Qdrant\Models\VectorStruct;

class PrefetchTest extends TestCase
{
    public function testPrefetchWithDenseVector(): void
    {
        $vector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $prefetch = (new Prefetch($vector))
            ->setUsing('dense')
            ->setLimit(20);

        $this->assertEquals(
            [
                'query' => [
                    'name' => 'dense',
                    'vector' => [0.01, 0.45, 0.67],
                ],
                'limit' => 20,
                'using' => 'dense',
            ],
            $prefetch->toArray()
        );
    }

    public function testPrefetchWithSparseVector(): void
    {
        $vector = new SparseVectorStruct([1, 42], [0.22, 0.8], 'sparse');

        $prefetch = (new Prefetch($vector))
            ->setUsing('sparse')
            ->setLimit(20);

        $this->assertEquals(
            [
                'query' => [
                    'name' => 'sparse',
                    'vector' => [
                        'indices' => [1, 42],
                        'values' => [0.22, 0.8],
                    ],
                ],
                'limit' => 20,
                'using' => 'sparse',
            ],
            $prefetch->toArray()
        );
    }

    public function testPrefetchWithFilter(): void
    {
        $vector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $prefetch = (new Prefetch($vector))
            ->setUsing('dense')
            ->setLimit(20)
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
                'limit' => 20,
                'using' => 'dense',
            ],
            $prefetch->toArray()
        );
    }

    public function testPrefetchWithParams(): void
    {
        $vector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $prefetch = (new Prefetch($vector))
            ->setUsing('dense')
            ->setLimit(20)
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
                'limit' => 20,
                'using' => 'dense',
            ],
            $prefetch->toArray()
        );
    }

    public function testPrefetchWithScoreThreshold(): void
    {
        $vector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $prefetch = (new Prefetch($vector))
            ->setUsing('dense')
            ->setLimit(20)
            ->setScoreThreshold(0.5);

        $this->assertEquals(
            [
                'query' => [
                    'name' => 'dense',
                    'vector' => [0.01, 0.45, 0.67],
                ],
                'limit' => 20,
                'using' => 'dense',
                'score_threshold' => 0.5,
            ],
            $prefetch->toArray()
        );
    }

    public function testNestedPrefetch(): void
    {
        $sparseVector = new SparseVectorStruct([1, 42], [0.22, 0.8], 'sparse');
        $denseVector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $sparsePrefetch = (new Prefetch($sparseVector))
            ->setUsing('sparse')
            ->setLimit(20);

        $densePrefetch = (new Prefetch($denseVector))
            ->setUsing('dense')
            ->setLimit(20);

        $prefetch = (new Prefetch())
            ->setPrefetch([$sparsePrefetch, $densePrefetch])
            ->setLimit(10);

        $result = $prefetch->toArray();

        $this->assertArrayHasKey('prefetch', $result);
        $this->assertCount(2, $result['prefetch']);
        $this->assertEquals(10, $result['limit']);
    }

    public function testAddPrefetch(): void
    {
        $sparseVector = new SparseVectorStruct([1, 42], [0.22, 0.8], 'sparse');
        $denseVector = new VectorStruct([0.01, 0.45, 0.67], 'dense');

        $prefetch = (new Prefetch())
            ->addPrefetch(
                (new Prefetch($sparseVector))
                    ->setUsing('sparse')
                    ->setLimit(20)
            )
            ->addPrefetch(
                (new Prefetch($denseVector))
                    ->setUsing('dense')
                    ->setLimit(20)
            )
            ->setLimit(10);

        $result = $prefetch->toArray();

        $this->assertArrayHasKey('prefetch', $result);
        $this->assertCount(2, $result['prefetch']);
    }
}
