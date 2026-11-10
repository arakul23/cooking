<?php

namespace App\Filament\Resources\OAuthProviders\Pages;

use App\Filament\Resources\OAuthProviders\OAuthProviderResource;
use Filament\Resources\Pages\ManageRecords;

class ManageOAuthProviders extends ManageRecords
{
    protected static string $resource = OAuthProviderResource::class;
}
