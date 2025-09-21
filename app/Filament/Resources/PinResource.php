<?php

// app/Filament/Resources/PinResource.php
namespace App\Filament\Resources;

use App\Models\Pin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\PinResource\Pages;

class PinResource extends Resource
{
    protected static ?string $model = Pin::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    // Pengaturan untuk navigasi sidebar
    protected static ?string $navigationGroup = 'Moderation';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pin Details')
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\FileUpload::make('icon')
                            ->directory('pin-icons')
                            ->image()
                            ->label('Pin Icon (e.g., a small logo)'),

                        Forms\Components\FileUpload::make('banner')
                            ->directory('pin-banners')
                            ->image()
                            ->label('Banner Image (for the sidebar)'),

                        Forms\Components\Textarea::make('body')
                            ->label('Description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location & Status')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->required()
                            ->numeric(),

                        Forms\Components\TextInput::make('longitude')
                            ->required()
                            ->numeric(),
                        
                        Forms\Components\Toggle::make('moderated')
                            ->label('Approved')
                            ->required(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('icon')
                    ->disk('public')
                    ->circular(),

                    Tables\Columns\ImageColumn::make('banner')
                    ->disk('public')
                    ->label('Banner'),


                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('moderated')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('moderated')
                    ->label('Moderation Status')
                    ->boolean()
                    ->trueLabel('Approved')
                    ->falseLabel('Pending')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn (Pin $record) => $record->update(['moderated' => true]))
                    ->visible(fn (Pin $record) => !$record->moderated),
                Tables\Actions\Action::make('reject')
                    ->label('Revoke')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(fn (Pin $record) => $record->update(['moderated' => false]))
                    ->visible(fn (Pin $record) => $record->moderated),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['moderated' => true])),
                    Tables\Actions\BulkAction::make('reject')
                        ->label('Revoke Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['moderated' => false])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPins::route('/'),
            'create' => Pages\CreatePin::route('/create'),
            'edit' => Pages\EditPin::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('moderated', false)->count();
    }
}