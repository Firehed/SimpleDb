<?php
declare(strict_types=1);

namespace Firehed\SimpleDb;

/**
 * @coversDefaultClass Firehed\SimpleDb\PrepareError
 * @covers ::<protected>
 * @covers ::<private>
 */
class PrepareErrorTest extends \PHPUnit\Framework\TestCase
{
    /** @covers ::__construct */
    public function testConstruct()
    {
        $e = new PrepareError();
        $this->assertInstanceOf(PrepareError::class, $e);
    }
}
