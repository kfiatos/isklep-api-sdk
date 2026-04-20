# ISklep API SDK

Klient REST do API ISklep napisany w PHP 8.4. Biblioteka działa na zasadzie zasobow (resource-based) — żeby dodać obsługę nowego endpointu wystarczą dwa pliki.

## Uruchomienie

### Wymagania

- PHP 8.4+
- Composer
- Docker (do testow i przykładu)

### Instalacja

```bash
make install
```

albo bezpośrednio przez composer:

```bash
composer install
```

### Uruchomienie kontenerów

SDK działa wewnątrz kontenera Docker, który jest podpięty do tej samej sieci co API (`codelabs_default`).

```bash
docker compose -f docker/docker-compose.yaml up -d
```

Przykładowy skrypt można uruchomić tak:

```bash
docker compose -f docker/docker-compose.yaml exec sdk php example/example.php
```

## Testy i linting

```bash
make test       # PHPUnit
make analyse    # PHPStan poziom 8
make cs-fixer   # PHP CS Fixer
make all        # wszystko naraz
```

## Architektura

### Jak to działa

Serce biblioteki to klasa `ResourceApi` — abstrakcja która implementuje pełny CRUD (list, get, create, update, delete). Żeby dodac obsługę nowego zasobu trzeba zaimplementować dwie metody:

- `getModelClass()` — zwraca klasę modelu
- `getOperations()` — mapuje operacje na URI-e

Reszta (wysyłanie requestów, parsowanie odpowiedzi, obsługa błędów) jest już gotowa.

### Dodawanie nowego zasobu

Potrzebne są dwa pliki.

**1. Model** (`src/Models/Category.php`):

```php
final class Category extends AbstractModel
{
    public static function getResourceKey(): string
    {
        return 'category';
    }

    public function __construct(
        public readonly int|string|null $id,
        public readonly string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
```

**2. Klasa API** (`src/Api/CategoriesApi.php`):

```php
/** @extends ResourceApi<Category> */
final class CategoriesApi extends ResourceApi
{
    protected function getModelClass(): string
    {
        return Category::class;
    }

    protected function getOperations(): array
    {
        return [
            Operation::List->value   => '/shop_api/v1/categories',
            Operation::Create->value => '/shop_api/v1/categories',
            Operation::Get->value    => '/shop_api/v1/categories/{id}',
            Operation::Update->value => '/shop_api/v1/categories/{id}',
            Operation::Delete->value => '/shop_api/v1/categories/{id}',
        ];
    }
}
```

i tyle. Nie potrzeba nic więcej.

### Inicjalizacja klienta

```php
use ISklep\Api\Client;
use ISklep\Api\Authorisation\BasicAuthorisation;
use ISklep\Api\Http\GuzzleHttpClientFactory;
use ISklep\Api\JsonResponseDecoder;
use GuzzleHttp\Client as GuzzleHttpClient;

$client = new Client(
    httpClient: new GuzzleHttpClientFactory(new GuzzleHttpClient()),
    authorisation: new BasicAuthorisation('login', 'haslo'),
    baseUri: 'http://rekrutacja.localhost:8091',
);

$api = new ProducersApi($client, new JsonResponseDecoder());

$producers = $api->list();
$created   = $api->create(new Producer(id: 123, name: 'Nowy'));
```

### Dekodery odpowiedzi

Biblioteka ma dwa dekodery i mozna łatwo dołożyć własny przez implementację `ResponseDecoderInterface`:

| Dekoder | Kiedy używać                                              |
|---------|-----------------------------------------------------------|
| `JsonResponseDecoder` | API zwraca plain JSON (tak działa ISklep API)             |
| `WrappedResponseDecoder` | API wrappuje odpowiedź w `{"success": true, "data": ...}` |

Domyślnie `ResourceApi` używa `WrappedResponseDecoder` — jezeli API zwraca dane bezpośrednio to trzeba przekazać dekoder explicite jak w przykładzie wyżej.

### Obsługa błędów

```php
use ISklep\Api\Exceptions\UnauthorizedException;
use ISklep\Api\Exceptions\HttpException;
use ISklep\Api\Exceptions\ApiException;

try {
    $api->create($producer);
} catch (UnauthorizedException $e) {
    // 401 - zły token / haslo
} catch (HttpException $e) {
    echo $e->getStatusCode();   
    echo $e->getMessage();      // błąd z API
    echo $e->getResponseBody();
} catch (ApiException $e) {
    // pozostale bledy (sieciowe, deserializacja itp.)
}
```

## Co można jeszcze zrobić

- **Logowanie** — `Client` ma już opcjonalny `LoggerInterface` wystarczy go przekazać (lub nie), pomocne przy impelemntacji biblioteki
- **Inne implementacje HTTP** — `HttpClientFactoryInterface` implementuje PSR-17 + PSR-18, mozna podmienić Guzzle na Symfony HttpClient bez zmian w reszcie kodu
- **Cache** — GET-y można owinąć w PSR-6 cache
- **Paginacja** — `list()` przyjmuje `$params` więc mozna przekazać np `page` i `per_page`

Zrobiłem dwie wersje. W pierwszej wersji było to Request based i wymagało stworzenia requestu per operacja
ale wydało się to zbyt dużym overheadem skoro nic więcej nie wiadomo, a payloady requestów i odopwiedzi wyglądaja prawie tak samo.

W drugiej wersji jest to Resource based i wystarczy zdefiniować URI per operację, a reszta jest już gotowa. Wydaje się to prostsze, ale oczywiście można to zrobić na wiele sposobów. Rozszerzanie jest relatywnie proste.
