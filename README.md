# Laravel Prompt Manager

A Laravel package built for the next generation of LLM engineers — seamlessly manage, version, and test your AI prompts with the same rigor you bring to your code. Designed for developers who want to apply real software engineering principles to prompt engineering and build AI features that scale with confidence.

## Table of Contents
- [Features](#features)
- [Installation](#installation)
- [Basic Usage](#basic-usage)
  - [Generating and Using Prompts](#generating-and-using-prompts)
- [Advanced Usage](#advanced-usage)
  - [Model-Specific Versions](#model-specific-versions)
  - [Working with Existing Template Storage](#working-with-existing-template-storage)
  - [A/B Testing Made Easy](#ab-testing-made-easy)
  - [Context-Aware Version Selection](#context-aware-version-selection)
  - [Custom Token Counting](#custom-token-counting)
- [Best Practices](#best-practices)
- [License](#license)

## Features

- 🔄 Prompt versioning
- 📊 Easy A/B testing with random selection
- 📏 Built-in token counting
- 🎯 Support for model-specific prompt variants
- 🧪 Simple testing and maintenance
- ⚡️ Fluent and intuitive API

## Installation

```bash
composer require prismaticoder/laravel-prompt-manager
```

## Basic Usage

Generate a new prompt class:

```bash
php artisan make:prompt ProductDescriptionPrompt
```

This will create a new `ProductDescriptionPrompt` class in the `App/Prompts` directory

```php
namespace App\Prompts;

use Prismaticoder\LaravelPromptManager\BaseLLMPrompt;
use Prismaticoder\LaravelPromptManager\PromptVersionManager;

class ProductDescriptionPrompt extends BaseLLMPrompt
{
    private string $productName;
    private array $features;

    public function __construct(string $productName, array $features)
    {
        $this->productName = $productName;
        $this->features = $features;
    }

    protected function versions(): PromptVersionManager
    {
        return new PromptVersionManager([
            'v1' => fn() => $this->generateBasicPrompt(),
            'v1-creative' => fn() => $this->generateCreativePrompt(),
            'v1-technical' => fn() => $this->generateTechnicalPrompt(),
        ]);
    }

    protected function defaultVersion(): string
    {
        return 'v1';
    }

    private function generateBasicPrompt(): string
    {
        return <<<PROMPT
        Write a product description for {$this->productName}.

        Key features:
        - " . implode("\n- ", $this->features)
        PROMPT;
    }
}
```

### Generating and Using Prompts

```php
$prompt = ProductDescriptionPrompt::make('Ergonomic Chair', [
    'Adjustable height',
    'Lumbar support',
    'Memory foam'
]);

// Default version
$result = $prompt->generate();
echo $result->prompt; // The generated prompt
echo "Version used: {$result->version}"
echo "Token count: {$result->token_count}";

// Specific version
$result = $prompt->generate('v1-creative');
```

## Advanced Usage

### Model-Specific Versions

Create model-specific prompts and dynamically select versions:

```php
class CustomerSupportPrompt extends BaseLLMPrompt
{
    protected function defaultVersion(): string
    {
        return 'v1-gpt3.5'; // Only defaults to this version when no version selector is defined.
    }

    protected function versions(): PromptVersionManager
    {
        return new PromptVersionManager([
            'v1-gpt3.5' => fn() => $this->basePromptWithConstraints(2000),
            'v1-gpt4' => fn() => $this->basePromptWithConstraints(4000),
            'v1-claude' => fn() => $this->generateClaudePrompt(),
        ]);
    }

    protected function versionSelector(): Closure
    {
        return function(array $versions) {
            $model = config('services.llm.default_model');
            return match($model) {
                'gpt-4' => 'v1-gpt4',
                'claude' => 'v1-claude',
                default => 'v1-gpt3.5'
            };
        };
    }
}
```

### Working with Existing Template Storage

If you already store prompt templates in a database or registry, you can easily integrate them:

```php
class TransactionFraudAnalysisPrompt extends BaseLLMPrompt
{
    private Transaction $transaction;
    private array $riskMetrics;

    public function __construct(Transaction $transaction, array $riskMetrics)
    {
        $this->transaction = $transaction;
        $this->riskMetrics = $riskMetrics;
    }

    protected function versions(): PromptVersionManager
    {
        return new PromptVersionManager([
            'v1' => fn() => $this->getTemplateAndCompile('standard'),
            'v2-high-risk' => fn() => $this->getTemplateAndCompile('high_risk'),
        ]);
    }

    private function getTemplateAndCompile(string $templateKey): string
    {
        // Fetch template from your existing storage
        $template = PromptTemplate::findByKey($templateKey);

        return collect([
            'amount' => $this->transaction->amount,
            'merchant' => $this->transaction->merchant_name,
            'risk_score' => $this->riskMetrics['score'],
        ])->reduce(
            fn(string $prompt, $value, $key) => str_replace("{{$key}}", $value, $prompt),
            $template
        );
    }

    protected function versionSelector(): Closure
    {
        return fn() => $this->riskMetrics['score'] > 0.7 ? 'v2-high-risk' : 'v1';
    }
}

// Usage example:
$prompt = TransactionFraudAnalysisPrompt::make($transaction, $riskMetrics);
$result = $prompt->generate(); // Version selected based on risk score
```

### A/B Testing Made Easy

Easily run A/B tests on your prompts by enabling random version selection:

```php
use Prismaticoder\LaravelPromptManager\Enums\VersionSelector;

class ProductCopyPrompt extends BaseLLMPrompt
{
    protected function versionSelector(): VersionSelector
    {
        return VersionSelector::RANDOM;
    }

    protected function defaultVersion(): string
    {
        return 'v1-formal'; // Only defaults to this version when no version selector is defined.
    }

    protected function versions(): PromptVersionManager
    {
        return new PromptVersionManager([
            'v1-formal' => fn() => $this->formalTone(),
            'v1-casual' => fn() => $this->casualTone(),
            'v1-persuasive' => fn() => $this->persuasiveTone(),
        ]);
    }
}
```

### Context-Aware Version Selection

You can override the default `versionSelector()` method to select prompt versions based on relevant context:

```php
class TutorialPrompt extends BaseLLMPrompt
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    protected function versionSelector(): Closure
    {
        return fn() => match($this->user->expertise_level) {
            'beginner' => 'v1-basic',
            'intermediate' => 'v1-detailed',
            'expert' => 'v1-advanced',
            default => $this->defaultVersion()
        };
    }
}
```

### Custom Token Counting

The default token counting strategy is a simple estimation (text length divided by 4). To improve accuracy, you can override it with a custom token counter like Tiktoken:

```php
class ComplexPrompt extends BaseLLMPrompt
{
    protected function tokenCounter(): Closure
    {
        return fn(string $prompt) => Tiktoken::count($prompt);
    }
}
```

## Best Practices

- **Abstract Complex Prompts**: Break down large prompts into smaller methods.
- **Semantic Versioning**: Use clear naming conventions (e.g., `v1-gpt4`, `v1-formal`).
- **Testing**: Use built-in random selection for effective A/B testing.

## License

The MIT License (MIT). See [License File](LICENSE).
