@props(['active' => false, 'actionUrl', 'name' => 'is_active', 'method' => 'POST'])

<form method="POST" action="{{ $actionUrl }}" class="inline-block m-0 p-0" onsubmit="this.querySelector('button').disabled = true;">
    @csrf
    @if(strtoupper($method) !== 'POST')
        @method(strtoupper($method))
    @endif
    <!-- Sends the inverted value -->
    <input type="hidden" name="{{ $name }}" value="{{ $active ? '0' : '1' }}">
    <button type="submit" 
            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 {{ $active ? 'bg-indigo-600' : 'bg-gray-200' }}" 
            role="switch" 
            aria-checked="{{ $active ? 'true' : 'false' }}"
            title="Toggle {{ $active ? 'Off' : 'On' }}">
        <span aria-hidden="true" 
              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $active ? 'translate-x-5' : 'translate-x-0' }}"></span>
    </button>
</form>
