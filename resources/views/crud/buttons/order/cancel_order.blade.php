@if ($crud->hasAccess('cancel_order', $entry))
    <a bp-button="update" class="btn btn-sm btn-link" href="javascript:void(0)" onclick="cancelOrder()">
        <i class="la la-trash"></i> <span>{{ trans('Cancel order') }}</span>
    </a>

    <script>
        if (typeof cancelOrder != 'function') {
            function cancelOrder() {
                // show confirm message
                swal({
                    title: "{{ trans('backpack::base.warning') }}",
                    text: "{{ trans('Confirm cancellation of this order?') }}",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "{{ trans('backpack::crud.cancel') }}",
                            value: null,
                            visible: true,
                            className: "bg-secondary",
                            closeModal: true,
                        },
                        delete: {
                            text: "{{ trans('backpack::crud.save') }}",
                            value: true,
                            visible: true,
                            className: "bg-danger",
                        }
                    },
                }).then((value) => {
                    if (!value) {
                        return
                    }

                    // submit an AJAX delete call
                    $.ajax({
                        url: "{{ url($crud->route) }}/{{ $entry->getKey() }}/cancel-order",
                        type: 'DELETE',
                        data: {
                            entry: {{ $entry->getKey() }}
                        },
                        success: (result) => {
                            // Show an alert with the result
                            new Noty({
                                type: "success",
                                text: `<strong>${result.title}</strong><br>${result.description}`
                            }).show();

                            crud.checkedItems = [];
                            crud.table.draw(false);
                        },
                        error: ({
                                    responseJSON
                                }) => {
                            // Show an alert with the result
                            new Noty({
                                type: "danger",
                                text: `<strong>${responseJSON.title}</strong><br>${responseJSON.description}`
                            }).show();
                        },
                    });
                });
            }
        }
    </script>
@endif
