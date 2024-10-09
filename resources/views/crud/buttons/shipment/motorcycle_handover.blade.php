@if ($crud->hasAccess('motorcycle_handover', $entry))
    <a href="{{ url($crud->route . '/' . $entry->getKey() . '/motorcycle-handover') }}" class="btn btn-sm btn-link">
        <i class="la la-send"></i> <span>{{ trans('Motorcycle handover') }}</span>
    </a>
@endif
