<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\CompanyModel;

$user = Auth::requireRole('employer');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $co = CompanyModel::byUserId($user['id']);
    Response::json(['ok' => true, 'company' => $co]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $body = file_get_contents('php://input');
    $raw = json_decode($body ?: '[]', true);
    if (!is_array($raw)) {
        $raw = [];
    }
    $co = CompanyModel::byUserId($user['id']);
    if ($co) {
        // Merge with existing row so missing keys do not wipe fields (and empty PUT bodies on some servers do not null everything).
        $patch = [
            'company_name' => array_key_exists('company_name', $raw)
                ? trim((string) $raw['company_name'])
                : ($co['company_name'] ?? ''),
            'description' => array_key_exists('description', $raw)
                ? ($raw['description'] !== null && $raw['description'] !== '' ? (string) $raw['description'] : null)
                : ($co['description'] ?? null),
            'website' => array_key_exists('website', $raw)
                ? ($raw['website'] !== null && $raw['website'] !== '' ? (string) $raw['website'] : null)
                : ($co['website'] ?? null),
            'location' => array_key_exists('location', $raw)
                ? ($raw['location'] !== null && $raw['location'] !== '' ? (string) $raw['location'] : null)
                : ($co['location'] ?? null),
        ];
        if ($patch['company_name'] === '') {
            Response::json(['ok' => false, 'error' => 'Company name required'], 422);
        }
        CompanyModel::update((int) $co['id'], $patch);
    } else {
        $name = trim((string) ($raw['company_name'] ?? ''));
        if ($name === '') {
            Response::json(['ok' => false, 'error' => 'Company name required'], 422);
        }
        CompanyModel::create(
            $user['id'],
            $name,
            isset($raw['description']) ? (string) $raw['description'] : null,
            isset($raw['website']) ? (string) $raw['website'] : null,
            isset($raw['location']) ? (string) $raw['location'] : null
        );
    }
    Response::json(['ok' => true, 'company' => CompanyModel::byUserId($user['id'])]);
}

Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
