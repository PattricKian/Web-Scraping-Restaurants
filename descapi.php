<!DOCTYPE html>
<html>
<head>
    <title>Popis API</title>
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
<div class="container mt-5 text-center">
    <h1>Popis API</h1>
    <p>Všetky API boli testované pomocou aplikácie Postman via link : https://site36.webte.fei.stuba.sk/restaurant/api.php <p>

    <p>1. Pomocou tejto API vieme:</p>

    <li>získať zoznam jedál a ceny pre všetky reštaurácie</li>
    <li>na základe zadaného dňa získať informácie ohľadom menu</li>
    <li>vymazať ponuku vybranej reštaurácie spolu so všetkými súvisiacimi údajmi</li>
    <li>vložiť nové jedlo do ponuky vybranej reštaurácie na celý týždeň</li>
    
<br>
    <p>2. Metody API:</p>
    <li>GET: Táto metóda sa používa na získanie informácií o menu v reštaurácii pre konkrétny deň alebo celkovo. Parametre sa predávajú cez query string (restaurant a daySelected). Výsledkom požiadavky je JSON objekt obsahujúci všetky záznamy z tabuľky sites_parsed, ktoré vyhovujú zadaným parametrom. </li>
    <li>DELETE: Táto metóda slúži na vymazanie záznamu o menu pre konkrétnu reštauráciu. Záznam na vymazanie sa získava z JSON payloadu, ktorý sa posiela s požiadavkou. V prípade úspešného vymazania, server vráti HTTP status kód 200 a JSON objekt obsahujúci správu o úspešnom vymazaní, v opačnom prípade vráti HTTP status kód 404 s správou o nájdení záznamu.</li>
    <li>POST: Táto metóda slúži na aktualizáciu menu v reštaurácii. JSON payload obsahuje dva povinné parametre - place (názov reštaurácie) a text (text aktualizácie). V prípade, že sú niektoré z týchto parametrov chýbajúce, server vráti HTTP status kód 400 a JSON objekt s chybovou správou. Ak sú všetky parametre prítomné, server aktualizuje záznam v databáze a vráti HTTP status kód 200 a JSON objekt s potvrdením o úspešnej aktualizácii. V prípade akýchkoľvek chýb pri aktualizácii, server vráti HTTP status kód 500 a JSON objekt s chybovou správou.</li>
    

</div>


	<!-- Bootstrap JS -->
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

</body>
</html>