@extends('desktop.layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('POS Access Verification') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('pos.login.submit') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Verify Access') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#category').select2({
        tags: true,
        placeholder: 'Select or type a category',
        allowClear: true,
        createTag: function(params) {
            return {
                id: params.term,
                text: params.term,
                newOption: true
            }
        },
        templateResult: function(data) {
            var $result = $("<span></span>");
            $result.text(data.text);
            if (data.newOption) {
                $result.append(" (new)");
            }
            return $result;
        }
    });
});
</script>
@endsection 