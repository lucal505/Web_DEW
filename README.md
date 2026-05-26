<div align="center">

  <h1>Walter Pink Drugs</h1>
  
  <p>
    Site web pentru gestionarea și analiza datelor despre droguri oferite de ANA (Agenţia Naţională Antidrog) — infracționalitate, urgențe medicale, proiecte de prevenire antidrog și capturi de substanțe.
  </p>

</div>

<br />

<!-- Table of Contents -->
# :notebook_with_decorative_cover: Cuprins

- [Despre proiect](#star2-despre-proiect)
  * [Tech Stack](#space_invader-tech-stack)
  * [Funcționalități](#dart-functionalitati)
  * [Structura proiectului](#file_folder-structura-proiectului)
  * [Variabile de mediu](#key-variabile-de-mediu)
- [Cum pornești proiectul](#toolbox-cum-pornesti-proiectul)
  * [Cerințe prealabile](#bangbang-cerinte-prealabile)
  * [Instalare](#gear-instalare)
  * [Inițializarea bazei de date](#floppy_disk-initializarea-bazei-de-date)
- [Utilizare](#eyes-utilizare)
  * [Rute publice](#globe_with_meridians-rute-publice)
  * [Rute de administrare](#lock-rute-de-administrare)
- [Autentificare](#closed_lock_with_key-autentificare)
- [Import de date](#inbox_tray-import-de-date)
- [Securitate](#shield-securitate)
- [Licență](#warning-licenta)

<!-- About the Project -->
## :star2: Despre proiect

Aceasta este o aplicație de tip API REST construită fără framework-uri, folosind exclusiv tehnologii vanilla. Aplicația servește și procesează date statistice despre droguri, organizate în patru domenii principale: infracționalitate, urgențe medicale, prevenire și capturi.

Arhitectura urmează un model stratificat clasic: **Controllers → Services → Repositories**, cu separarea clară a responsabilităților. Datele transferate între straturi sunt încapsulate în obiecte DTO (Data Transfer Objects), iar accesul la baza de date se face exclusiv prin PDO cu prepared statements.

Frontend-ul se află în directorul `public/` și este construit în totalitate fără framework-uri — HTML, CSS și JavaScript vanilla. Acesta conține o pagină publică pentru vizualizarea și filtrarea datelor și o interfață separată de administrare care apelează rutele protejate ale API-ului.

<!-- TechStack -->
### :space_invader: Tech Stack

<details>
  <summary>Backend</summary>
  <ul>
    <li><a href="https://www.php.net/">PHP 8.2</a> — limbaj principal</li>
    <li><a href="https://www.sqlite.org/">SQLite</a> — bază de date</li>
    <li><a href="https://github.com/firebase/php-jwt">firebase/php-jwt</a> — autentificare pe baza token-urilor JWT</li>
    <li><a href="https://getcomposer.org/">Composer</a> — gestionarea dependențelor</li>
  </ul>
</details>

<details>
  <summary>Frontend</summary>
  <ul>
    <li>HTML</li>
    <li>CSS</li>
    <li>JavaScript vanilla</li>
  </ul>
</details>

<details>
  <summary>Infrastructură</summary>
  <ul>
    <li><a href="https://www.docker.com/">Docker</a> — containerizare</li>
    <li><a href="https://httpd.apache.org/">Apache</a> — server web (imaginea <code>php:8.2-apache</code>)</li>
  </ul>
</details>

Singura dependență externă a backend-ului este biblioteca `firebase/php-jwt`, instalată prin Composer în directorul `vendor/`. Aceasta este folosită pentru autentificarea stateless prin token-uri JWT. Tot restul codului este scris fără framework-uri sau biblioteci terțe.

<!-- Features -->
### :dart: Funcționalități

- Filtrare multicriterială a datelor stocate in baza de date
- Operații CRUD complete pe toate tabelele de date, protejate prin autentificare
- Autentificare stateless prin token-uri JWT
- Gestionarea conturilor de administrator (creare, schimbare parolă, ștergere)
- Import de date din fișiere CSV cu detectarea automată a tipului
- Export de date în format CSV și HTML
- Rutare REST cu un singur punct de intrare

### :file_folder: Structura proiectului

```
.
├── api/
│   ├── index.php          # punct unic de intrare, rutare
│   └── .htaccess          # direcționarea tuturor request-urilor către index.php
├── public/                # frontend — HTML, CSS, JavaScript vanilla
│   ├── index.html         # pagina publică de vizualizare a datelor
│   ├── admin.html         # interfața de administrare
│   ├── css/               # stiluri
│   ├── js/                # scripturi JavaScript 
│   └── assets/            # imagini și alte resurse statice
├── src/
│   ├── Config/            # conexiunea la baza de date
│   ├── Controllers/       # primesc request-uri, apelează service-uri
│   ├── Services/          # logica de business și validarea datelor
│   ├── Repositories/      # accesul la baza de date
│   ├── DTOs/              # obiecte de transfer al datelor
│   ├── Importers/         # importatoare CSV pentru fiecare categorie de tabel
│   └── Interfaces/        # interfețe (ex. ImporterInterface)
├── scripts/
│   ├── db_init.php        # creează tabelele
│   └── db_seed_admins.php # inserează contul de admin din .env
├── data/                  # directorul bazei de date SQLite
├── uploads/               # fișiere CSV încărcate
├── vendor/                # dependențe Composer (firebase/php-jwt)
├── docker-compose.yml
├── Dockerfile
└── .env                   # secrete și configurare
```

<!-- Env Variables -->
### :key: Variabile de mediu

Pentru a rula proiectul trebuie să creezi un fișier `.env` în rădăcina proiectului. Acest fișier **nu** este inclus în repository (este în `.gitignore`) pentru a nu expune secrete.

Conținutul `.env`:

```
JWT_SECRET=cheia_ta_secreta_de_minim_256_biti

ADMIN_USERNAME=username_admin
ADMIN_PASSWORD=parola_admin
```

`JWT_SECRET` este cheia folosită pentru semnarea token-urilor JWT. Trebuie să fie o valoare aleatorie, lungă (recomandat minim 256 de biți), alfanumerică. Oricine deține această cheie poate genera token-uri valide, deci trebuie păstrată secretă.

`ADMIN_USERNAME` și `ADMIN_PASSWORD` definesc contul de administrator creat la inițializarea bazei de date.

<!-- Getting Started -->
## :toolbox: Cum pornești proiectul

<!-- Prerequisites -->
### :bangbang: Cerințe prealabile

Singura cerință pe mașina locală este Docker. Toate celelalte componente (PHP, Apache, Composer, SQLite) rulează în interiorul containerului.

- [Docker](https://www.docker.com/) și Docker Compose

<!-- Installation -->
### :gear: Instalare

Clonează proiectul:

```bash
git clone <url-repository>
cd <numele-proiectului>
```

Creează fișierul `.env` în rădăcina proiectului conform secțiunii [Variabile de mediu](#key-variabile-de-mediu).

Pornește containerul:

```bash
docker compose up --build
```

La prima pornire, containerul instalează automat dependențele Composer și pornește serverul Apache. Aplicația va fi disponibilă la `http://localhost:8080`.

### :floppy_disk: Inițializarea bazei de date

După ce containerul rulează, creează tabelele și contul de administrator.

Creează schema bazei de date:

```bash
docker exec -it DEW_web bash -c "cd /var/www/html && php scripts/db_init.php"
```

Inserează contul de administrator definit în `.env`:

```bash
docker exec -it DEW_web bash -c "cd /var/www/html && php scripts/db_seed_admins.php"
```

Scriptul de seed folosește `INSERT OR IGNORE`, deci poate fi rulat de mai multe ori fără să producă erori sau duplicate.

<!-- Usage -->
## :eyes: Utilizare

Toate request-urile trec printr-un singur punct de intrare (`api/index.php`), iar fișierul `.htaccess` rescrie URL-urile către acesta. Structura unui URL este `/api/{secțiune}/{resursă}/{id}`.

### :globe_with_meridians: Rute publice

Rutele publice nu necesită autentificare. Sunt accesibile prin metoda `GET`.

| Metodă | Rută | Descriere |
| ------ | ---- | --------- |
| GET | `/api/filters/{resursă}` | Datele filtrate, cu paginare opțională |
| GET | `/api/options/{resursă}` | Opțiunile disponibile pentru filtre |
| GET | `/api/export/{resursă}?format=csv\|html` | Exportă datele în CSV sau HTML |

Resursele disponibile sunt: `emergencies`, `demographics`, `sentences`, `articles`, `general`, `groups`, `projects`, `campaigns`, `activities`, `seizures`.

Exemplu — datele despre urgențe filtrate după an:

```
GET /api/filters/emergencies?from=2020&to=2022
```

Exemplu — datele paginate:

```
GET /api/filters/seizures?page=1
```

### :lock: Rute de administrare

Rutele de administrare necesită un token JWT valid trimis în header-ul `Authorization`. Sunt grupate sub `/api/admin/`.

| Metodă | Rută | Descriere |
| ------ | ---- | --------- |
| POST | `/api/admin/{resursă}` | Creează o înregistrare |
| PATCH | `/api/admin/{resursă}/{id}` | Modifică o înregistrare |
| DELETE | `/api/admin/{resursă}/{id}` | Șterge o înregistrare |
| POST | `/api/admin/admins` | Creează un cont de administrator |
| PATCH | `/api/admin/admins` | Schimbă parola unui administrator |
| DELETE | `/api/admin/admins` | Șterge un administrator |
| POST | `/api/admin/import/upload` | Încarcă și importă un fișier CSV |
| POST | `/api/admin/import/all` | Importă toate fișierele din `uploads/` |
| DELETE | `/api/admin/wipe` | Golește toate tabelele de date |

La operațiile de modificare și ștergere, `id`-ul resursei se trimite în URL. Câmpurile de date se trimit în corpul request-ului ca JSON.

<!-- Authentication -->
## :closed_lock_with_key: Autentificare

Aplicația folosește autentificare stateless prin JSON Web Tokens. Nu se folosesc sesiuni PHP — serverul nu reține starea de autentificare între request-uri.

Procesul este următorul. Administratorul trimite credențialele la endpoint-ul de login:

```
POST /api/auth/login
```

Dacă credențialele sunt corecte, serverul răspunde cu un token JWT. Acest token are o durată de viață limitată (o oră) și conține identificatorul și numele administratorului.

Pentru orice request către o rută de administrare, token-ul trebuie inclus în header:

```
Authorization: Bearer <token>
```

Token-ul este semnat cu `JWT_SECRET` din `.env`, folosind algoritmul HS256. La fiecare request protejat, serverul verifică semnătura și expirarea token-ului înainte de a permite accesul.

<!-- Import -->
## :inbox_tray: Import de date

Datele pot fi importate din fișiere CSV. Tipul fișierului este detectat automat după numele acestuia, iar anul este extras tot din nume (fișierul trebuie să conțină un an din 4 cifre).

Importatorul corect este ales după un cuvânt cheie din numele fișierului:

| Cuvânt cheie în nume | Importator | Domeniu |
| -------------------- | ---------- | ------- |
| `capturi` | SeizureImporter | Capturi de droguri |
| `infractionalitate` | CrimeImporter | Infracționalitate |
| `proiecte` | PreventionImporter | Prevenire |
| `urgente` | EmergencyImporter | Urgențe medicale |

Importatoarele sunt tolerante la date incorecte. Rândurile invalide sau valorile negative sunt sărite, iar importul continuă cu restul datelor. La final, răspunsul indică numărul de înregistrări inserate efectiv. Conținutul fișierelor este convertit la UTF-8 pentru a păstra corect diacriticele.

<!-- Security -->
## :shield: Securitate

Aplicația aplică câteva tehnici de bază pentru prevenirea atacurilor uzuale.

Împotriva **SQL injection**, toate interogările folosesc PDO cu prepared statements și parametri legați. Nicio valoare provenită din request nu este concatenată direct în interogările SQL.

Împotriva **Cross-Site Scripting (XSS)**, exportul HTML trece toate valorile prin `htmlspecialchars` înainte de a le insera în pagină, astfel încât eventualul cod injectat este afișat ca text, nu executat. Răspunsurile API sunt în format JSON, care nu este interpretat ca HTML de către browser.

Accesul la operațiile de administrare este protejat prin autentificare JWT. Fișierul `.htaccess` blochează accesul direct la fișierele PHP, cu excepția punctului de intrare `index.php`. 

<!-- License -->
## :warning: Licență

Distribuit fără licență.
