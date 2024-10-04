const notyAlert = (title, description = "", type = "success") =>
    new Noty({
        text: `<strong>${title}</strong><br>${description}`,
        type,
    }).show();

$("#fee").on("click", function (event) {
    let currentTarget = $(event.currentTarget);

    currentTarget.attr("disabled", true);

    let options = [];

    $(`[id^="select2-options"]`).each((index) => {
        let rowNumber = index + 1;

        options.push({
            option: crud.field("options").subfield("option", rowNumber).input
                .value,
            amount: crud.field("options").subfield("amount", rowNumber).input
                .value,
        });
    });

    let shipping_method = crud.field("shipping_method").input.value;
    let payment_method = crud.field("payment_method").input.value;
    let customer = crud.field("customer").input.value;
    let address = crud.field("address").input.value;

    axios
        .post(currentTarget.attr("data-route"), {
            options,
            shipping_method,
            payment_method,
            customer,
            address,
        })
        .then(({ data }) => {
            let title = currentTarget.attr(
                "data-notification-successfully-title",
            );
            let description = currentTarget.attr(
                "data-notification-successfully-description",
            );

            notyAlert(title, description);

            data.items.forEach((item, index) => {
                let rowNumber = index + 1;
                let price = parseInt(item.price).toLocaleString();
                let valueAddedTax = parseInt(
                    item.value_added_tax,
                ).toLocaleString();
                let makeMoney = parseInt(
                    item.price * item.quantity,
                ).toLocaleString();

                crud.field("options").subfield("price", rowNumber).input.value =
                    price;
                crud
                    .field("options")
                    .subfield("value_added_tax", rowNumber).input.value =
                    valueAddedTax;
                crud
                    .field("options")
                    .subfield("make_money", rowNumber).input.value = makeMoney;
            });

            let tax = parseInt(data.tax).toLocaleString();
            let shippingFee = parseInt(data.shipping_fee).toLocaleString();
            let handlingFee = parseInt(data.handling_fee).toLocaleString();
            let total = parseInt(data.total).toLocaleString();

            crud.field("tax").input.value = tax;
            crud.field("shipping_fee").input.value = shippingFee;
            crud.field("handling_fee").input.value = handlingFee;
            crud.field("total").input.value = total;
        })
        .catch(({ response }) => {
            let title = response.data.message;
            let description = "";

            for (const errorField in response.data.errors)
                for (const validateText of response.data.errors[errorField])
                    description += `${validateText} <br>`;

            notyAlert(title, description, "danger");
        })
        .finally(() => {
            currentTarget.removeAttr("disabled");
        });
});
