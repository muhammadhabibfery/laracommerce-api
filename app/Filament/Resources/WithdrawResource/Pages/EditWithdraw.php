<?php

namespace App\Filament\Resources\WithdrawResource\Pages;

use App\Models\User;
use Filament\Pages\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\WithdrawResource;
use App\Mail\MerchantWdMail;
use Illuminate\Support\Facades\Mail;

class EditWithdraw extends EditRecord
{
    protected static string $resource = WithdrawResource::class;

    public array $payload;

    protected function getActions(): array
    {
        return [];
    }

    protected function getTitle(): string
    {
        return 'Confirmation withdraw funds';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->payload = $data;
        $user = $this->getUser($data['user_id']);
        $data['user_name'] = $user->name;
        $data['user_merchantAccount_name'] = $user->merchantAccount->name;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->payload = array_merge($this->payload, $data);
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $status = 'Approved';
        $user = $this->getUser($this->payload['user_id']);

        if ($data['is_confirm'] == "0") {
            $merchantAccount = $user->merchantAccount;
            $merchantAccount->balance += $this->payload['amount'];
            $merchantAccount->save();

            $record->balance += $this->payload['amount'];
            $record->status = 'REJECT';
            $status = 'Rejected';
        } else {
            $record->status = 'SUCCESS';
        }

        $record->updated_by = auth()->id();
        $record->save();

        Mail::to($user)
            ->send(new MerchantWdMail($this->payload['id'], $status));

        return $record;
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('Update'))
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'withdraw funds request has been updated';
    }

    protected function getUser(int $id): User
    {
        return User::findOrFail($id);
    }
}
