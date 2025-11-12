<?php
declare(strict_types=1);

$routeParam = isset($_GET['route']) ? '/' . ltrim((string) $_GET['route'], '/') : null;

if ($routeParam === null && php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;

    if ($path !== '/' && $path !== '' && file_exists($file) && !is_dir($file)) {
        return false;
    }
}

$uri = $routeParam ?? (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($uri) {
    case '/upload':
        if ($method !== 'POST') {
            respondJson(405, ['message' => 'Método não permitido.']);
        }
        handleUpload();
        break;
    case '/methods':
        if ($method === 'GET') {
            handleGetMethods();
            break;
        }

        if ($method === 'POST') {
            handleCreateMethod();
            break;
        }

        respondJson(405, ['message' => 'Método não permitido.']);
        break;
    case '/games':
        if ($method !== 'GET') {
            respondJson(405, ['message' => 'Método não permitido.']);
        }
        handleGetGames();
        break;
    case '/games/selection':
        if ($method !== 'POST') {
            respondJson(405, ['message' => 'Método não permitido.']);
        }
        handleSelectionUpdate();
        break;
    case '/':
        if ($method === 'GET') {
            readfile(__DIR__ . '/index.html');
            break;
        }
        respondJson(405, ['message' => 'Método não permitido.']);
        break;
    default:
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['message' => 'Rota não encontrada.'], JSON_UNESCAPED_UNICODE);
        break;
}

function handleUpload(): void
{
    $payload = file_get_contents('php://input');

    if ($payload === false) {
        respondJson(400, ['message' => 'Não foi possível ler os dados enviados.']);
    }

    $decoded = json_decode($payload, true);

    if (!is_array($decoded) || !array_key_exists('csv', $decoded)) {
        respondJson(400, ['message' => 'Formato de conteúdo inválido.']);
    }

    if (!array_key_exists('matchDate', $decoded)) {
        respondJson(400, ['message' => 'Selecione a data dos jogos antes de enviar.']);
    }

    try {
        $matchDate = normalizeMatchDate($decoded['matchDate']);
    } catch (InvalidArgumentException $exception) {
        respondJson(400, ['message' => $exception->getMessage()]);
    }

    $csvText = trim((string) $decoded['csv']);

    if ($csvText === '') {
        respondJson(400, ['message' => 'Cole o conteúdo do CSV antes de enviar.']);
    }

    $requiredColumns = [
        'liga',
        'pais',
        'horario',
        'casa',
        'visitante',
        'odd_casa',
        'odd_visitante',
        'media_gols',
    ];

    $requiredColumnLabels = [
        'liga' => 'Liga',
        'pais' => 'País',
        'horario' => 'Horário',
        'casa' => 'Casa',
        'visitante' => 'Visitante',
        'odd_casa' => 'Odd Casa',
        'odd_visitante' => 'Odd Visitante',
        'media_gols' => 'Média Gols',
    ];

    $defaultHeaderRow = [
        'Liga',
        'País',
        'Horário',
        'Casa',
        'Visitante',
        'Odd Casa',
        'Odd Visitante',
        'Média Gols',
    ];
    $defaultNormalized = array_map('normalizeKey', array_map('cleanHeader', $defaultHeaderRow));

    $lines = preg_split("/(\r\n|\r|\n)/", $csvText);
    $lines = array_values(array_filter(array_map('trim', $lines), static fn(string $line): bool => $line !== ''));

    if (empty($lines)) {
        respondJson(400, ['message' => 'Nenhuma linha válida encontrada no conteúdo enviado.']);
    }

    $firstRowValues = array_map('trim', str_getcsv($lines[0]));
    $firstRowClean = array_map('cleanHeader', $firstRowValues);
    $firstRowNormalized = array_map('normalizeKey', $firstRowClean);

    $headerRow = $firstRowClean;
    $normalizedHeaders = $firstRowNormalized;
    $dataStartIndex = 1;

    $missing = array_diff($requiredColumns, $normalizedHeaders);

    if (!empty($missing)) {
        // assume que o conteúdo não possui cabeçalho e usa o padrão esperado
        $headerRow = $defaultHeaderRow;
        $normalizedHeaders = $defaultNormalized;
        $dataStartIndex = 0;
    }

    if (count(array_unique($normalizedHeaders)) !== count($normalizedHeaders)) {
        respondJson(400, ['message' => 'Existem colunas duplicadas após normalização. Renomeie o cabeçalho e tente novamente.']);
    }

    if ($dataStartIndex === 1 && !empty($missing)) {
        $missingLabels = array_map(static fn(string $key) => $requiredColumnLabels[$key] ?? $key, $missing);
        respondJson(400, ['message' => sprintf('As colunas obrigatórias "%s" não foram encontradas.', implode('", "', $missingLabels))]);
    }

    if ($dataStartIndex === 0) {
        // sem cabeçalho, exige exatamente as colunas padrão
        $expectedColumns = count($defaultHeaderRow);
    } else {
        $expectedColumns = count($headerRow);
    }

    $rows = [];

    for ($index = $dataStartIndex; $index < count($lines); $index++) {
        $data = array_map('trim', str_getcsv($lines[$index]));

        if ($data === ['']) {
            continue;
        }

        if (count($data) !== $expectedColumns) {
            respondJson(400, ['message' => 'Linha do CSV com número incorreto de colunas.']);
        }

        $rawAssoc = [];
        $normalizedAssoc = [];

        foreach ($data as $columnIndex => $value) {
            $label = $headerRow[$columnIndex] ?? $defaultHeaderRow[$columnIndex] ?? 'Coluna ' . ($columnIndex + 1);
            $rawValue = trim((string) $value);
            $normalizedKey = $normalizedHeaders[$columnIndex] ?? normalizeKey($label);

            $rawAssoc[$label] = $rawValue;
            $normalizedAssoc[$normalizedKey] = $rawValue;
        }

        $rows[] = [
            'raw' => $rawAssoc,
            'normalized' => $normalizedAssoc,
        ];
    }

    if (empty($rows)) {
        respondJson(400, ['message' => 'Nenhuma linha válida encontrada no conteúdo enviado.']);
    }

    try {
        $inserted = persistRows($headerRow, $rows, $matchDate);
    } catch (Throwable $exception) {
        respondJson(500, ['message' => 'Erro ao salvar os dados no banco.', 'detail' => $exception->getMessage()]);
    }

    respondJson(200, ['inserted' => $inserted]);
}

