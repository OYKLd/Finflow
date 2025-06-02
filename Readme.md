finflow/
├── config/
│   ├── bundles.php
│   ├── packages/
│   └── routes/
├── migrations/
├── public/
│   ├── assets/
│   └── index.php
├── src/
│   ├── Controller/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── TransactionController.php
│   │   └── UserController.php
│   ├── Entity/
│   │   ├── User.php
│   │   └── Transaction.php
│   ├── Form/
│   │   ├── LoginFormType.php
│   │   ├── RegistrationFormType.php
│   │   └── TransactionFormType.php
│   ├── Repository/
│   │   ├── UserRepository.php
│   │   └── TransactionRepository.php
│   ├── Security/
│   │   └── LoginAuthenticator.php
│   └── Service/
│       ├── ReportService.php
│       └── ExportService.php
├── templates/
│   ├── base.html.twig
│   ├── dashboard/
│   │   └── index.html.twig
│   ├── security/
│   │   ├── login.html.twig
│   │   └── register.html.twig
│   └── transaction/
│       ├── add.html.twig
│       ├── edit.html.twig
│       └── list.html.twig
├── translations/
├── var/
├── vendor/
├── .env
├── composer.json
├── README.md
└── symfony.lock

- **Installer les dépendances nécessaires** :
  - Doctrine ORM (pour la gestion de la base de données)
  - Twig (pour le rendu des vues)
  - Symfony Security Bundle (pour la gestion de l'authentification)
  - Symfony Form (pour la gestion des formulaires)
  - Chart.js (pour les graphiques)
  - SwiftMailer (pour l'envoi d'emails)
  ```bash
  composer require symfony/orm-pack symfony/security-bundle symfony/twig-bundle symfony/form symfony/validator symfony/webpack-encore-bundle
  composer require symfony/swiftmailer-bundle
  composer require symfony/monolog-bundle
  composer require symfony/asset
