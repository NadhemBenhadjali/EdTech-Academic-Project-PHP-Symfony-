<?php
// scripts/list_users.php
// Usage: php scripts/list_users.php

use App\Kernel;
use App\Entity\User;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Load env (so DATABASE_URL is available)
if (class_exists(Dotenv::class)) {
    (new Dotenv())->bootEnv(__DIR__ . '/../.env');
}

echo "PHP: " . phpversion() . PHP_EOL;
echo "APP_ENV: " . ($_SERVER['APP_ENV'] ?? ($_ENV['APP_ENV'] ?? 'n/a')) . PHP_EOL;
echo "DATABASE_URL env: " . ($_SERVER['DATABASE_URL'] ?? ($_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL') ?: 'n/a')) . PHP_EOL;

try {
    $env = $_SERVER['APP_ENV'] ?? ($_ENV['APP_ENV'] ?? 'dev');
    $debug = (bool) ($_SERVER['APP_DEBUG'] ?? ($_ENV['APP_DEBUG'] ?? ('dev' !== $env)));

    echo "Booting kernel (env={$env}, debug=" . ($debug ? '1' : '0') . ")..." . PHP_EOL;
    $kernel = new Kernel($env, $debug);
    $kernel->boot();

    $container = $kernel->getContainer();
    if (!$container) {
        echo "No container available\n";
        exit(1);
    }

    if (!$container->has('doctrine')) {
        echo "Container doesn't have 'doctrine' service\n";
        exit(1);
    }

    $doctrine = $container->get('doctrine');
    $em = $doctrine->getManager();

    echo "Connected to DB via Doctrine. Running query..." . PHP_EOL;

    /** @var User[] $users */
    $users = $em->getRepository(User::class)->findAll();

    if (!$users) {
        echo "No users found.\n";
        $kernel->shutdown();
        exit(0);
    }

    foreach ($users as $u) {
        $roles = $u->getRoles();
        echo sprintf("%d | %s | %s | %s\n", $u->getId(), $u->getEmail(), $u->getName() ?? '', json_encode($roles));
    }

    $kernel->shutdown();
} catch (Throwable $e) {
    echo "ERROR: " . get_class($e) . ": " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
