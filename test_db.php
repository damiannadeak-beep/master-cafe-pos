<?php
try {
    $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=master_cafe_pos', 'postgres', 'root');
    $stmt = $pdo->query("SELECT column_name, is_nullable FROM information_schema.columns WHERE table_name = 'pesanan' AND column_name = 'id_meja'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
