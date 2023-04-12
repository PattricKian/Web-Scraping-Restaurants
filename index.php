<?php



require_once('config.php');

$delikantiURL = "https://www.delikanti.sk/prevadzky/3-jedalen-prif-uk/";
$eatURL = "http://eatandmeet.sk/tyzdenne-menu";
$freefoodURL = "http://www.freefood.sk/menu/#fayn-food";



$pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function getPageContent($pdo, $url, $name) {
    // Funkcia ktora pomocou cURL ulozi stranku definovanu v $url
    // a ulozi do databazy pod nazvom $name.

    // cURL inicializacia
    $ch = curl_init();

    // Konfiguracia cURL: zadam stranku, ktoru chcem parsovat a navratovy typ -> 1=string.
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Vykonanie cURL dopytu.
    $output = curl_exec($ch);

    // Slusne ukoncim a uvolnim cURL.
    curl_close($ch);

    // Vlozim obsah stranky do databazy.
    $sql = "INSERT INTO sites (name, html) VALUES (:name, :html)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->bindParam(":html", $output, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "Stranka ulozena.";
    } else {
        echo "Ups. Nieco sa pokazilo";
    }

    unset($stmt);
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


   

    


   
    
   
 




    $output = getMenuFromDB($pdo, "freefood");

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
            $date = trim($datum[1]) . " " . trim($datum[0]);
    
            // Iterujem cez ponuku dna.
            $items = array();
            foreach ($day->lastElementChild->childNodes as $ponuka) {
                // Ziskam si poradove cislo jedla, resp. pismeno P. urcuje polievku
                
                $cena = $ponuka->lastElementChild;
    
               
                
              
                
                $items[] = trim($ponuka->textContent);
            }
    
            $menu[] = array("date" => $date, "items" => $items);
        }
    }


?>




<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://code.jquery.com/jquery-3.6.0.js"
            integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
            crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T"
          crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
    <title>Jedálny lístok</title>
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
      
 
 

      

