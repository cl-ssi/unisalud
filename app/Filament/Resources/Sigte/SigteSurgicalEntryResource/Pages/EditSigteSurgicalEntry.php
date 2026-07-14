<?php

namespace App\Filament\Resources\Sigte\SigteSurgicalEntryResource\Pages;

use App\Filament\Resources\Sigte\SigteSurgicalEntryResource;
use App\Models\Address;
use App\Models\ContactPoint;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;

class EditSigteSurgicalEntry extends EditRecord
{
    protected static string $resource = SigteSurgicalEntryResource::class;

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record->user;

        if (! $user) {
            return $data;
        }

        $address = Address::where('user_id', $user->id)->where('use', 'home')->first();

        $data['address_street'] = $address?->text;
        $data['address_number'] = $address?->line;
        $data['address_extra']  = $address?->suburb;
        $data['address_city']   = $address?->city;
        $data['is_rural']       = (bool) ($address?->is_rural);
        $data['via']            = $address?->via;

        $data['phone_home']   = ContactPoint::where('user_id', $user->id)->where('system', 'phone')->where('use', 'home')->first()?->value;
        $data['phone_mobile'] = ContactPoint::where('user_id', $user->id)->where('system', 'phone')->where('use', 'mobile')->first()?->value;
        $data['email']        = ContactPoint::where('user_id', $user->id)->where('system', 'email')->where('use', 'work')->first()?->value;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = $this->record->user;

        if ($user) {
            Address::updateOrCreate(
                ['user_id' => $user->id, 'use' => 'home'],
                [
                    'type'       => 'physical',
                    'text'       => $data['address_street'] ?? null,
                    'line'       => $data['address_number'] ?? null,
                    'suburb'     => $data['address_extra'] ?? null,
                    'city'       => $data['address_city'] ?? null,
                    'commune_id' => $data['commune_id'] ?? null,
                    'is_rural'   => $data['is_rural'] ?? null,
                    'via'        => $data['via'] ?? null,
                ]
            );

            ContactPoint::updateOrCreate(
                ['user_id' => $user->id, 'system' => 'phone', 'use' => 'home'],
                ['value' => $data['phone_home'] ?? null]
            );

            ContactPoint::updateOrCreate(
                ['user_id' => $user->id, 'system' => 'phone', 'use' => 'mobile'],
                ['value' => $data['phone_mobile'] ?? null]
            );

            ContactPoint::updateOrCreate(
                ['user_id' => $user->id, 'system' => 'email', 'use' => 'work'],
                ['value' => $data['email'] ?? null]
            );
        }

        return collect($data)->only((new \App\Models\SigteSurgicalWaitlist())->getFillable())->toArray();
    }
}
