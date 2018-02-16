# SimpleDb
A very simple PDO wrapper

Detailed documentation is coming Soon™.

## API

### `Firehed\SimpleDb\SimpleDb`

#### `public function __construct(PDO $pdo)`
Constructor

#### `public function select(string $query, array $params = []): Generator`

Pass in any SELECT query, with colon-prefixed `:placeholder`s.
For each of those placeholders, ensure `$params` has a matching key, including the colon.
The value can be any scalar type, _or an array of scalar types_ which will automatically be expanded when matched with an `IN()` clause.
Automatic `IN` support was the primary motivation behind this library.

#### `public function selectOne(string $query, array $params = []): array`

Same as above, but you get either the first row directly or a `NoResultError` will be thrown.
Nice for primary/unique key `SELECT`s.
