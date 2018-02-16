<?php
declare(strict_types=1);

namespace Firehed\SimpleDb;

/**
 * @coversDefaultClass Firehed\SimpleDb\ExecuteError
 * @covers ::<protected>
 * @covers ::<private>
 */
class ExecuteErrorTest extends \PHPUnit\Framework\TestCase
{
    /** @covers ::__construct */
    public function testConstruct()
    {
        $e = new ExecuteError();
        $this->assertInstanceOf(ExecuteError::class, $e);
    }
}
