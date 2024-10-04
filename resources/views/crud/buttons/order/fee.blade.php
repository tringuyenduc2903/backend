@include('crud::fields.inc.wrapper_start')
@include('crud::fields.inc.translatable_icon')

<button type="button"
        id="fee"
        class="btn btn-secondary text-white"
        data-route="{{ $field['route'] }}"
        data-notification-successfully-title="{{ $field['notification']['successfully']['title'] }}"
        data-notification-successfully-description="{{ $field['notification']['successfully']['description'] }}"
>
    <span class="la la-refresh" role="presentation" aria-hidden="true"></span> &nbsp;
    <span>{{ $field['label'] }}</span>
</button>

{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')
