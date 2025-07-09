<div>
    <div class="absolute" wire:poll="showMailFetchProgress"></div>
    <h2>Total Mails: <span class="font-bold"> {{$totalMails}}</span></h2>


    @if ($fetchingMails)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50"
        x-transition.opacity>
        <div class="flex flex-col items-center absolute top-2/4 left-2/4 transform -translate-x-1/2 -translate-y-1/2">
            <!-- Spinner -->
            <div class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
            <p class="text-white mt-3">Please wait while we fetch your mails...</p>
        </div>
    </div>

    @else
    <h2>
        Date of the first email containing the text 'Medsurity Experts Smattering':
        <span class="font-bold"> {{ isset($firstSubjectMailDate['received_at']) ? \Carbon\Carbon::parse($firstSubjectMailDate['received_at'])->toDateString() : '' }}</span>
    </h2>
    <h2>Total Invoice Mail Count after 'Medsurity Experts Smattering': <span class="font-bold">{{$totalInvoiceMailCount}}</span> </h2>
    <h2>Total Invoice Ammount after 'Medsurity Experts Smattering': <span class="font-bold">${{$totalInvoiceAmmount}}</span> </h2>


    @endif


</div>