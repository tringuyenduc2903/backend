@if ($crud->hasAccess('update', $entry))
    <a href="{{ url($crud->route . '/' . $entry->getKey() . '/edit') }}" class="btn btn-sm btn-link">
        <i class="la la-edit"></i> <span>{{ trans('Reply this review') }}</span>
    </a>
@endif
