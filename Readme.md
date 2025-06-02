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