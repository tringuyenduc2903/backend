crud.field("amount")
    .onChange((field) => {
        let value = parseInt(field.value);

        if (isNaN(value)) value = 0;

        crud.field("preview", field.rowNumber).input.value =
            parseInt(value).toLocaleString();
    })
    .change();
