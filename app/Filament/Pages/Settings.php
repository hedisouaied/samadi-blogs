<?php

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Models\User;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use UnitEnum;

class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Settings';

    protected static ?string $navigationLabel = 'Settings';

    protected string $view = 'filament.pages.settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Filament::auth()->user()?->hasPermission(Permission::ViewSettings) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill($this->loadSettings());
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->footer([
                        Action::make('save')
                            ->label('Save changes')
                            ->submit('save')
                            ->visible(fn (): bool => Filament::auth()->user()?->hasPermission(Permission::ManageSettings) ?? false),
                    ]),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        $tabs = [];

        foreach (app(SettingsService::class)->schema() as $group => $definition) {
            $fields = [];

            foreach ($definition['settings'] ?? [] as $key => $meta) {
                $fields[] = $this->buildField($group, $key, $meta);
            }

            $tabs[] = Tabs\Tab::make($definition['label'] ?? ucfirst($group))
                ->icon($definition['icon'] ?? null)
                ->schema([
                    Section::make($definition['label'] ?? ucfirst($group))
                        ->description($definition['description'] ?? null)
                        ->schema($fields)
                        ->columns(2),
                ]);
        }

        return $schema
            ->components([
                Tabs::make('settings')->persistTabInQueryString()->tabs($tabs),
            ])
            ->statePath('data');
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function buildField(string $group, string $key, array $meta): Component
    {
        $name = $group.'__'.$key;
        $label = $meta['label'] ?? ucfirst(str_replace('_', ' ', $key));
        $helper = isset($meta['helper']) ? new HtmlString(e($meta['helper'])) : null;

        $field = match ($meta['type'] ?? 'string') {
            'text' => Textarea::make($name)->rows(3)->maxLength($meta['max'] ?? 1000),
            'integer' => TextInput::make($name)->numeric()
                ->minValue($meta['min'] ?? null)
                ->maxValue($meta['max'] ?? null),
            'boolean' => Toggle::make($name)->inline(false),
            'select' => Select::make($name)
                ->options($this->resolveOptions($meta))
                ->native(false),
            'image' => FileUpload::make($name)
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('settings')
                ->visibility('public')
                ->maxSize(4096),
            'user' => Select::make($name)
                ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->native(false)
                ->placeholder('No default'),
            'string' => TextInput::make($name)
                ->maxLength($meta['max'] ?? 255)
                ->email($meta['email'] ?? false)
                ->url($meta['url'] ?? false),
            default => TextInput::make($name)->maxLength($meta['max'] ?? 255),
        };

        return $field
            ->label($label)
            ->helperText($helper)
            ->required($meta['required'] ?? false);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<int|string, string>
     */
    protected function resolveOptions(array $meta): array
    {
        $options = $meta['options'] ?? [];

        if (is_string($options) && enum_exists($options)) {
            /** @var array<int|string, string> $resolved */
            $resolved = $options::options();

            return $resolved;
        }

        return is_array($options) ? $options : [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function loadSettings(): array
    {
        $service = app(SettingsService::class);
        $data = [];

        foreach ($service->schema() as $group => $definition) {
            foreach ($definition['settings'] ?? [] as $key => $meta) {
                $data[$group.'__'.$key] = $service->get($group.'.'.$key, $meta['default'] ?? null);
            }
        }

        return $data;
    }

    public function save(): void
    {
        if (! (Filament::auth()->user()?->hasPermission(Permission::ManageSettings) ?? false)) {
            Notification::make()->title('You are not allowed to change settings')->danger()->send();

            return;
        }

        $state = $this->form->getState();

        $values = [];

        foreach ($state as $name => $value) {
            $values[str_replace('__', '.', (string) $name)] = $value;
        }

        app(SettingsService::class)->setMany($values);

        Notification::make()->title('Settings saved')->success()->send();
    }
}
