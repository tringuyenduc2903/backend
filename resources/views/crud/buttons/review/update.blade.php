@if ($crud->hasAccess('update', $entry))
    <a href="{{ url($crud->route . '/' . $entry->getKey() . '/edit') }}" bp-button="update" class="btn btn-sm btn-link">
        <i class="la la-edit"></i> <span>{{ trans('Reply this review') }}</span>
    </a>
@endif
