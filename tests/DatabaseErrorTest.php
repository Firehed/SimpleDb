<?php
declare(strict_types=1);

namespace Firehed\SimpleDb;

/**
 * @coversDefaultClass Firehed\SimpleDb\DatabaseError
 * @covers ::<protected>
 * @covers ::<private>
 */
class DatabaseErrorTest extends \PHPUnit\Framework\TestCase
{
    /** @covers ::__construct */
    public function testConstruct()
    {
        $e = new DatabaseError();
        $this->assertInstanceOf(DatabaseError::class, $e);
    }

    /** @covers ::__construct */
    public function testConstructWithMessage()
    {
        $e = new DatabaseError('Some message');
        $this->assertInstanceOf(DatabaseError::class, $e);
        $this->assertSame('Some message', $e->getMessage());
    }

    /** @covers ::__construct */
    public function testConstructWithMessageAndNumericCode()
    {
        $e = new DatabaseError('Some message', 12345);
        $this->assertInstanceOf(DatabaseError::class, $e);
        $this->assertSame('Some message', $e->getMessage());
        $this->assertSame(12345, $e->getCode());
    }

    /**
     * @covers ::__construct
     * @covers ::getSqlState
     */
    public function testConstructWithMessageAndStringCode()
    {
        $e = new DatabaseError('Some message', 12345, 'HY000');
        $this->assertInstanceOf(DatabaseError::class, $e);
        $this->assertSame('Some message', $e->getMessage());
        $this->assertSame(12345, $e->getCode());
        $this->assertSame('HY000', $e->getSqlState());
    }
}
