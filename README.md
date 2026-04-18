# Laravel DevGuard 🛡️

[](https://www.google.com/search?q=https://packagist.org/packages/niduranga/laravel-dev-guard)
[](https://www.google.com/search?q=https://packagist.org/packages/niduranga/laravel-dev-guard)
[](LICENSE.md)

**Laravel DevGuard** is an AI-powered TDD companion designed specifically for the Laravel Action pattern. It automates the tedious task of writing unit and feature tests by analyzing your business logic and generating production-ready **Pest** or **PHPUnit** tests using Google's Gemini AI.

Stop being a "Balu" developer spending hours on boilerplate tests. Focus on the logic, and let DevGuard handle the coverage.

## 🚀 Features

* **Auto-Framework Detection:** Automatically detects whether your project uses **Pest** or **PHPUnit**.
* **Context-Aware Generation:** Analyzes your Action classes and intelligently mocks dependencies.
* **One-Command Setup:** Seamless installation and configuration.
* **Gemini AI Powered:** Uses high-speed, high-accuracy LLMs to understand your code flow.
* **SOLID Compliant:** Generates tests that follow clean architecture and best practices.

## 📦 Installation

You can install the package via composer:

```bash
composer require niduranga/laravel-dev-guard
```

After the package is installed, run the installation command to publish the configuration file:

```bash
php artisan devguard:install
```

## ⚙️ Configuration

1.  Get your free API Key from the [Google AI Studio](https://aistudio.google.com/).
2.  Add the following key to your `.env` file:

<!-- end list -->

```env
GEMINI_API_KEY=your_actual_api_key_here

# Optional: Defaults to gemini-2.5-flash for high performance you can change to other models
GEMINI_MODEL=gemini-3-flash-preview
```

## 🛠️ Usage

Simply point DevGuard to any Action class in your project.

```bash
php artisan guard:test Actions/CreateUserAction
```

### What happens next?

1.  **Scanning:** DevGuard reads your `CreateUserAction` class.
2.  **Analysis:** It identifies DB interactions, events, and external API calls.
3.  **Generation:** AI generates a comprehensive test suite.
4.  **Saving:** The test file is automatically saved in `tests/Feature/` or `tests/Unit/`.

## ✅ Testing

We take testing seriously. This package is fully tested with Pest.

```bash
composer test
```

## 🤝 Contributing

Contributions are welcome\! If you have ideas for new features or improvements, feel free to open an issue or submit a pull request.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

-----

**Developed with ❤️ by [Niduranga Jayarathna](https://www.google.com/search?q=https://github.com/niduranga-jayarathna)**

-----