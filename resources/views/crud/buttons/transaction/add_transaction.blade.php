@if ($crud->hasAccess('add_transaction', $entry))
    <a href="{{ url($crud->route . '/' . $entry->getKey() . '/add-transaction') }}" class="btn btn-sm btn-link">
        <i class="la la-edit"></i> <span>{{ trans('Add cash transactions') }}</span>
    </a>
@endif
