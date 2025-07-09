<?php

namespace App\Livewire\FullPage;

use Illuminate\Http\Request;
use App\Models\GmailAccountMails;
use App\Models\GmailBatchFetchProgress;
use Illuminate\Support\Facades\Bus;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('livewire.layout.gmail-layout')]
class Mails extends Component
{
    public $gmailAccountId;
    public $totalMails;
    public $firstSubjectMailDate;
    public $totalInvoiceAmmount;
    public $totalInvoiceMailCount;
    public $fetchingMails;

    public function mount(Request $req)
    {
        $this->gmailAccountId = $req->route()->parameters['gmailAccountId'];
        $this->fetchingMails = true;
    }

    public function render()
    {
        return view('livewire.full-page.mails');
    }

    public function fetchData()
    {
        $this->totalMails = GmailAccountMails::where('gmail_account_id', $this->gmailAccountId)->count();
        $this->firstSubjectMailDate = optional(
            GmailAccountMails::where('gmail_account_id', $this->gmailAccountId)
                ->where('subject', 'like', '%Medsurity Experts Smattering%')
                ->orderBy('received_at', 'asc')
                ->first()
        )->toArray();

        if (!empty($this->firstSubjectMailDate['received_at'])) {
            $this->totalInvoiceAmmount = GmailAccountMails::getInvoiceTotalAfterDate($this->firstSubjectMailDate['received_at']);
            $this->totalInvoiceMailCount = GmailAccountMails::getUniqueInvoiceCount($this->firstSubjectMailDate['received_at']);
        } else {
            $this->totalInvoiceAmmount = 0;
            $this->totalInvoiceMailCount = 0;
        }
    }

    public function showMailFetchProgress()
    {
        $batchDetails = GmailBatchFetchProgress::where('gmail_account_id', $this->gmailAccountId)->get()->toArray();
        if (count($batchDetails) != 0) {
            $batchId = $batchDetails[0]['batch_id'];
            $batchData = Bus::findBatch($batchId);
            $this->fetchingMails = true;

            if ($batchData->pendingJobs == 0) {
                $this->fetchingMails = false;
            }
        } else {
            $this->fetchingMails = false;
        }
        $this->fetchData();
    }
}
