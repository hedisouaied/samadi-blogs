<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identity')
                    ->description('Basic profile information for this administrator or author.')
                    ->icon('heroicon-o-user-circle')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(120)
                            ->autocomplete('name'),
                        TextInput::make('username')
                            ->maxLength(60)
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->helperText('Optional public handle. Letters, numbers, dashes and underscores only.'),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->maxLength(180)
                            ->unique(ignoreRecord: true)
                            ->autocomplete('email'),
                        TextInput::make('job_title')
                            ->label('Job title')
                            ->maxLength(120),
                        Textarea::make('bio')
                            ->rows(4)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Avatar')
                    ->icon('heroicon-o-photo')
                    ->columns(2)
                    ->components([
                        FileUpload::make('avatar_path')
                            ->label('Profile photo')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->circleCropper()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Square image recommended, maximum 2 MB.'),
                    ]),

                Section::make('Role & access')
                    ->description('Determines what this user can see and do in the back office.')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->components([
                        Select::make('role')
                            ->options(fn (): array => self::roleOptions())
                            ->required()
                            ->native(false)
                            ->live()
                            ->disabled(fn (?User $record): bool => $record !== null && ! self::actorCanManageRoles())
                            ->helperText('Only Super Admins may promote a user to Super Admin.'),
                        Select::make('status')
                            ->options(UserStatus::options())
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Password')
                    ->description('Leave blank when editing to keep the current password.')
                    ->icon('heroicon-o-key')
                    ->columns(2)
                    ->components([
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->minLength(8)
                            ->maxLength(255)
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->same('password_confirmation')
                            ->helperText('Minimum 8 characters.'),
                        TextInput::make('password_confirmation')
                            ->label('Confirm password')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->same('password'),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected static function roleOptions(): array
    {
        $options = UserRole::options();

        // Non super admins may not create or promote other super admins.
        if (! self::actorCanManageRoles()) {
            unset($options[UserRole::SuperAdmin->value]);
        }

        return $options;
    }

    protected static function actorCanManageRoles(): bool
    {
        return (bool) auth()->user()?->hasPermission(Permission::ManageUserRoles);
    }
}
