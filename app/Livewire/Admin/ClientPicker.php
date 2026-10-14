<?php

namespace App\Livewire\Admin;

use App\Enums\ClientType;
use App\Models\TelegramClient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Customer field of a document: free text plus a "Browse" button that opens a searchable client list in a modal.
 * Picking a client fills the name and links the document to the client; typing over the name removes the link.
 * Inputs carry the form names (counterparty, telegram_client_id), so the surrounding form submits them.
 */
class ClientPicker extends Component
{
    use WithPagination;

    public string $name = '';

    public ?int $clientId = null;

    public bool $open = false;

    public string $search = '';

    public string $type = '';

    public string $label = '';

    public ?string $error = null;

    public function mount(?string $value = null, ?int $clientId = null, string $label = '', ?string $error = null): void
    {
        $this->name = (string) $value;
        $this->clientId = $clientId;
        $this->label = $label;
        $this->error = $error;
    }

    public function updatedName(): void
    {
        $this->clientId = null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage('clientsPage');
    }

    public function updatedType(): void
    {
        $this->resetPage('clientsPage');
    }

    public function choose(int $clientId): void
    {
        $client = TelegramClient::find($clientId);

        if ($client !== null) {
            $this->clientId = $client->id;
            $this->name = $client->displayName();
        }

        $this->open = false;
    }

    public function unlink(): void
    {
        $this->clientId = null;
    }

    #[Computed]
    public function chosen(): ?TelegramClient
    {
        return $this->clientId ? TelegramClient::find($this->clientId) : null;
    }

    /**
     * @return LengthAwarePaginator<int, TelegramClient>
     */
    #[Computed]
    public function clients(): LengthAwarePaginator
    {
        $search = trim($this->search);
        $digits = preg_replace('/\D/', '', $search);

        return TelegramClient::query()
            ->when(ClientType::tryFrom($this->type), fn ($query, ClientType $type) => $query->where('type', $type))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search, $digits) {
                $query->where('company_name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%");

                if (strlen($digits) >= 3) {
                    $query->orWhereRaw("replace(replace(replace(phone, ' ', ''), '-', ''), '+', '') like ?", ["%{$digits}%"]);
                }
            }))
            ->withCount('orders')
            ->orderByRaw("coalesce(nullif(company_name, ''), first_name)")
            ->paginate(10, pageName: 'clientsPage');
    }

    public function render(): View
    {
        return view('livewire.admin.client-picker');
    }
}
