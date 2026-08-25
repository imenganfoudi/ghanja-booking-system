# 💇‍♀️ Ghanja — Booking Reservation System

Système de réservation en ligne pour salon de beauté, développé avec **Laravel**. Permet aux clients de réserver un rendez-vous en ligne, et aux administrateurs de gérer les rendez-vous, services et employés depuis un panneau d'administration complet.

<!--
  📸 Ajouter ici des screenshots du projet :
  ![Page d'accueil](docs/screenshots/home.png)
  ![Réservation](docs/screenshots/booking.png)
  ![Admin dashboard](docs/screenshots/admin-dashboard.png)
-->

🔗 **Démo live :** _à venir_

---

## ✨ Fonctionnalités

### Côté client

- Page d'accueil avec présentation du salon et des services
- Réservation en ligne avec sélection de service, employé, date et créneau horaire
- Détection automatique des créneaux disponibles (en temps réel, sans conflit)
- Confirmation de réservation par email (protégée par lien signé, non-devinable)
- Inscription à la newsletter

### Côté administration

- Dashboard avec vue d'ensemble
- Gestion complète des rendez-vous (filtrage par statut, mise à jour, suppression)
- Gestion des services (ajout, modification, suppression, activation/désactivation)
- Gestion des employés (staff)
- Notification par email à chaque nouvelle réservation

### Sécurité

- Authentification avec [Laravel Breeze](https://laravel.com/docs/starter-kits#laravel-breeze)
- Autorisation basée sur les rôles (admin / utilisateur standard)
- Protection contre les doubles réservations (transactions + row locking)
- Liens de confirmation signés (protection contre l'accès non autorisé aux données d'autres clients)
- Rate limiting sur les formulaires sensibles (réservation, disponibilités)
- Validation stricte des données côté serveur

---

## 🛠️ Stack technique

| Catégorie        | Technologie          |
| ---------------- | -------------------- |
| Framework        | Laravel              |
| Authentification | Laravel Breeze       |
| Base de données  | MySQL                |
| Frontend         | Blade + Tailwind CSS |
| Emails           | Laravel Mail (SMTP)  |

---

## 🚀 Installation locale

### Prérequis

- PHP >= 8.2
- Composer
- MySQL
- Node.js & npm

### Étapes

```bash
# 1. Cloner le dépôt
git clone https://github.com/imenganfoudi/ghanja-booking-system.git
cd ghanja-booking-system

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances front-end
npm install
npm run build

# 4. Configurer l'environnement
cp .env.example .env
php artisan key:generate
```

### Configurer la base de données

Dans le fichier `.env`, renseigner vos identifiants MySQL :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_reservation
DB_USERNAME=root
DB_PASSWORD=
```

Puis lancer les migrations :

```bash
php artisan migrate
```

### Configurer l'envoi d'emails (optionnel)

Dans `.env`, renseigner un compte SMTP (ex: Gmail avec un [mot de passe d'application](https://myaccount.google.com/apppasswords)) :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD="xxxx xxxx xxxx xxxx"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="votre-email@gmail.com"
MAIL_FROM_NAME="Ghanja"

ADMIN_EMAIL=admin@example.com
```

### Lancer le serveur

```bash
php artisan serve
```

Le site est accessible sur `http://127.0.0.1:8000`.

---

## 📁 Structure du projet

```
app/
├── Http/Controllers/
│   ├── AppointmentController.php       # Réservation côté client
│   └── Admin/                          # Contrôleurs du panneau admin
├── Models/
│   ├── Appointment.php
│   ├── Service.php
│   ├── Staff.php
│   └── Subscriber.php
resources/views/
├── front/                              # Vues publiques (accueil, réservation)
└── admin/                              # Vues du panneau d'administration
routes/
└── web.php
```

---

## 🔒 Points de sécurité implémentés

Ce projet a fait l'objet d'une revue de sécurité couvrant notamment :

- **Race conditions** : verrouillage des lignes (`lockForUpdate`) dans une transaction lors de la création d'un rendez-vous, empêchant deux réservations simultanées sur le même créneau.
- **IDOR (Insecure Direct Object Reference)** : la page de confirmation de rendez-vous est protégée par une URL signée Laravel, empêchant un utilisateur de consulter les données d'un rendez-vous qui n'est pas le sien en modifiant simplement l'identifiant dans l'URL.
- **Rate limiting** : limitation du nombre de requêtes sur les endpoints de réservation pour prévenir les abus.
- **Validation stricte** : formats de données validés côté serveur (dates, heures, emails).

---

## 📄 Licence

Projet développé à des fins d'apprentissage / portfolio.
