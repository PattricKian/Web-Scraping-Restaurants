# Web-Scraping-Restaurants

## Úloha

(1) V okolí školy si nájdite troch rôznych poskytovateľov stravovacích služieb, ktorí predávajú obedové menu (napr. Delikanti poskytuje stravu aj na FEI STU a aj na PriF UK, keďže však ide o toho istého poskytovateľa, tak budeme uvažovať iba jednu z týchto dvoch prevádzok). Odporúčame vybrať si tiež zariadenia, ktorých menu sa dá programovo parsovať.

(2) Na stránke na overenie vytvorených metód API umiestnite okrem iného aj 3 tlačidlá: "Stiahni", "Rozparsuj" a "Vymaž".

(3) Po stlačení tlačidla "Stiahni" sa pomocou CURL-u z jednotlivých reštaurácií stiahnu stránky s aktuálnym menu a získané údaje sa uložia do databázy spolu s dátumom, keď sa menu stahovalo.

(4) Po stlačení tlačidla "Rozparsuj" sa zoberie posledný záznam z predchádzajúceho bodu, stiahnuté údaje sa rozparsujú a rozparsované údaje sa tiež uložia do databázy. Neukladajte duplicitné údaje. Údaje, ktoré si je potrebné získať a uložiť sú:

ponuka jedál,
cena, za ktorú si je možné jedlo zakúpiť (Tento bod je realizovateľný, iba ak daná stránka poskytuje túto informáciu. Ak však je táto informácia k dispozícii, tak tento bod je povinný.),
kde si je možné, ktoré jedlo zakúpiť,
pre ktorý deň daná ponuka platí,
ak ponuka obsahuje obrázok, tak stiahnite a uložte aj ten.
(5) Po stlačení tlačidla "Vymaž" sa v databáze vymažú tabuľky, ktoré uchovávajú všetky údaje týkajúce sa poskytovaného menu (rozparserované a aj nerozparserované, t.j. minimálne 2 tabuľky).
(6) Vytvorte API pre poskytovanie webovej služby ohľadom obedových menu v okolí fakulty. Jednotlivé metódy API nech umožňujú:
získať zoznam jedál spolu s dostupnými cenami pre aktuálny týždeň a všetky reštaurácie,
na základe zadaného dňa získať json s detailnými informáciami o jedlách, ktoré sú vtedy podávané (aj cena, miesto),
modifikovať cenu jedla,
vymazať ponuku vybranej reštaurácie z databázy spolu so všetkými údajmi, ktoré k nej prináležia,
vložiť nové jedlo do ponuky reštaurácie na celý aktuálny týždeň (napr. vyprážaný syr, ktorý sa zvykne ponúkať mimo štandardnej ponuky menu).
Webovú službu vytvorte pomocou jednej z nasledujúcich alternatív: XML-RPC, JSON-RPC, SOAP alebo REST. Pri zadaní sa bude kontrolovať, či funkcionalita stránky je robena naozaj pomocou zvolenej webovej služby. Pri REST službe si dajte záležať na tom, aby boli skutočne dodržané zásady RESTU.
(7) Nezabudnite správne odchytiť prípadné chyby, a posielať správny chybový stav (400, 404, atď.).
(8) Na tomu určenej podstránke popíšte API vytvorenej služby. V prípade, že vytvoríte WSDL dokument pre SOAP, tak stačí, keď namiesto ručného popisu API iba vizualizujete jednotlivé metódy služby pomocou nejakého voľne dostupného wsdl viewera. Kto však má záujem, môže ručne popísať API aj v tomto prípade. (Pre dokumentáciu API môžete použiť aj knižnicu, vďaka ktorej si bude možné jednotlivé endpointy aj vyskúšať.)
(9) Na základe vytvoreného API vytvorte web stránku, na ktorej prehľadným spôsobom zobrazíte jedálny lístok na aktuálny týždeň (nezabudnite uviesť dátumy). Na stránke budú uvedené všetky údaje z bodu 4 tohoto zadania. Pozor! Stránka nesmie byť zostavená tak, že najprv sa zobrazí jedálny lístok pre jednu reštauráciu, potom pre druhú a nakoniec pre tretiu. Je potrebné to skombinovať tak, aby sme si mohli pozrieť, čo v daný deň kde varia a na základe toho sa rozhodli, kde pôjdeme na obed. Layout stránky navrhujte tak, aby tam prípadne v budúcnosti mohli pribudnúť aj ďalšie reštaurácie.
(10) Umožnite zobrazenie jedálného lístka nie iba pre celý týždeň, ale aj pre vybraný deň.
(11) Na otestovanie všetkých vytvorených metód API vytvorte okrem zobrazeného jedálného lístka aj klientsku stranu aplikácie, ktorá umožní zadávať vstupné údaje vo forme formulára.

