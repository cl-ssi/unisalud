<div>
    <button class="btn btn-secondary" wire:click.prevent="resetPassword" wire:loading.remove>Reset</button>
    @if($message)
        {{ $message }}
    @endif
</div>
