# EVE - Gewiss Gatepass Interface

Webes felület alvállalkozói kapcsolattartók számára, akik regisztrálandó személyeket rögzítenek, amit a helyi Gewiss Gatepass szerver API-n keresztül tölt be és importál. PHP 8.2+ MVC alap MySQL, SMTP, session autentikáció, JWT API, routing és Tailwind CSS támogatással.

## Követelmények

- PHP 8.2+ (`pdo_mysql`, `mbstring`)
- Composer
- Node.js 20+ / npm
- MySQL 8+ vagy MariaDB
- Apache `mod_rewrite`, Nginx, vagy a PHP beépített szervere

## Telepítés

```bash
cp .env.example .env
composer install
npm install
npm run build
```

Hozd létre az adatbázist, töltsd ki a `.env` DB mezőit, majd:

```bash
composer migrate
composer serve
```

Nyisd meg: `http://localhost:8000`

## API használat

Token kérése:

```bash
curl -X POST http://localhost:8000/api/token \
  -H 'Content-Type: application/json' \
  -d '{"email":"user@example.com","password":"very-secure-password"}'
```

Védett végpont:

```bash
curl http://localhost:8000/api/me \
  -H 'Authorization: Bearer YOUR_TOKEN'
```

## SMTP példa

```php
use App\Services\MailService;

(new MailService())->send(
    'user@example.com',
    'Teszt levél',
    '<h1>Sikeres SMTP kapcsolat</h1>'
);
```

## Éles környezet

- A webserver document rootja kizárólag a `public/` könyvtár legyen.
- `APP_DEBUG=false`, `SESSION_SECURE=true` és HTTPS szükséges.
- Az `APP_KEY` és `JWT_SECRET` legyen különálló, legalább 32 bájtos véletlen titok.
- A `storage/` legyen írható a PHP futtató felhasználónak, de ne legyen publikus.
- A fájlalapú rate limiter egygépes starter megoldás; több példányhoz Redis ajánlott.
- A JWT visszavonás nincs beépítve; rövid TTL-t és szükség esetén token blacklistet használj.
- Futtasd rendszeresen: `composer audit` és `npm audit`.
