@if ($crud->hasAccess('create_ghn_order', $entry))
    <a class="btn btn-sm btn-link" href="javascript:void(0)" onclick="createGhnOrder(this)"
       data-id="{{ $entry->getKey() }}">
        <i class="la la-send"></i> <span>{{ trans('Create GHN Order') }}</span>
    </a>

    <script>
        if (typeof createGhnOrder != 'function') {
            function createGhnOrder(button) {
                // show confirm message
                swal({
                    title: "{{ trans('Confirm packaging') }}",
                    text: "{{ trans('Is the order ready for delivery to the :name?', ['name' => trans('Shipping unit')]) }}",
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
                            className: "bg-success",
                        }
                    },
                }).then((value) => {
                    if (!value) {
                        return
                    }

                    const entry = $(button).attr('data-id')

                    // submit an AJAX delete call
                    $.ajax({
                        url: `{{ url($crud->route) }}/${entry}/create-ghn-order`,
                        type: 'DELETE',
                        data: {
                            entry
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
