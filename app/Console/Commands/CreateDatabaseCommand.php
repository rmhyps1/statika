<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use Exception;

class CreateDatabaseCommand extends Command
{
    protected $signature = 'db:create';

    protected $description = 'Create the database if it does not exist';

    public function handle()
    {
        $database = env('DB_DATABASE', 'pklmagang');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');

        try {
            $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
            $pdo->exec($query);

            $this->info("Database '$database' checked/created successfully.");
        } catch (Exception $e) {
            $this->error("Failed to connect or create database: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
