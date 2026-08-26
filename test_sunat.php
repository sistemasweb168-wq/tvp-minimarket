<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\SunatApiController;

$controller = new SunatApiController();

echo "--- TEST RUC 20100047218 ---\n";
$req1 = new Request(['documento' => '20100047218']);
print_r($controller->validarDocumento($req1)->getData(true));
