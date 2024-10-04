const notyAlert = (title, description = "", type = "success") =>
    new Noty({
        text: `<strong>${title}</strong><br>${description}`,
        type,
    }).show();

$("#fee").on("click", function (event) {
    let currentTarget = $(event.currentTarget);

    currentTarget.attr("disabled", true);

    let option = crud.field("option").input.value;
    let payment_method = crud.field("payment_method").input.value;
    let customer = crud.field("customer").input.value;
    let address = crud.field("address").input.value;
    let identification = crud.field("identification").input.value;
    let motorcycle_registration_support = crud.field(
        "motorcycle_registration_support",
    ).input.value;
    let registration_option = crud.field("registration_option").input.value;
    let license_plate_registration_option = crud.field(
        "license_plate_registration_option",
    ).input.value;

    axios
        .post(currentTarget.attr("data-route"), {
            option,
            payment_method,
            customer,
            address,
            identification,
            motorcycle_registration_support,
            registration_option,
            license_plate_registration_option,
        })
        .then(({ data }) => {
            let title = currentTarget.attr(
                "data-notification-successfully-title",
            );
            let description = currentTarget.attr(
                "data-notification-successfully-description",
            );

            notyAlert(title, description);

            let price = parseInt(data.item.price).toLocaleString();
            let valueAddedTax = parseInt(
                data.item.value_added_tax,
            ).toLocaleString();
            let tax = parseInt(data.tax).toLocaleString();
            let handlingFee = parseInt(data.handling_fee).toLocaleString();
            let total = parseInt(data.total).toLocaleString();
            let motorcycleRegistrationSupportFee = parseInt(
                data.motorcycle_registration_support_fee,
            ).toLocaleString();
            let registrationFee = parseInt(
                data.registration_fee,
            ).toLocaleString();
            let licensePlateRegistrationFee = parseInt(
                data.license_plate_registration_fee,
            ).toLocaleString();

            crud.field("price").input.value = price;
            crud.field("value_added_tax").input.value = valueAddedTax;
            crud.field("tax").input.value = tax;
            crud.field("motorcycle_registration_support_fee").input.value =
                motorcycleRegistrationSupportFee;
            crud.field("registration_fee").input.value = registrationFee;
            crud.field("license_plate_registration_fee").input.value =
                licensePlateRegistrationFee;
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
