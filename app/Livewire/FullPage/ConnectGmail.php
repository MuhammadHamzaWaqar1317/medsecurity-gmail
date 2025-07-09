<?php

namespace App\Livewire\FullPage;

use App\Models\GmailAccounts;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('livewire.layout.gmail-layout')]
class ConnectGmail extends Component
{
    public $gmailAccounts;

    public function mount()
    {
        $this->fetchAccounts();
    }

    public function render()
    {
        return view('livewire.full-page.connect-gmail');
    }

    public function fetchAccounts()
    {
        $this->gmailAccounts = GmailAccounts::all()->toArray();
    }

    public function deleteAccount($uuid)
    {
        GmailAccounts::where('uuid', $uuid)->delete();
        $this->fetchAccounts();
    }
}
