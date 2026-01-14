<?php

declare(strict_types=1);

/**
 * Heuristic ERD generator from Laravel migration files.
 * - Reads database/migrations/*.php
 * - Extracts Schema::create tables, columns, foreignId()->constrained()
 * - Extracts simple Schema::table additions for users
 * - Outputs Mermaid erDiagram to docs/diagrams/database-erd.mmd
 */

$projectRoot = realpath(__DIR__ . '/..');
if ($projectRoot === false) {
    fwrite(STDERR, "Could not resolve project root\n");
    exit(1);
}

$migrationsDir = $projectRoot . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
$outFile = $projectRoot . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'diagrams' . DIRECTORY_SEPARATOR . 'database-erd.mmd';

$files = glob($migrationsDir . DIRECTORY_SEPARATOR . '*.php') ?: [];
if (count($files) === 0) {
    fwrite(STDERR, "No migration files found in {$migrationsDir}\n");
    exit(1);
}

/** @var array<string, array{columns: array<string, array{type:string, key?:string}>, fks: array<int, array{column:string, refTable:string}>}> $schema */
$schema = [];

function ensureTable(array &$schema, string $table): void {
    if (!isset($schema[$table])) {
        $schema[$table] = ['columns' => [], 'fks' => []];
    }
}

function addColumn(array &$schema, string $table, string $name, string $type, ?string $key = null): void {
    ensureTable($schema, $table);
    // Keep first-seen type, but allow upgrading key if needed.
    if (!isset($schema[$table]['columns'][$name])) {
        $schema[$table]['columns'][$name] = ['type' => $type];
    }
    if ($key !== null) {
        $existingKey = $schema[$table]['columns'][$name]['key'] ?? null;
        if ($existingKey === null) {
            $schema[$table]['columns'][$name]['key'] = $key;
        } else {
            // merge keys (PK, FK, UK)
            $keys = array_unique(array_filter(array_map('trim', explode(',', $existingKey . ',' . $key))));
            $schema[$table]['columns'][$name]['key'] = implode(', ', $keys);
        }
    }
}

function addFk(array &$schema, string $table, string $column, string $refTable): void {
    ensureTable($schema, $table);
    $schema[$table]['fks'][] = ['column' => $column, 'refTable' => $refTable];
    addColumn($schema, $table, $column, 'bigint', 'FK');
}

function normalizeType(string $method): string {
    return match ($method) {
        'id', 'bigIncrements' => 'bigint',
        'increments', 'integer' => 'int',
        'string' => 'string',
        'text', 'longText' => 'text',
        'boolean' => 'bool',
        'float', 'double' => 'float',
        'decimal' => 'decimal',
        'date' => 'date',
        'dateTime', 'timestamp' => 'datetime',
        'json' => 'json',
        'uuid' => 'uuid',
        default => $method,
    };
}

function parseCreateBlocks(string $php): array {
    // returns array of ['table' => string, 'body' => string]
    $results = [];
    $pattern = '/Schema::create\(\s*\'([^\']+)\'\s*,\s*function\s*\(\s*Blueprint\s*\$table\s*\)\s*\{([\s\S]*?)\}\s*\)\s*;/m';
    if (preg_match_all($pattern, $php, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $results[] = ['table' => $m[1], 'body' => $m[2]];
        }
    }
    return $results;
}

function parseTableBlocks(string $php): array {
    // returns array of ['table' => string, 'body' => string]
    $results = [];
    $pattern = '/Schema::table\(\s*\'([^\']+)\'\s*,\s*function\s*\(\s*Blueprint\s*\$table\s*\)\s*\{([\s\S]*?)\}\s*\)\s*;/m';
    if (preg_match_all($pattern, $php, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $results[] = ['table' => $m[1], 'body' => $m[2]];
        }
    }
    return $results;
}

function extractUpBody(string $php): string {
    // Best-effort extraction of the up() method body. We only use this to detect explicit drop migrations.
    if (preg_match('/function\s+up\s*\(\s*\)\s*:\s*void\s*\{([\s\S]*?)\n\s*\}/m', $php, $m)) {
        return $m[1];
    }
    return '';
}