function handleGetGames(): void
{
    try {
        $pdo = getDatabaseConnection();
    } catch (Throwable $exception) {
        respondJson(500, ['message' => 'Erro ao abrir o banco de dados.', 'detail' => $exception->getMessage()]);
    }

    $selectedOnly = isset($_GET['selected']) && $_GET['selected'] === '1';

    $query = 'SELECT g.id, g.match_date, g.match_time, g.league, g.home_team, g.away_team, g.raw_data, g.selected, g.expected_goals, g.method, g.method_id, g.link, m.name AS method_name, m.color AS method_color
        FROM games g
        LEFT JOIN methods m ON m.id = g.method_id';

    if ($selectedOnly) {
        $query .= ' WHERE g.selected = 1';
    }

    $query .= ' ORDER BY g.match_date, g.match_time, g.id';

    $statement = $pdo->query($query);
    $records = $statement !== false ? $statement->fetchAll() : [];

    $games = [];

    foreach ($records as $record) {
        $raw = json_decode($record['raw_data'], true);
        if (!is_array($raw)) {
            $raw = [];
        }

        $games[] = [
            'id' => (int) $record['id'],
            'match_date' => $record['match_date'],
            'match_time' => $record['match_time'],
            'league' => $record['league'],
            'home_team' => $record['home_team'],
            'away_team' => $record['away_team'],
            'selected' => (bool) $record['selected'],
            'expected_goals' => $record['expected_goals'] !== null ? (float) $record['expected_goals'] : null,
            'method' => $record['method_name'] ?? $record['method'],
            'method_id' => $record['method_id'] !== null ? (int) $record['method_id'] : null,
            'method_color' => $record['method_color'] ?? null,
            'link' => $record['link'] !== null ? (string) $record['link'] : null,
            'raw' => $raw,
        ];
    }

    respondJson(200, [
        'headers' => loadHeaders(),
        'games' => $games,
    ]);
}