<!-- Monday Modal -->
<div class="modal fade" id="mondayModal" tabindex="-1" aria-labelledby="mondayModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content bg-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="mondayModalLabel">Pondelok</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="row">
    <div class="col">
        <?php
        echo "<h2>Delikanti</h2>";
        foreach ($foods as $food) {
            if ($food['day'] == 'Pondelok') { 
                echo "<h2>{$food['day']} ({$food['date']}) </h2>";
                echo "<ul>";
                foreach ($food['menu'] as $menu_item) {
                    echo "<li>" . $menu_item . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
        <?php
        echo "<h2>Eat&Meet</h2>";
        foreach ($jedla as $den) {
            if ($den['day'] == 'Pondelok' ) { 
                echo "<h2>{$den['day']} ({$den['date']}) </h2>";
                echo "<ul>";
                foreach ($den['menu'] as $jedlo) {
                    echo "<li>" . $jedlo . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
    <?php
echo "<h2>FreeFood</h2>";
foreach ($menu as $day) {
   
    if (strpos(strtolower($day['date']), 'pondelok') !== false) {
        echo "<h2>({$day['date']})</h2>";
        echo "<ul>";
        foreach ($day['items'] as $item) {
            echo "<li>" . $item ."</li>";
        }
        echo "</ul>";
    }
}
?>
    </div>
</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Tuesday Modal -->
<div class="modal fade" id="tuesdayModal" tabindex="-1" aria-labelledby="tuesdayModalLabel" aria-hidden="true">
<div class="modal-dialog modal-xl">
    <div class="modal-content bg-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="tuesdayModalLabel">Utorok</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="row">
    <div class="col">
        <?php
        echo "<h2>Delikanti</h2>";
        foreach ($foods as $food) {
            if ($food['day'] == 'Utorok') { 
                echo "<h2>{$food['day']} ({$food['date']}) </h2>";
                echo "<ul>";
                foreach ($food['menu'] as $menu_item) {
                    echo "<li>" . $menu_item . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
        <?php
        echo "<h2>Eat&Meet</h2>";
        foreach ($jedla as $den) {
            if ($den['day'] == 'Utorok' ) { 
                echo "<h2>{$den['day']} ({$den['date']}) </h2>";
                echo "<ul>";
                foreach ($den['menu'] as $jedlo) {
                    echo "<li>" . $jedlo . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
    <?php
echo "<h2>FreeFood</h2>";
foreach ($menu as $day) {
    
    if (strpos(strtolower($day['date']), 'utorok') !== false) {
        echo "<h2>({$day['date']})</h2>";
        echo "<ul>";
        foreach ($day['items'] as $item) {
            echo "<li>" . $item ."</li>";
        }
        echo "</ul>";
    }
}
?>
    </div>
</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


  <!-- Streda Modal -->
<div class="modal fade" id="wednesdayModal" tabindex="-1" aria-labelledby="wednesdayModalLabel" aria-hidden="true">
<div class="modal-dialog modal-xl">
    <div class="modal-content bg-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="wednesdayModalLabel">Streda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="row">
    <div class="col">
        <?php
        echo "<h2>Delikanti</h2>";
        foreach ($foods as $food) {
            if ($food['day'] == 'Streda') {
                echo "<h2>{$food['day']} ({$food['date']}) </h2>";
                echo "<ul>";
                foreach ($food['menu'] as $menu_item) {
                    echo "<li>" . $menu_item . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
        <?php
        echo "<h2>Eat&Meet</h2>";
        foreach ($jedla as $den) {
            if ($den['day'] == 'Streda' ) { 
                echo "<h2>{$den['day']} ({$den['date']}) </h2>";
                echo "<ul>";
                foreach ($den['menu'] as $jedlo) {
                    echo "<li>" . $jedlo . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
    <?php
echo "<h2>FreeFood</h2>";
foreach ($menu as $day) {
    
    if (strpos(strtolower($day['date']), 'streda') !== false) {
        echo "<h2>({$day['date']})</h2>";
        echo "<ul>";
        foreach ($day['items'] as $item) {
            echo "<li>" . $item ."</li>";
        }
        echo "</ul>";
    }
}
?>
    </div>
</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!-- stvrtok Modal -->
<div class="modal fade" id="thursdayModal" tabindex="-1" aria-labelledby="thursdayModalLabel" aria-hidden="true">
<div class="modal-dialog modal-xl">
    <div class="modal-content bg-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="thursdayModalLabel">Štvrtok</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="row">
    <div class="col">
        <?php
        echo "<h2>Delikanti</h2>";
        foreach ($foods as $food) {
            if ($food['day'] == 'Stvrtok') { 
                echo "<h2>{$food['day']} ({$food['date']}) </h2>";
                echo "<ul>";
                foreach ($food['menu'] as $menu_item) {
                    echo "<li>" . $menu_item . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
        <?php
        echo "<h2>Eat&Meet</h2>";
        foreach ($jedla as $den) {
            if ($den['day'] == 'Štvrtok' ) { 
                echo "<h2>{$den['day']} ({$den['date']}) </h2>";
                echo "<ul>";
                foreach ($den['menu'] as $jedlo) {
                    echo "<li>" . $jedlo . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
    <?php
echo "<h2>FreeFood</h2>";
foreach ($menu as $day) {
    // Check if the day is "pondelok"
    if (strpos(strtolower($day['date']), 'štvrtok') !== false) {
        echo "<h2>({$day['date']})</h2>";
        echo "<ul>";
        foreach ($day['items'] as $item) {
            echo "<li>" . $item ."</li>";
        }
        echo "</ul>";
    }
}
?>
    </div>
</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!-- PIATOK Modal -->
<div class="modal fade" id="fridayModal" tabindex="-1" aria-labelledby="fridayModalLabel" aria-hidden="true">
<div class="modal-dialog modal-xl">
    <div class="modal-content bg-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="fridayModalLabel">Piatok</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="row">
    <div class="col">
        <?php
        echo "<h2>Delikanti</h2>";
        foreach ($foods as $food) {
            if ($food['day'] == 'Piatok') { 
                echo "<h2>{$food['day']} ({$food['date']}) </h2>";
                echo "<ul>";
                foreach ($food['menu'] as $menu_item) {
                    echo "<li>" . $menu_item . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
        <?php
        echo "<h2>Eat&Meet</h2>";
        foreach ($jedla as $den) {
            if ($den['day'] == 'Piatok' ) {
                echo "<h2>{$den['day']} ({$den['date']}) </h2>";
                echo "<ul>";
                foreach ($den['menu'] as $jedlo) {
                    echo "<li>" . $jedlo . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
    <?php
echo "<h2>FreeFood</h2>";
foreach ($menu as $day) {
    
    if (strpos(strtolower($day['date']), 'piatok') !== false) {
        echo "<h2>({$day['date']})</h2>";
        echo "<ul>";
        foreach ($day['items'] as $item) {
            echo "<li>" . $item ."</li>";
        }
        echo "</ul>";
    }
}
?>
    </div>
</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!-- SOBOTA Modal -->
<div class="modal fade" id="sundayModal" tabindex="-1" aria-labelledby="sundayModalLabel" aria-hidden="true">
<div class="modal-dialog modal-xl">
    <div class="modal-content bg-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="sundayModalLabel">Nedeľa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="row">
    <div class="col">
        <?php
        echo "<h2>Delikanti</h2>";
        foreach ($foods as $food) {
            if ($food['day'] == 'Sobota') { 
                echo "<h2>{$food['day']} ({$food['date']}) </h2>";
                echo "<ul>";
                foreach ($food['menu'] as $menu_item) {
                    echo "<li>" . $menu_item . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
        <?php
        echo "<h2>Eat&Meet</h2>";
        foreach ($jedla as $den) {
            if ($den['day'] == 'Sobota' ) { 
                echo "<h2>{$den['day']} ({$den['date']}) </h2>";
                echo "<ul>";
                foreach ($den['menu'] as $jedlo) {
                    echo "<li>" . $jedlo . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
    <?php
echo "<h2>FreeFood</h2>";
foreach ($menu as $day) {
   
    if (strpos(strtolower($day['date']), 'sobota') !== false) {
        echo "<h2>({$day['date']})</h2>";
        echo "<ul>";
        foreach ($day['items'] as $item) {
            echo "<li>" . $item ."</li>";
        }
        echo "</ul>";
    }
}
?>
    </div>
</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<!-- nedala Modal -->
<div class="modal fade" id="saturdayModal" tabindex="-1" aria-labelledby="saturdayModalLabel" aria-hidden="true">
<div class="modal-dialog modal-xl">
    <div class="modal-content bg-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="saturdayModalLabel">Sobota</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="row">
    <div class="col">
        <?php
        echo "<h2>Delikanti</h2>";
        foreach ($foods as $food) {
            if ($food['day'] == 'Nedeľa') { 
                echo "<h2>{$food['day']} ({$food['date']}) </h2>";
                echo "<ul>";
                foreach ($food['menu'] as $menu_item) {
                    echo "<li>" . $menu_item . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
        <?php
        echo "<h2>Eat&Meet</h2>";
        foreach ($jedla as $den) {
            if ($den['day'] == 'Nedeľa' ) { 
                echo "<h2>{$den['day']} ({$den['date']}) </h2>";
                echo "<ul>";
                foreach ($den['menu'] as $jedlo) {
                    echo "<li>" . $jedlo . "</li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
    <div class="col">
    <?php
echo "<h2>FreeFood</h2>";
foreach ($menu as $day) {
    
    if (strpos(strtolower($day['date']), 'nedeľa') !== false) {
        echo "<h2>({$day['date']})</h2>";
        echo "<ul>";
        foreach ($day['items'] as $item) {
            echo "<li>" . $item ."</li>";
        }
        echo "</ul>";
    }
}
?>
    </div>
</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>





    
<h1 style="margin-top: 5vh;">
    Jedlo v okolí FEI STU
</h1>
<div class="container my-4 d-flex justify-content-center">
  <div class="btn-group" role="group" aria-label="Weekdays">
    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#mondayModal">Pondelok</button>
    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#tuesdayModal">Utorok</button>
    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#wednesdayModal">Streda</button>
    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#thursdayModal">Štvrtok</button>
    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#fridayModal">Piatok</button>
    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#saturdayModal">Sobota</button>
    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#sundayModal">Nedeľa</button>
  </div>
</div>
<div class="container" style="margin-top: 5vh;">
    

    <h2 style="margin-top: 12vh; margin-bottom: 5vh;">
        Rozpis celého týždňa
    </h1>

    <div class="row">
    <div class="col">
        <?php
         echo "<h2>Delikanti</h2>";
            foreach ($foods as $food) {
                echo "<h2>{$food['day']} ({$food['date']}) </h2>";
                echo "<ul>";
                foreach ($food['menu'] as $menu_item) {
                    echo "<li>" . $menu_item . "</li>";
                }
                echo "</ul>";
            }
        ?>
    </div>
    <div class="col">
        <?php
           echo "<h2>Eat&Meet</h2>";
            foreach ($jedla as $den) {
                echo "<h2>{$den['day']} ({$den['date']}) </h2>";
                echo "<ul>";
                foreach ($den['menu'] as $jedlo) {
                    echo "<li>" . $jedlo . "</li>";
                }
                echo "</ul>";
            }
        ?>
    </div>
    <div class="col">
        
        <?php
        echo "<h2>FreeFood</h2>";
            foreach ($menu as $day) {
                echo "<h2>({$day['date']})</h2>";
                echo "<ul>";
                foreach ($day['items'] as $item) {
                    echo "<li>" . $item ."</li>";
                }
                echo "</ul>";
            }
        ?>
    </div>
</div>







                
                
           
        </div>
    </div>
</div>




<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
</body>
</html>
