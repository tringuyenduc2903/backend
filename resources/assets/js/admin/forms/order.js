crud.field("motorcycle_registration_support")
    .onChange(function (field) {
        const rowNumber = field.rowNumber;
        const close = field.value === "0";

        crud.field("registration_option", rowNumber).hide(close);
        crud.field("license_plate_registration_option", rowNumber).hide(close);
    })
    .change();
