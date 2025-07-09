<div class="w-full">
    <div>
        <h1>Connect Gmail Account</h1>
        <a href="{{ route('GmailAuthController.allowAccess') }}" class="h-8 bg-[#f8fafc] rounded-lg hover:bg-[#eff6ff] hover:-translate-y-1 lg:col-span-2 md:col-span-3 sm:col-span-3 xs:col-span-1 cursor-pointer transition-all duration-200 ease-in">
            <div class="flex p-2 justify-start items-center gap-2 w-full h-full border rounded-lg">

                <p class="text-sm font-medium text-[#1f2937]">Gmail</p>
            </div>
        </a>
    </div>

    <h1>Connected Accounts</h1>
    @foreach ($gmailAccounts as $gmailAccount)


    <div wire:key="gmail-{{ $gmailAccount['uuid'] }}" class="flex w-full gap-6">
        <div class="flex gap-2">
            <p class="text-sm font-medium text-[#1f2937] truncate overflow-hidden whitespace-nowrap">{{$gmailAccount['gmail_account']}}</p>
        </div>
        <div class=" ">
            <a href="{{ route('mails', ['gmailAccountId' => $gmailAccount['uuid']]) }}" class="py-1 px-2 bg-[#059669] text-white text-xs font-medium rounded-md hover:bg-[#059669] hover:scale-105 transition-all duration-200 ease-in">Load</a>
        </div>
        <div class=" ">
            <a wire:click="deleteAccount('{{ $gmailAccount['uuid'] }}')" class="py-1 px-2 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-500 hover:scale-105 cursor-pointer transition-all duration-200 ease-in">Delete</a>
        </div>
    </div>

    @endforeach
</div>