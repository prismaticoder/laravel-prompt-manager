# Laravel Prompt Manager

A powerful Laravel package for managing, versioning, and testing AI prompts, built for developers who need clean, testable, and version-controlled prompts for LLM integrations.

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

```php
use Prismaticoder\LaravelPromptManager\BaseLLMPrompt;
use Prismaticoder\LaravelPromptManager\VersionManager;

class ProductDescriptionPrompt extends BaseLLMPrompt
{
    private string $productName;
    private array $features;

    public function __construct(string $productName, array $features)
    {
        $this->productName = $productName;
        $this->features = $features;
    }

    protected function versions(): VersionManager
    {
        return new VersionManager([
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
    protected function versions(): VersionManager
    {
        return new VersionManager([
            'v1-gpt3.5' => fn() => $this->basePromptWithConstraints(2000),
            'v1-gpt4' => fn() => $this->basePromptWithConstraints(4000),
            'v1-claude' => fn() => $this->generateClaudePrompt(),
        ]);
    }

    protected function versionSelector(): Closure
    {
        return function(array $versions) {
            $model = config('ai.default_model');
            return match($model) {
                'gpt-4' => 'v1-gpt4',
                'claude' => 'v1-claude',
                default => 'v1-gpt3.5'
            };
        };
    }
}
```

### A/B Testing with Random Selection

Optimize prompts using random selection:

```php
class ProductCopyPrompt extends BaseLLMPrompt
{
    protected function versionSelector(): VersionSelector
    {
        return VersionSelector::RANDOM;
    }

    protected function versions(): VersionManager
    {
        return new VersionManager([
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

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). See [License File](LICENSE.md).