function handleSelectionUpdate(): void
{
    $payload = file_get_contents('php://input');

    if ($payload === false) {
        respondJson(400, ['message' => 'Não foi possível ler os dados enviados.']);
    }

    $decoded = json_decode($payload, true);

    if (!is_array($decoded) || !isset($decoded['updates']) || !is_array($decoded['updates'])) {
        respondJson(400, ['message' => 'Formato de payload inválido.']);
    }

    if ($decoded['updates'] === []) {
        respondJson(200, ['updated' => 0]);
    }

    try {
        $pdo = getDatabaseConnection();
    } catch (Throwable $exception) {
        respondJson(500, ['message' => 'Erro ao abrir o banco de dados.', 'detail' => $exception->getMessage()]);
    }

    $statement = $pdo->prepare('UPDATE games SET selected = :selected, expected_goals = :expected_goals, method = :method, method_id = :method_id, link = :link WHERE id = :id');

    if ($statement === false) {
        respondJson(500, ['message' => 'Não foi possível preparar a consulta de atualização.']);
    }

    $loadGameStatement = $pdo->prepare('SELECT expected_goals, link FROM games WHERE id = :id');

    if ($loadGameStatement === false) {
        respondJson(500, ['message' => 'Não foi possível preparar a consulta de leitura.']);
    }

    $pdo->beginTransaction();
    $updated = 0;

    try {
        foreach ($decoded['updates'] as $update) {
            if (!is_array($update)) {
                throw new InvalidArgumentException('Atualização inválida.');
            }

            if (!isset($update['id'])) {
                throw new InvalidArgumentException('ID do jogo é obrigatório.');
            }

            $id = filter_var($update['id'], FILTER_VALIDATE_INT);
            if ($id === false) {
                throw new InvalidArgumentException('ID de jogo inválido.');
            }

            $loadGameStatement->execute([':id' => $id]);
            $currentGame = $loadGameStatement->fetch(PDO::FETCH_ASSOC);

            if ($currentGame === false) {
                throw new InvalidArgumentException('Jogo informado não foi encontrado.');
            }

            $loadGameStatement->closeCursor();

            $selected = !empty($update['selected']);
            $expectedGoals = null;
            $method = null;
            $methodIdValue = null;
            $linkValue = null;

            if ($selected) {
                if (!array_key_exists('method_id', $update)) {
                    throw new InvalidArgumentException('Selecione um método para os jogos escolhidos.');
                }

                $methodId = filter_var($update['method_id'], FILTER_VALIDATE_INT);
                if ($methodId === false || $methodId <= 0) {
                    throw new InvalidArgumentException('Método selecionado é inválido.');
                }

                $methodData = loadMethodById($pdo, $methodId);

                if ($methodData === null) {
                    throw new InvalidArgumentException('Método selecionado não existe.');
                }

                if (array_key_exists('expected_goals', $update)) {
                    $expectedGoals = normalizeExpectedGoals($update['expected_goals']);
                } else {
                    $expectedGoals = $currentGame['expected_goals'] !== null ? (float) $currentGame['expected_goals'] : null;
                }

                $method = $methodData['name'];
                $methodIdValue = $methodData['id'];

                if (array_key_exists('link', $update)) {
                    $linkValue = normalizeLink($update['link']);
                } else {
                    $linkValue = isset($currentGame['link']) && $currentGame['link'] !== null ? (string) $currentGame['link'] : null;
                }
            } else {
                $linkValue = null;
            }

            $statement->execute([
                ':selected' => $selected ? 1 : 0,
                ':expected_goals' => $expectedGoals,
                ':method' => $method,
                ':method_id' => $methodIdValue,
                ':link' => $linkValue,
                ':id' => $id,
            ]);

            $updated++;
        }

        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        $status = $exception instanceof InvalidArgumentException ? 400 : 500;
        respondJson($status, ['message' => $exception->getMessage()]);
    }

    respondJson(200, ['updated' => $updated]);
}

