<?php

;

require_once('config.php');

$delikantiURL = "https://www.delikanti.sk/prevadzky/3-jedalen-prif-uk/";
$eatURL = "http://eatandmeet.sk/tyzdenne-menu";
$freefoodURL = "http://www.freefood.sk/menu/#fayn-food";


$pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function getPageContent($pdo, $url, $name) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $output = curl_exec($ch);

    curl_close($ch);

    $created_at = date('Y-m-d H:i:s'); // current date and time

    $sql = "INSERT INTO sites (name, html, created_at) VALUES (:name, :html, :created_at)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->bindParam(":html", $output, PDO::PARAM_STR);
    $stmt->bindParam(":created_at", $created_at, PDO::PARAM_STR);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }

    unset($stmt);
}









function deleteAllPages($pdo) {
    $sql = "DELETE sites, sites_parsed FROM sites INNER JOIN sites_parsed";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }

    unset($stmt);
}
if(isset($_POST['stiahni'])) {
    $result1 = getPageContent($pdo, $delikantiURL, "delikanti");
    $result2 = getPageContent($pdo, $eatURL, "eat");
    $result3 = getPageContent($pdo, $freefoodURL, "freefood");
    
    if ($result1 && $result2 && $result3) {
        echo "<script>alert('Successful');</script>";
        header("Location: https://site36.webte.fei.stuba.sk/restaurant/checkapi.php");
        exit();
    } else {
        echo "Ups. Nieco sa pokazilo";
    }
}

if(isset($_POST['vymaz'])) {
    $result = deleteAllPages($pdo);
    
    if ($result) {
        echo "<script>alert('Successful');</script>";
        header("Location: https://site36.webte.fei.stuba.sk/restaurant/checkapi.php");
        exit();
    } else {
        echo "Ups. Nieco sa pokazilo";
    }
}


function getMenuFromDB($pdo, $name) {
    // Funkcia ziska html obsah z databazy.
    $page_content = "";
    $sql = "SELECT html FROM sites WHERE name = :name LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        // Uzivatel existuje, skontroluj heslo.
        $row = $stmt->fetch();
        $page_content = $row["html"];
    } else {
        echo "Nenachadza sa v tabulke alebo je duplicitne.";
    }

    return $page_content;

}


