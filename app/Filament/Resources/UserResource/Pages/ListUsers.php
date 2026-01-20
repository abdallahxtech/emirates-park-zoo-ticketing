<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Mail\StaffInvitationMail;
use App\Models\Role;
use App\Models\StaffInvitation;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Mail;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('invite')
                ->label('Invite Staff')
                ->icon('heroicon-o-envelope')
                ->form([
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique('users', 'email')
                        ->unique('staff_invitations', 'email'),
                    Forms\Components\Select::make('role_id')
                        ->options(Role::pluck('name', 'id'))
                        ->required()
                        ->label('Role'),
                ])
                ->action(function (array $data) {
                    $invitation = StaffInvitation::create([
                        'email' => $data['email'],
                        'role' => Role::find($data['role_id'])->slug ?? 'sales', // Backup slug logic
                        // In new schema we refer to role_id in user, but invitation table has 'role' string (slug or ID?)
                        // Let's check schema: table->string('role')->default('sales');
                        // So it stores the SLUG or NAME? Plan said "Role assignment".
                        // Let's store the role ID via relationship or just the slug if the table expects string.
                        // Checked schema: 2026_01_20_000010_create_staff_invitations_table.php -> $table->string('role')->default('sales');
                        // It seems to expect a string slug.
                        'invited_by' => auth()->id(),
                    ]);

                    // Send email
                    try {
                        Mail::to($invitation->email)->send(new StaffInvitationMail($invitation));
                        
                        Notification::make()
                            ->title('Invitation sent successfully')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                         Notification::make()
                            ->title('Invitation created but email failed')
                            ->body($e->getMessage())
                            ->warning()
                            ->send();
                    }
                }),
            Actions\CreateAction::make()->label('Create Direct User'),
        ];
    }
}