function cleanHeader(string $header): string
{
    $header = (string) preg_replace('/^\xEF\xBB\xBF/', '', $header);
    return trim($header);
}

function normalizeKey(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/["\']/', '', $value);

    $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
    if ($transliterated !== false) {
        $value = $transliterated;
    }

    $value = preg_replace('/[^a-z0-9\s_-]/', '', $value);
    $value = str_replace(['-', ' '], '_', $value);
    $value = preg_replace('/_{2,}/', '_', $value);
    return trim($value, '_');
}

function normalizeExpectedGoals($value): float
{
    if (is_float($value) || is_int($value)) {
        return (float) $value;
    }

    $value = (string) $value;
    $normalized = str_replace([' ', "'"], '', $value);
    $normalized = str_replace(',', '.', $normalized);

    if (!is_numeric($normalized)) {
        throw new InvalidArgumentException('Valor de gols esperados inválido.');
    }

    return (float) $normalized;
}

function normalizeLink($value): ?string
{
    if ($value === null) {
        return null;
    }

    if (is_array($value)) {
        throw new InvalidArgumentException('Link inválido.');
    }

    $link = trim((string) $value);

    if ($link === '') {
        return null;
    }

    if (preg_match('#^[a-z][a-z0-9+\-.]*://#i', $link) !== 1) {
        $candidate = 'https://' . $link;
        if (filter_var($candidate, FILTER_VALIDATE_URL)) {
            $link = $candidate;
        }
    }

    if (!filter_var($link, FILTER_VALIDATE_URL)) {
        throw new InvalidArgumentException('Informe um link válido (http ou https).');
    }

    $scheme = parse_url($link, PHP_URL_SCHEME);
    if ($scheme === null || !in_array(strtolower($scheme), ['http', 'https'], true)) {
        throw new InvalidArgumentException('Informe um link válido (http ou https).');
    }

    if (strlen($link) > 2048) {
        throw new InvalidArgumentException('O link informado é muito longo.');
    }

    return $link;
}

function normalizeMatchDate($value): string
{
    if ($value instanceof DateTimeInterface) {
        return $value->format('Y-m-d');
    }

    if (is_array($value)) {
        throw new InvalidArgumentException('Selecione uma data válida para os jogos.');
    }

    $candidate = trim((string) $value);

    if ($candidate === '') {
        throw new InvalidArgumentException('Selecione uma data válida para os jogos.');
    }

    $normalized = normalizeCsvDate($candidate);

    if ($normalized === null) {
        throw new InvalidArgumentException('Selecione uma data válida para os jogos.');
    }

    return $normalized;
}

function persistRows(array $headers, array $rows, ?string $defaultMatchDate = null): int
{
    $pdo = getDatabaseConnection();

    $pdo->beginTransaction();

    try {
        $pdo->exec('DELETE FROM games');

        $statement = $pdo->prepare('INSERT INTO games (match_date, match_time, league, home_team, away_team, raw_data, selected, expected_goals, method, method_id, link)
            VALUES (:match_date, :match_time, :league, :home_team, :away_team, :raw_data, 0, NULL, NULL, NULL, NULL)');

        if ($statement === false) {
            throw new RuntimeException('Não foi possível preparar a consulta de inserção.');
        }

        foreach ($rows as $row) {
            $rawJson = json_encode($row['raw'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($rawJson === false) {
                throw new RuntimeException('Erro ao serializar dados da partida.');
            }

            $normalized = $row['normalized'];

            $statement->execute([
                ':match_date' => resolveMatchDate($normalized, $defaultMatchDate),
                ':match_time' => extractField($normalized, ['horario', 'hora', 'time', 'horario_jogo']),
                ':league' => extractField($normalized, ['liga', 'competicao', 'league', 'campeonato']),
                ':home_team' => extractField($normalized, ['casa', 'time_casa', 'mandante', 'home', 'equipe_casa', 'team_home']),
                ':away_team' => extractField($normalized, ['visitante', 'time_visitante', 'away', 'equipe_visitante', 'team_away']),
                ':raw_data' => $rawJson,
            ]);
        }

        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }

    storeHeaders($headers);

    return count($rows);
}

function extractField(array $normalizedRow, array $candidates): ?string
{
    foreach ($candidates as $candidate) {
        if (array_key_exists($candidate, $normalizedRow)) {
            $value = trim((string) $normalizedRow[$candidate]);
            if ($value !== '') {
                return $value;
            }
        }
    }

    return null;
}

function resolveMatchDate(array $normalizedRow, ?string $defaultMatchDate): ?string
{
    $candidate = extractField($normalizedRow, ['match_date', 'data', 'data_jogo', 'date']);

    if ($candidate !== null) {
        $normalized = normalizeCsvDate($candidate);
        if ($normalized !== null) {
            return $normalized;
        }
    }

    return $defaultMatchDate;
}

function normalizeCsvDate(?string $value): ?string
{
    if ($value === null) {
        return null;
    }

    $value = trim($value);

    if ($value === '') {
        return null;
    }

    $directFormats = [
        '!Y-m-d',
        '!Y/m/d',
        '!Y.m.d',
        '!d/m/Y',
        '!d-m-Y',
        '!d.m.Y',
        '!m/d/Y',
        '!m-d-Y',
        '!m.d.Y',
        '!d/m/y',
        '!d-m-y',
        '!d.m.y',
    ];

    foreach ($directFormats as $format) {
        $date = DateTime::createFromFormat($format, $value);
        if ($date instanceof DateTime) {
            $errors = DateTime::getLastErrors();
            if (($errors['warning_count'] ?? 0) === 0 && ($errors['error_count'] ?? 0) === 0) {
                return $date->format('Y-m-d');
            }
        }
    }

    $normalized = str_replace(['.', '-'], '/', $value);
    $normalized = preg_replace('/\s+/', '', $normalized);
    $parts = explode('/', $normalized);

    if (count($parts) === 3) {
        [$part1, $part2, $part3] = $parts;

        if ($part1 === '' || $part2 === '' || $part3 === '') {
            return null;
        }

        if (strlen($part1) === 4) {
            $year = (int) $part1;
            $month = (int) $part2;
            $day = (int) $part3;
        } else {
            $day = (int) $part1;
            $month = (int) $part2;
            $year = (int) $part3;

            if ($year < 100) {
                $year += $year >= 70 ? 1900 : 2000;
            }
        }

        if (checkdate($month, $day, $year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
    }

    return null;
}

function getDatabaseConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dataDir = getDataDirectory();
    $databasePath = $dataDir . '/games.sqlite';

    $pdo = new PDO('sqlite:' . $databasePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    runMigrations($pdo);

    return $pdo;
}

function runMigrations(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS methods (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        color TEXT NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS games (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        match_date TEXT,
        match_time TEXT,
        league TEXT,
        home_team TEXT,
        away_team TEXT,
        raw_data TEXT NOT NULL,
        selected INTEGER NOT NULL DEFAULT 0,
        expected_goals REAL,
        method TEXT,
        method_id INTEGER
    )');

    ensureColumn($pdo, 'games', 'method_id', 'INTEGER');
    ensureColumn($pdo, 'games', 'link', 'TEXT');
}

function ensureColumn(PDO $pdo, string $table, string $column, string $definition): void
{
    $statement = $pdo->query('PRAGMA table_info(' . $table . ')');
    $columns = $statement !== false ? $statement->fetchAll() : [];

    foreach ($columns as $info) {
        if (isset($info['name']) && strcasecmp($info['name'], $column) === 0) {
            return;
        }
    }

    $pdo->exec('ALTER TABLE ' . $table . ' ADD COLUMN ' . $column . ' ' . $definition);
}

function getDataDirectory(): string
{
    $dataDir = __DIR__ . '/data';

    if (!is_dir($dataDir)) {
        if (!mkdir($dataDir, 0775, true) && !is_dir($dataDir)) {
            throw new RuntimeException('Não foi possível criar o diretório de dados.');
        }
    }

    return $dataDir;
}

function storeHeaders(array $headers): void
{
    $dataDir = getDataDirectory();
    $filePath = $dataDir . '/headers.json';

    $payload = json_encode(['headers' => array_values($headers)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($payload === false) {
        throw new RuntimeException('Não foi possível serializar os cabeçalhos.');
    }

    if (file_put_contents($filePath, $payload) === false) {
        throw new RuntimeException('Falha ao gravar os cabeçalhos.');
    }
}

function loadHeaders(): array
{
    $filePath = __DIR__ . '/data/headers.json';

    if (!is_file($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        return [];
    }

    $decoded = json_decode($content, true);

    if (!is_array($decoded) || !isset($decoded['headers']) || !is_array($decoded['headers'])) {
        return [];
    }

    return array_map('strval', $decoded['headers']);
}

function handleGetMethods(): void
{
    try {
        $pdo = getDatabaseConnection();
    } catch (Throwable $exception) {
        respondJson(500, ['message' => 'Erro ao abrir o banco de dados.', 'detail' => $exception->getMessage()]);
    }

    $statement = $pdo->query('SELECT id, name, color FROM methods ORDER BY name COLLATE NOCASE');
    $methods = $statement !== false ? $statement->fetchAll() : [];

    $normalized = array_map(static function (array $method): array {
        return [
            'id' => (int) $method['id'],
            'name' => $method['name'],
            'color' => $method['color'],
        ];
    }, $methods);

    respondJson(200, ['methods' => $normalized]);
}

function handleCreateMethod(): void
{
    $payload = file_get_contents('php://input');

    if ($payload === false) {
        respondJson(400, ['message' => 'Não foi possível ler os dados enviados.']);
    }

    $decoded = json_decode($payload, true);

    if (!is_array($decoded)) {
        respondJson(400, ['message' => 'Formato de conteúdo inválido.']);
    }

    $name = isset($decoded['name']) ? trim((string) $decoded['name']) : '';
    $color = isset($decoded['color']) ? strtoupper(trim((string) $decoded['color'])) : '';

    if ($name === '') {
        respondJson(400, ['message' => 'Informe o nome do método.']);
    }

    if ($color === '') {
        respondJson(400, ['message' => 'Informe uma cor para o método.']);
    }

    if (!preg_match('/^#([0-9A-F]{6})$/', $color)) {
        respondJson(400, ['message' => 'A cor deve estar no formato hexadecimal, como #1A2B3C.']);
    }

    try {
        $pdo = getDatabaseConnection();
    } catch (Throwable $exception) {
        respondJson(500, ['message' => 'Erro ao abrir o banco de dados.', 'detail' => $exception->getMessage()]);
    }

    $statement = $pdo->prepare('INSERT INTO methods (name, color) VALUES (:name, :color)');

    if ($statement === false) {
        respondJson(500, ['message' => 'Não foi possível preparar o cadastro do método.']);
    }

    try {
        $statement->execute([
            ':name' => $name,
            ':color' => $color,
        ]);
    } catch (Throwable $exception) {
        if ($exception instanceof PDOException && $exception->getCode() === '23000') {
            respondJson(409, ['message' => 'Já existe um método com esse nome.']);
        }

        respondJson(500, ['message' => 'Erro ao salvar o método.', 'detail' => $exception->getMessage()]);
    }

    $id = (int) $pdo->lastInsertId();

    respondJson(201, [
        'method' => [
            'id' => $id,
            'name' => $name,
            'color' => $color,
        ],
    ]);
}

function loadMethodById(PDO $pdo, int $id): ?array
{
    $statement = $pdo->prepare('SELECT id, name, color FROM methods WHERE id = :id');

    if ($statement === false) {
        return null;
    }

    $statement->execute([':id' => $id]);
    $result = $statement->fetch();

    if ($result === false) {
        return null;
    }

    return [
        'id' => (int) $result['id'],
        'name' => $result['name'],
        'color' => $result['color'],
    ];
}

function respondJson(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
