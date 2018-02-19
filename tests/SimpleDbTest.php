<?php
declare(strict_types=1);

namespace Firehed\SimpleDb;

use PDO;

/**
 * @coversDefaultClass Firehed\SimpleDb\SimpleDb
 * @covers ::<protected>
 * @covers ::<private>
 */
class SimpleDbTest extends \PHPUnit\Framework\TestCase
{
    // @var PDO
    private $pdo;

    // @var SimpleDb
    private $simpleDb;

    /** @var PDO */
    public function setUp()
    {
        $this->pdo = new PDO('sqlite::memory:');
        $sql = 'CREATE TABLE settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            key NVARCHAR(255) NOT NULL COLLATE NOCASE,
            value NVARCHAR(255) NULL
        );';
        $this->pdo->query($sql);
        $this->pdo->query('INSERT INTO settings VALUES
            (1, "one", "one"),
            (2, "two", "two"),
            (3, "three", "three")');

        $this->simpleDb = new SimpleDb($this->pdo);
    }

    /** @covers ::__construct */
    public function testConstruct()
    {
        $this->assertInstanceOf(SimpleDb::class, new SimpleDb($this->pdo));
    }

    /** @covers ::select */
    public function testSimpleQuery()
    {
        $i = 0;
        foreach ($this->simpleDb->select('SELECT * FROM settings WHERE id = :id', [':id' => 1]) as $row) {
            $i++;
            $expected = [
                'id' => '1', // FIXME why is this stringy?
                'key' => 'one',
                'value' => 'one',
            ];
            $this->assertSame($expected, $row, 'Output from DB did not match expectation');
        }
        $this->assertSame(1, $i, 'Wrong number of rows yielded');
    }

    /** @covers ::select */
    public function testInClause()
    {
        $clauses = [':ids' => [1, 2]];
        $rows = [];
        foreach ($this->simpleDb->select('SELECT * FROM settings WHERE id IN (:ids)', $clauses) as $row) {
            $rows[] = $row;
        }
        $this->assertSame(2, count($rows), 'Wrong number of rows yielded');
        // TODO: match rows
    }

    /** @covers ::select */
    public function testSelectPartialColumns()
    {
        $clauses = [':id' => 2];
        $i = 0;
        foreach ($this->simpleDb->select('SELECT id, key FROM settings WHERE id = :id', $clauses) as $row) {
            $i++;
            $this->assertSame([
                'id' => '2', // FIXME: stringyness
                'key' => 'two',
            ], $row, 'Incorrect row output');
        }
        $this->assertSame(1, $i, 'Wrong number of rows yielded');
    }

    /** @covers ::selectOne */
    public function testSelectOneWithOneResult()
    {
        $row = $this->simpleDb->selectOne('SELECT * FROM settings WHERE id = :id', [':id' => 1]);
        $this->assertSame(
            $row,
            [
                'id' => '1',
                'key' => 'one',
                'value' => 'one',
            ]
        );
    }

    /** @covers ::selectOne */
    public function testSelectOneWithTwoResultsAsc()
    {
        $row = $this->simpleDb->selectOne(
            'SELECT * FROM settings WHERE id IN (:ids) ORDER BY id ASC',
            [':ids' => [1, 2]]
        );
        $this->assertSame(
            $row,
            [
                'id' => '1',
                'key' => 'one',
                'value' => 'one',
            ]
        );
    }

    /** @covers ::selectOne */
    public function testSelectOneWithTwoResultsDesc()
    {
        $row = $this->simpleDb->selectOne(
            'SELECT * FROM settings WHERE id IN (:ids) ORDER BY id DESC',
            [':ids' => [1, 2]]
        );
        $this->assertSame(
            $row,
            [
                'id' => '2',
                'key' => 'two',
                'value' => 'two',
            ]
        );
    }

    /** @covers ::selectOne */
    public function testInvalidTableQueryWithSelectOne()
    {
        $this->expectException(PrepareError::class);
        $this->simpleDb->selectOne('SELECT * FROM faketable');
    }

    /** @covers ::selectOne */
    public function testInvalidColumnQueryWithSelectOne()
    {
        $this->expectException(ExecuteError::class);
        $this->simpleDb->selectOne('SELECT * FROM settings WHERE id = :id', [':iid' => 1]);
    }


    /** @covers ::select */
    public function testInvalidTableQueryWithSelect()
    {
        $this->expectException(PrepareError::class);
        // This needs to actually be a loop or it never actually starts the
        // generator
        foreach ($this->simpleDb->select('SELECT * FROM faketable') as $x) {
        }
    }

    /** @covers ::select */
    public function testInvalidColumnQueryWithSelect()
    {
        $this->expectException(ExecuteError::class);
        $res = $this->simpleDb->select('SELECT * FROM settings WHERE id = :id', [':iid' => 1]);
        // This needs to actually be a loop or it never actually starts the
        // generator
        foreach ($res as $x) {
        }
    }

    /** @covers ::select */
    public function testSelectWithNoResults()
    {
        $results = $this->simpleDb->select('SELECT * FROM settings WHERE id = :id', [':id' => 4]);
        $i = 0;
        foreach ($results as $result) {
            $i++;
        }
        $this->assertSame(0, $i, 'Should have had zero rows');
    }

    /** @covers ::selectOne */
    public function testSelectOneWithNoResults()
    {
        $this->expectException(NoResultError::class);
        $this->simpleDb->selectOne('SELECT * FROM settings WHERE id = :id', [':id' => 4]);
    }

    /** @covers ::insert */
    public function testInsert()
    {
        $id = $this->simpleDb->insert('settings', [
            'key' => 'four',
            'value' => 'four',
        ]);
        $this->assertSame('4', $id, 'Insert did not return last insert id');

        $newRow = $this->simpleDb->selectOne('SELECT * FROM settings WHERE id = :id', [':id' => 4]);
        $this->assertSame($newRow, [
            'id' => '4',
            'key' => 'four',
            'value' => 'four',
        ]);
    }

    /** @covers ::insert */
    public function testInsertWithInvalidColumn()
    {
        $this->expectException(PrepareError::class);
        $id = $this->simpleDb->insert('settings', [
            'key' => 'four',
            'val' => 'four',
        ]);
    }

    /** @covers ::insert */
    public function testInsertWithInvalidValue()
    {
        $this->expectException(ExecuteError::class);
        $id = $this->simpleDb->insert('settings', [
            'key' => null,
            'value' => 'four',
        ]);
    }
}
