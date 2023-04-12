<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once('config.php');
$pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);


$restaurant = isset($_GET['restaurant']) ? $_GET['restaurant'] : '';
$day = isset($_GET['daySelected']) ? $_GET['daySelected'] : '';


$sql = 'SELECT * FROM `sites_parsed`';
$where = array();
$params = array();

if ($restaurant) {
    $where[] = '`place` = ?';
    $params[] = $restaurant;
}

if ($day) {
    $where[] = '`day` = ?';
    $params[] = $day;
}

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}


$stmt = $pdo->prepare($sql);
$stmt->execute($params);


$results = $stmt->fetchAll(PDO::FETCH_ASSOC);


echo json_encode($results);





if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
   
    $requestData = json_decode(file_get_contents('php://input'), true);
    $restaurantName = $requestData['restaurant'];

    
    $stmt = $pdo->prepare("DELETE FROM `sites_parsed` WHERE `place` = ?");
    $stmt->execute([$restaurantName]);

   
    $rowCount = $stmt->rowCount();
    if ($rowCount > 0) {
        http_response_code(200);
        echo json_encode(array("message" => "Restaurant deleted successfully."));
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Restaurant not found."));
    }
} else {
    
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed."));
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  
    $data = json_decode(file_get_contents('php://input'), true);

    
    if (!isset($data['place']) || !isset($data['text'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing required parameters']);
        exit;
    }

    $pdo->beginTransaction();
    try {

        
        $stmt = $pdo->prepare('UPDATE sites_parsed SET menu = CONCAT(menu, :text) WHERE place = :place');
        
       
        $stmt->bindParam(':text', $data['text'], PDO::PARAM_STR);
        $stmt->bindParam(':place', $data['place'], PDO::PARAM_STR);
        
        
        $stmt->execute();

       
        $pdo->commit();

        
        echo json_encode(['message' => 'Menu updated successfully']);

    } catch (PDOException $e) {

        
        $pdo->rollBack();

        
        http_response_code(500);
        echo json_encode(['message' => $e->getMessage()]);
    }

} else {

    
    http_response_code(405);
    echo json_encode(['message' => 'Invalid HTTP method']);

}

?>




	