if (isset($_POST['parse'])) {
    


    
     
    
    
    $output = getMenuFromDB($pdo, "delikanti");

$dom = new DOMDocument();
@$dom->loadHTML($output);
$dom->preserveWhiteSpace = false;

$tables = $dom->getElementsByTagName('table');

$rows = $tables->item(0)->getElementsByTagName('tr');
$index = 0;
$dayCount = 0;

$foods = [];
$foodCount = $rows->item(0)->getElementsByTagName('th')->item(0)->getAttribute('rowspan');

foreach ($rows as $row) {
    $th = $row->getElementsByTagName('th')->item(0);

    if ($th) {
        $foodCount = $th->getAttribute('rowspan');

        $dayNode = $th->getElementsByTagName('strong')->item(0);

        if ($dayNode) {
            $day = trim($dayNode->nodeValue);

            foreach ($th->childNodes as $node) {
                if (!($node instanceof \DomText)) {
                    $node->parentNode->removeChild($node);
                }
            }

            $date = trim($th->nodeValue);

            array_push($foods, ["date" => $date, "day" => $day, "place" => "Delikanti", "menu" => []]);

            for ($i = $index; $i < $index + intval($foodCount); $i++) {
                $td = $rows->item($i)->getElementsByTagName('td')->item(1);

                if ($td) {
                    if ($foods[$dayCount]) {
                        array_push($foods[$dayCount]["menu"], trim($td->nodeValue));
                    }
                }
            }

            $index += intval($foodCount);
            $dayCount++;
        }
    }
}

$insertStmt = $pdo->prepare("INSERT INTO sites_parsed (date, day, place, menu) VALUES (:date, :day, :place, :menu)");

// Iterate through each day's menu and execute the insert statement
foreach ($foods as $menu_item) {
    $insertStmt->execute([
        'date' => $menu_item['date'],
        'day' => $menu_item['day'],
        'place' => "delikanti",
        'menu' => implode("\n", $menu_item['menu'])
    ]);
}
    
    
    
    
    
    
    
    
    
    
    
    $output = getMenuFromDB($pdo, "eat");
    

    $dom = new DOMDocument();

    @$dom->loadHTML($output);
    $dom->preserveWhiteSpace = false;

    $parseNodes = ["day-1", "day-2", "day-3", "day-4", "day-5", "day-6", "day-7",];

    $jedla = [
        ["date" => date( 'd.m.Y', strtotime( 'monday this week' ) ), "day" => "Pondelok", "place" => "Eat&Meet", "menu" => []],
        ["date" => date( 'd.m.Y', strtotime( 'tuesday this week' ) ), "day" => "Utorok", "place" => "Eat&Meet", "menu" => []],
        ["date" => date( 'd.m.Y', strtotime( 'wednesday this week' ) ), "day" => "Streda", "place" => "Eat&Meet", "menu" => []],
        ["date" => date( 'd.m.Y', strtotime( 'thursday this week' ) ), "day" => "Štvrtok", "place" => "Eat&Meet", "menu" => []],
        ["date" => date( 'd.m.Y', strtotime( 'friday this week' ) ), "day" => "Piatok", "place" => "Eat&Meet", "menu" => []],
        ["date" => date( 'd.m.Y', strtotime( 'saturday this week' ) ), "day" => "Sobota", "place" => "Eat&Meet", "menu" => []],
        ["date" => date( 'd.m.Y', strtotime( 'sunday this week' ) ), "day" => "Nedeľa", "place" => "Eat&Meet", "menu" => []],
    ];

    foreach ($parseNodes as $index => $nodeId) {

        $node = $dom->getElementById($nodeId);

        foreach ($node->childNodes as $menuItem)
        {
            if($menuItem && $menuItem->childNodes->item(1) && $menuItem->childNodes->item(1)->childNodes->item(3)){
                $nazov = trim($menuItem->childNodes->item(1)->childNodes->item(3)->childNodes->item(1)->childNodes->item(1)->nodeValue);
                $cena = trim($menuItem->childNodes->item(1)->childNodes->item(3)->childNodes->item(1)->childNodes->item(3)->nodeValue);
                $popis = trim($menuItem->childNodes->item(1)->childNodes->item(3)->childNodes->item(3)->nodeValue);
                array_push($jedla[$index]["menu"], "$nazov ($popis): $cena");
            }
        }
    }

    $finder = new DomXPath($dom);
    $classname="mb-20";
    $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

    $foodsWeekly = [];

    for ($i = 0; $i < count($nodes); $i++) {
        $node = $nodes[$i];
        for ($x = 1; $x < $node->childNodes->item(1)->childNodes->item(1)->childNodes->count(); $x = $x + 2) {
            $nazov = $node->childNodes->item(1)->childNodes->item(1)->childNodes->item($x)->childNodes->item(1)->childNodes->item(3)->childNodes->item(1)->childNodes->item(1)->nodeValue;
            $cena = $node->childNodes->item(1)->childNodes->item(1)->childNodes->item($x)->childNodes->item(1)->childNodes->item(3)->childNodes->item(1)->childNodes->item(3)->nodeValue;
            $foodsWeekly["menu"][] = "$nazov: $cena";
        }
    }

    for($i = 0; $i < count($jedla); $i++) {
        $jedla[$i]["menu"] = array_merge($jedla[$i]["menu"],$foodsWeekly["menu"]);
    }

    
    $insertStmt = $pdo->prepare("INSERT INTO sites_parsed (date, day, place, menu) VALUES (:date, :day, :place, :menu)");

// Iterate through each day's menu and execute the insert statement
foreach ($jedla as $menu) {
    $insertStmt->execute([
        'date' => $menu['date'],
        'day' => $menu['day'],
        'place' => $menu['place'],
        'menu' => implode("\n", $menu['menu']) // Convert the menu array to a string
    ]);
} 
 








 

$output = getMenuFromDB($pdo, "freefood");
$dom = new DOMDocument();
$dom->loadHTML($output);
$xpath = new DOMXPath($dom);

// Pomocou xpath viem ziskat aj elementy podla atributov a teda aj podla triedy
$menu_lists = $xpath->query('//ul[contains(@class, "daily-offer")]');
// Stranka poskytuje menu pre 3 restauracie teda su tam aj 3x daily-offer zoznamy.
$fayn_food = $menu_lists[1];

$menu = array();
foreach ($fayn_food->childNodes as $day) {
    // Nezaujima ma DOMText, iba prvok typu DOMElement.
    if ($day->nodeType === XML_ELEMENT_NODE) {
        // Ziskam si datum a rozdelim ho na den a datum, kedze tieto dva su oddelene ciarkou.
        $datum = explode(',', $day->firstElementChild->textContent);
        $date = trim($datum[1]);
        $day_of_week = trim($datum[0]);

        // Iterujem cez ponuku dna.
        $items = array();
        foreach ($day->lastElementChild->childNodes as $ponuka) {
            // Ziskam si poradove cislo jedla, resp. pismeno P. urcuje polievku
            $cena = $ponuka->lastElementChild;

            $items[] = trim($ponuka->textContent);
        }

        $menu[] = array("date" => $date, "day_of_week" => $day_of_week, "items" => $items);
    }
}

$insertStmt = $pdo->prepare("INSERT INTO sites_parsed (date, day, place, menu) VALUES (:date, :day, :place, :menu)");

// Iterate through each day's menu and execute the insert statement
foreach ($menu as $menu_item) {
    $insertStmt->execute([
        'date' => $menu_item['date'],
        'day' => $menu_item['day_of_week'],
        'place' => "freefood", 
        'menu' => implode("\n", $menu_item['items']) 
    ]);
}







}





    
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vytvorenie API metod</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
</head>

