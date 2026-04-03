<?php
/**
 * CHECKOUT.PHP — Gestion du paiement Stripe pour SoccerMidable
 * Version avec débuggage amélioré
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // Ne pas afficher les erreurs directement (casse le JSON)
ini_set('log_errors', 1);

// Log personnalisé pour Stripe
function stripe_log($msg) {
    file_put_contents('stripe_debug.log', date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once 'admin/db.php';

try {
    if (!file_exists('stripe-php/init.php')) {
        throw new Exception("Le dossier 'stripe-php' est manquant ou mal placé.");
    }
    require_once 'stripe-php/init.php';

    // Charger la configuration sécurisée
    if (!file_exists('config.php')) {
        throw new Exception("Le fichier de configuration 'config.php' est manquant.");
    }
    $config = require 'config.php';
    if (!isset($config['stripe_secret_key'])) {
        throw new Exception("Clé secrète Stripe non trouvée dans config.php.");
    }

    \Stripe\Stripe::setApiKey($config['stripe_secret_key']);

    // Récupération des données POST (JSON)
    $json = file_get_contents('php://input');
    stripe_log("Raw Input received: " . ($json ?: "EMPTY"));

    $data = json_decode($json);

    // Si le JSON est vide ou invalide, on essaie de voir si c'est du POST standard (fallback)
    if (!$data) {
        if (!empty($_POST)) {
            $data = (object)$_POST;
            stripe_log("Using fallback \$_POST data.");
        } else {
            $json_error = json_last_error_msg();
            throw new Exception("Données invalides ou JSON corrompu : $json_error");
        }
    }

    // Récupération des programmes et prix depuis la base de données
    $stmt = $pdo->query("SELECT name_fr, price_id_stripe FROM programs WHERE is_active = 1");
    $prices = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $prices[$row['name_fr']] = $row['price_id_stripe'];
    }

    if (empty($prices)) {
        throw new Exception("Aucun programme n'est configuré dans la base de données.");
    }

    $program = trim($data->program ?? '');
    stripe_log("Selected program: '$program'");

    if (empty($program) || !isset($prices[$program])) {
        // Tentative de matching flou si le matching exact échoue (gestion des espaces/accents)
        $foundPriceId = null;
        foreach ($prices as $key => $id) {
            if (mb_strtolower(trim($key)) === mb_strtolower($program)) {
                $foundPriceId = $id;
                break;
            }
        }
        
        if (!$foundPriceId) {
            throw new Exception("Programme non reconnu ou non sélectionné : '$program'");
        }
        $priceId = $foundPriceId;
    } else {
        $priceId = $prices[$program];
    }

    // Construction de l'URL de base pour les redirections
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $dir = dirname($_SERVER['PHP_SELF']);
    $baseUrl = $protocol . "://" . $host . rtrim(str_replace('\\', '/', $dir), '/');

    stripe_log("Base URL: $baseUrl");

    // Création de la session Stripe Checkout
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price' => $priceId,
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'customer_email' => !empty($data->email) ? $data->email : null,
        'success_url' => $baseUrl . '/index.html?payment=success',
        'cancel_url' => $baseUrl . '/index.html?payment=cancel',
        'metadata' => [
            'parent_name' => $data->parentName ?? 'Inconnu',
            'program'     => $program,
            'phone'       => $data->phone ?? 'Non précisé'
        ]
    ]);

    stripe_log("Session created successfully: " . $session->id);
    echo json_encode(['url' => $session->url]);

} catch (Exception $e) {
    stripe_log("ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