function parseColumnsAndFks(array &$schema, string $table, string $body): void {
    // id
    if (preg_match('/\$table->id\(\)\s*;/', $body)) {
        addColumn($schema, $table, 'id', 'bigint', 'PK');
    }

    // foreignId('x')->constrained('y')
    $fkPattern = '/\$table->foreignId\(\s*\'([^\']+)\'\s*\)\s*->constrained\(\s*\'([^\']+)\'\s*\)\s*;/m';
    if (preg_match_all($fkPattern, $body, $m, PREG_SET_ORDER)) {
        foreach ($m as $fk) {
            addFk($schema, $table, $fk[1], $fk[2]);
        }
    }

    // foreignId('x')->constrained()
    $fkDefaultPattern = '/\$table->foreignId\(\s*\'([^\']+)\'\s*\)\s*->constrained\(\s*\)\s*;/m';
    if (preg_match_all($fkDefaultPattern, $body, $m2, PREG_SET_ORDER)) {
        foreach ($m2 as $fk) {
            $col = $fk[1];
            $ref = preg_replace('/_id$/', '', $col);
            // Laravel default: table name plural (very rough)
            $refTable = $ref . 's';
            addFk($schema, $table, $col, $refTable);
        }
    }

    // common columns: string/text/integer/float/decimal/date/json/uuid
    $colPattern = '/\$table->(string|text|longText|integer|float|double|decimal|date|dateTime|timestamp|json|uuid)\(\s*\'([^\']+)\'\s*(?:,\s*[^\)]*)?\)\s*(?:->[^;]+)?;/m';
    if (preg_match_all($colPattern, $body, $cols, PREG_SET_ORDER)) {
        foreach ($cols as $c) {
            addColumn($schema, $table, $c[2], normalizeType($c[1]));
        }
    }

    // timestamps
    if (preg_match('/\$table->timestamps\(\)\s*;/', $body)) {
        addColumn($schema, $table, 'created_at', 'datetime');
        addColumn($schema, $table, 'updated_at', 'datetime');
    }
}

foreach ($files as $file) {
    $php = file_get_contents($file);
    if ($php === false) {
        continue;
    }

    foreach (parseCreateBlocks($php) as $block) {
        $table = $block['table'];
        ensureTable($schema, $table);
        parseColumnsAndFks($schema, $table, $block['body']);
    }

    foreach (parseTableBlocks($php) as $block) {
        // Only parse added columns (ignore dropColumn)
        $table = $block['table'];
        ensureTable($schema, $table);
        parseColumnsAndFks($schema, $table, $block['body']);
    }
}

// Remove tables that are explicitly dropped in an up() method (e.g. drop_*_table migrations).
$dropPattern = "/Schema::dropIfExists\(\s*'([^']+)'\s*\)\s*;/m";
foreach ($files as $file) {
    $php = file_get_contents($file);
    if ($php === false) {
        continue;
    }
    $upBody = extractUpBody($php);
    if ($upBody === '') {
        continue;
    }
    if (preg_match_all($dropPattern, $upBody, $drops, PREG_SET_ORDER)) {
        foreach ($drops as $d) {
            unset($schema[$d[1]]);
        }
    }
}

// Build Mermaid
$lines = [];
$lines[] = '---';
$lines[] = 'title: Database ERD';
$lines[] = '---';
$lines[] = 'erDiagram';
$lines[] = '    direction LR';

// Relationships
$relLines = [];
foreach ($schema as $table => $info) {
    foreach ($info['fks'] as $fk) {
        $parent = strtoupper($fk['refTable']);
        $child = strtoupper($table);
        $label = $fk['column'];
        $relLines[] = "    {$parent} ||--o{ {$child} : \"{$label}\"";
    }
}
$relLines = array_values(array_unique($relLines));
sort($relLines);
$lines = array_merge($lines, $relLines);

// Entities
$lines[] = '';
$tables = array_keys($schema);
sort($tables);
foreach ($tables as $table) {
    $entity = strtoupper($table);
    $lines[] = "    {$entity} {";

    $cols = $schema[$table]['columns'];
    // stable order: id first, then others alpha
    $colNames = array_keys($cols);
    usort($colNames, function ($a, $b) {
        if ($a === 'id') return -1;
        if ($b === 'id') return 1;
        return strcmp($a, $b);
    });

    foreach ($colNames as $name) {
        $type = $cols[$name]['type'] ?? 'string';
        $key = $cols[$name]['key'] ?? null;
        if ($key !== null) {
            $lines[] = "        {$type} {$name} {$key}";
        } else {
            $lines[] = "        {$type} {$name}";
        }
    }

    $lines[] = '    }';
    $lines[] = '';
}

$out = implode("\n", $lines);

@mkdir(dirname($outFile), 0777, true);
file_put_contents($outFile, $out);

fwrite(STDOUT, "Wrote ERD to {$outFile}\n");