<body>

<div class="hamburger-menu">
    <input id="menu__toggle" type="checkbox" />
    <label class="menu__btn" for="menu__toggle">
      <span></span>
    </label>

    <ul class="menu__box">
        <li><a class="menu__item" href="index.php">Jedálny lístok</a></li>
        <li><a class="menu__item" href="checkapi.php">Overenie API</a></li>
        <li><a class="menu__item" href="descapi.php">Popis API</a></li>
    </ul>
</div>

<div class="container d-flex justify-content-center">
    <div class="row">
        <div class="col-md-4 ">
            <form method="POST" id="stiahni-form">
                <button type="submit" class="btn btn-primary" id="stiahni-btn" name="stiahni">Stiahni</button>
            </form>
        </div>
        <div class="col-md-4">
        <form method="POST" id="stiahni-form">
                <button type="submit" class="btn btn-primary" id="parsuj-btn" name="parse">Parsuj</button>
            </form>
        </div>
        <div class="col-md-4"> 
        <form method="POST" id="vymaz-form">
                <button type="submit" class="btn btn-primary" id="vymaz-btn" name="vymaz">Vymaz</button>
            </form>
        </div>
    </div>
</div>



<div class="container mt-5">
  <div class="row">
    <div class="col-md-3">
      <form id="updateMenuForm" class="my-form">
        <div class="form-group">
          <h2>Updatuj menu</h2>
          <label for="place">Place:</label>
          <input type="text" id="place" name="place" class="form-control">
        </div>
        <div class="form-group">
          <label for="text">Text:</label>
          <textarea id="text" name="text" class="form-control"></textarea>
        </div>
        <input type="submit" value="Update Menu" class="btn btn-success">
      </form>
    </div>
    <div class="col-md-3">
      <form id="delete-form" class="my-form">
        <h2>Zmazať reštauráciu</h2>
        <div class="form-group">
          <label for="restaurant">Názov reštaurácie:</label>
          <input type="text" id="restaurant" name="restaurant" class="form-control" required>
        </div>
        <input type="submit" value="Zmazať reštauráciu" class="btn btn-danger">
      </form>
    </div>
    <div class="col-md-3">
      <h2>Zobraz menu pre deň</h2>
      <form action="https://site36.webte.fei.stuba.sk/restaurant/api.php" method="get" class="my-form">
        <label for="daySelected">Vyberte deň:</label>
        <select id="daySelected" name="daySelected" class="form-control">
          <option value="Pondelok">Pondelok</option>
          <option value="Utorok">Utorok</option>
          <option value="Streda">Streda</option>
          <option value="Štvrtok">Štvrtok</option>
          <option value="Piatok">Piatok</option>
        </select>
        <br>
        <input type="submit" value="Zobraziť menu" class="btn btn-primary">
      </form>
    </div>
    <div class="col-md-3">
      <h2>Zobraz celé menu</h2>
      <form method="get" action="https://site36.webte.fei.stuba.sk/restaurant/api.php" class="my-form">
        <label for="daySelected">Stlačte tlačidlo a zobrazte celkové menu</label>
        <input type="hidden" name="all" value="1">
        <input type="submit" value="Zobraziť celé menu" class="btn btn-primary">
      </form>
    </div>
  </div>
</div>



<script>
    const form = document.getElementById('delete-form');

form.addEventListener('submit', async (event) => {
  event.preventDefault();

  const restaurantName = form.elements.restaurant.value;

  const response = await fetch('https://site36.webte.fei.stuba.sk/restaurant/api.php', {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      restaurant: restaurantName
    })
  });

  const data = await response.json();

  if (response.ok) {
    console.log(data.message);
    // display success message to user
  } else {
    console.error(data.message);
    // display error message to user
  }
});
</script>

	<script>
		document.getElementById("updateMenuForm").addEventListener("submit", function(event) {
			event.preventDefault();

			var xhr = new XMLHttpRequest();
			xhr.open("POST", "https://site36.webte.fei.stuba.sk/restaurant/api.php");

			xhr.onreadystatechange = function() {
				if (xhr.readyState === XMLHttpRequest.DONE) {
					var response = JSON.parse(xhr.responseText);
					var resultDiv = document.getElementById("updateMenuResult");

					if (xhr.status === 200) {
						resultDiv.innerHTML = "Menu updated successfully";
					} else {
						resultDiv.innerHTML = response.message;
					}
				}
			};

			var formData = new FormData(this);
			xhr.send(JSON.stringify(Object.fromEntries(formData.entries())));
		});
	</script>
	<!-- Bootstrap JS -->
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

</body>
</html>