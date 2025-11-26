<?php
/**
 * @since     Nov 2024
 * @author    Ondrej Novotny
 */

namespace Qdrant\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use Qdrant\Models\SparseVectorStruct;

class SparseVectorStructTest extends TestCase
{
    public function testSparseVectorStruct(): void
    {
        $vector = new SparseVectorStruct([1, 42], [0.22, 0.8]);

        $this->assertEquals(
            [
                'indices' => [1, 42],
                'values' => [0.22, 0.8],
            ],
            $vector->toArray()
        );

        $this->assertEquals(
            [
                'indices' => [1, 42],
                'values' => [0.22, 0.8],
            ],
            $vector->toSearchArray()
        );
    }

    public function testNamedSparseVectorStruct(): void
    {
        $vector = new SparseVectorStruct([1, 42], [0.22, 0.8], 'sparse');

        $this->assertEquals(
            [
                'sparse' => [
                    'indices' => [1, 42],
                    'values' => [0.22, 0.8],
                ]
            ],
            $vector->toArray()
        );

        $this->assertEquals(
            [
                'name' => 'sparse',
                'vector' => [
                    'indices' => [1, 42],
                    'values' => [0.22, 0.8],
                ],
            ],
            $vector->toSearchArray()
        );
    }

    public function testGetName(): void
    {
        $vector = new SparseVectorStruct([1, 42], [0.22, 0.8], 'sparse');

        $this->assertEquals('sparse', $vector->getName());
    }

    public function testGetNameWithNullValue(): void
    {
        $vector = new SparseVectorStruct([1, 42], [0.22, 0.8]);

        $this->assertNull($vector->getName());
    }

    public function testIsNamed(): void
    {
        $namedVector = new SparseVectorStruct([1, 42], [0.22, 0.8], 'sparse');
        $unnamedVector = new SparseVectorStruct([1, 42], [0.22, 0.8]);

        $this->assertTrue($namedVector->isNamed());
        $this->assertFalse($unnamedVector->isNamed());
    }

    public function testGetIndices(): void
    {
        $vector = new SparseVectorStruct([1, 42], [0.22, 0.8]);

        $this->assertEquals([1, 42], $vector->getIndices());
    }

    public function testGetValues(): void
    {
        $vector = new SparseVectorStruct([1, 42], [0.22, 0.8]);

        $this->assertEquals([0.22, 0.8], $vector->getValues());
    }
}
