<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use MeroKam\Core\Auth;
use MeroKam\Core\Response;
use MeroKam\Models\EmployerCreditsModel;
use MeroKam\Models\PaymentModel;

$user = Auth::user();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $plans = PaymentModel::plans();
    if (!$user) {
        Response::json(['ok' => true, 'plans' => $plans, 'payments' => []]);
    }
    if ($user['role'] === 'admin') {
        Response::json(['ok' => true, 'payments' => PaymentModel::listAll(200), 'plans' => $plans]);
    }
    if ($user['role'] === 'employer') {
        Response::json(['ok' => true, 'payments' => PaymentModel::listForUser($user['id']), 'plans' => $plans]);
    }
    Response::json(['ok' => true, 'plans' => $plans, 'payments' => []]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = Auth::requireRole('employer');
    $raw = json_decode(file_get_contents('php://input'), true) ?? [];
    $slug = $raw['plan_slug'] ?? 'basic';
    $plan = PaymentModel::planBySlug($slug);
    if (!$plan) {
        Response::json(['ok' => false, 'error' => 'Invalid plan'], 422);
    }
    $ref = 'MK-' . bin2hex(random_bytes(8));
    $pid = PaymentModel::createPayment($u['id'], (int) $plan['id'], (float) $plan['price'], 'completed', $ref);
    EmployerCreditsModel::addCredits($u['id'], (int) $plan['job_credits'], (int) $plan['featured_jobs']);
    PaymentModel::setStatus($pid, 'completed');
    Response::json(['ok' => true, 'payment_id' => $pid, 'reference' => $ref]);
}

Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
