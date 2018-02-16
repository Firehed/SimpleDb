<?php
declare(strict_types=1);

namespace Firehed\SimpleDb;

use Generator;
use PDO;

class SimpleDb
{
    /** @var PDO */
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Builds, executes, and yields a parameterized SELECT query. Accepts the
     * query with `:param`-style placeholders as the first argument, and an
     * array of `:param => value` parameters for the second argument. This will
     * expand any parameters where the value is an array to an `IN`-style
     * clause, and as such should only be used in such a clause.
     *
     * @param string $query The query, with any named parameters
     * @param array $originalParameters The parameters to bind when executing
     * the query
     *
     * @return Generator The results to be iterated over. Each value that the
     * generator yields will be a row in the result set, `FETCH_ASSOC`-style.
     */
    public function select(string $query, array $originalParameters = []): Generator
    {
        $parameters = [];
        foreach ($originalParameters as $binding => $value) {
            if (!is_array($value)) {
                $parameters[$binding] = $value;
                continue;
            }

            // If the parameter value is an array, translate the array values
            // into a list of bindings for an `IN()` clause. E.g. `IN(:foo)`
            // with `[':foo' => [1, 2, 3]]` will become something like
            // `IN(:foo_1, :foo_2, :foo_3)`.
            $bindings = [];
            foreach ($value as $i => $innerValue) {
                $generated = sprintf('%s_%d', $binding, $i);
                $bindings[] = $generated;
                $parameters[$generated] = $innerValue;
            }

            $inClause = implode(', ', $bindings);
            $query = str_replace($binding, $inClause, $query);
        }

        // @var \PDOStatement
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            list($sqlstate, $code, $message) = $this->db->errorInfo();
            throw new PrepareError($message, $code, $sqlstate);
        }

        if (!$stmt->execute($parameters)) {
            list($sqlstate, $code, $message) = $stmt->errorInfo();
            throw new ExecuteError($message, $code, $sqlstate);
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    /**
     * Select a single row from the results and immediately return it, avoiding
     * the need to work with a generator in any way.
     *
     * @see ::select
     */
    public function selectOne(string $query, array $originalParameters = []): array
    {
        $results = $this->select($query, $originalParameters);
        $row = $results->current();
        if ($row === null) {
            throw new NoResultError('Not found');
        }
        return $row;
    }
}
