<?php
declare(strict_types=1);

namespace Firehed\SimpleDb;

/**
 * @coversDefaultClass Firehed\SimpleDb\NoResultError
 * @covers ::<protected>
 * @covers ::<private>
 */
class NoResultErrorTest extends \PHPUnit\Framework\TestCase
{
    /** @covers ::__construct */
    public function testConstruct()
    {
        $e = new NoResultError();
        $this->assertInstanceOf(NoResultError::class, $e);
    }
}
