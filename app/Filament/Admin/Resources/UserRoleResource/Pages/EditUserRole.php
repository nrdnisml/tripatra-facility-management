<?php

namespace App\Filament\Admin\Resources\UserRoleResource\Pages;

use App\Filament\Admin\Resources\UserRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserRole extends EditRecord
{
    protected static string $resource = UserRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
