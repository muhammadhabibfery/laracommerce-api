<?php

namespace App\Filament\Resources;

use Closure;
use App\Models\User;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\ProfileResource\Pages;
use App\Policies\UserPolicy;
use Filament\Forms\Components\FileUpload;
use Illuminate\Validation\Rules\Password;
use Livewire\TemporaryUploadedFile;

class ProfileResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $pluralModelLabel = 'Profile';

    protected static ?string $slug = 'profile';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Profile';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Profile')
                    ->description('This form section to update your profile')
                    ->schema([
                        TextInput::make('name')
                            ->nullable()
                            ->maxLength(50)
                            ->reactive()
                            ->afterStateUpdated(
                                function (Closure $set, $state) {
                                    return $set('username', function () use ($state) {
                                        $state = str($state)->slug()->value();
                                        return User::where('username', $state)->first() ? $state .= rand(11, 99) : $state;
                                    });
                                }
                            ),
                        TextInput::make('username')
                            ->maxLength(35)
                            ->disabled(),
                        TextInput::make('email')
                            ->email()
                            ->nullable()
                            ->unique('users', 'email', self::getUser()),
                        TextInput::make('phone')
                            ->numeric()
                            ->nullable()
                            ->unique('users', 'phone', self::getUser())
                            ->minLength(11)
                            ->maxLength(13),
                        Textarea::make('address')
                            ->nullable(),
                        FileUpload::make('avatar')
                            ->label('Profile Picture')
                            ->image()
                            ->maxSize(2500)
                            ->directory('avatars')
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file): string => setFileName($file->getClientOriginalName())
                            )
                    ])
                    ->columns(2)
                    ->collapsible(),
                Section::make('Password')
                    ->description('This form section to change your password')
                    ->schema([
                        TextInput::make('current_password')
                            ->password()
                            ->nullable()
                            ->rules(['current_password'])
                            ->dehydrated(false),
                        TextInput::make('new_password')
                            ->password()
                            ->requiredWith('current_password')
                            ->different('current_password')
                            ->rules([Password::min(8)->numbers()->symbols()])
                            ->confirmed(),
                        TextInput::make('new_password_confirmation')
                            ->password()
                            ->requiredWith('new_password')
                            ->dehydrated(false)
                    ])
                    ->collapsible()

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\CreateProfile::route('/'),
        ];
    }

    public static function getUser(): User
    {
        return auth()->user();
    }

    public static function shouldIgnorePolicies(): bool
    {
        return setAuthorization(self::getUser(), UserPolicy::ADMIN_ROLE, UserPolicy::STAFF_ROLE);
    }
}
