# Maturitní práce

## Instalace

### WAMP
#### Požadavky
- WAMP
- git

#### Naklonování projektu
- Ve složce kde se nachází webové soubory WAMPu (typicky C:\wamp64\www) otevřete příkazový řádek a pomocí příkazu `git clone` naklonujte repozitář
- `git clone https://github.com/adamne-tis/maturita`

#### Vytvoření databáze
##### Způsob 1 (phpMyAdmin)
- V phpMyAdmin na kartě `Import` vyberte soubor `create.sql` ze složky `sql` a klikněte na tlačítko `Import`
- Pokud vyžadujete i testovací data, opakujte postup a vyberte soubor `mock_data.sql`

##### Způsob 2 (CLI program mariadb)
- Ve složce s instalací MariaDB otevřete příkazový řádek a spusťte příkaz `mariadb -u root`
- Napište příkaz `source <cesta k souboru create.sql>`
- Příklad: `source C:\wamp64\www\maturita\sql\create.sql`
- Stejným postupem importujte i soubor `mock_data.sql`

#### Nastavení spojení s databází
- Upravte potřebné proměnné v souboru `db_cfg.php`
- Pro výchozí instalaci WAMP je nastavení následující:
```php
$server = "localhost";
$user = "root";
$password = "";
$database = "maturita";
$port = 3307;
```

Po dokončení všech kroků bude stránka dostupná na adrese http://localhost/maturita/src/index.php

### Docker / Podman
- Zkopírujte soubor `db.env.example`, upravte potřebné proměnné a uložte soubor jako `db.env`.
- Pomocí příkazu `docker compose up -d` nebo `podman compose up -d` spusťte projekt.
- Otevřete stránku http://localhost:8000