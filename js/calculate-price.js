

document.addEventListener("DOMContentLoaded", function () {
    const adultInput = document.getElementById("adults");
    const childInput = document.getElementById("children");
    const totalPriceElement = document.getElementById("total-price");

    const adultPrice = 7; // Precio por adulto
    const childPrice = 5; // Precio por niño

    function updateTotalPrice() {
        const adults = parseInt(adultInput.value) || 0;
        const children = parseInt(childInput.value) || 0;
        const totalPrice = adults * adultPrice + children * childPrice;
        totalPriceElement.textContent = totalPrice.toFixed(2); // Actualizar el precio total
    }

    // Escuchar cambios en los campos de adultos y niños
    adultInput.addEventListener("input", updateTotalPrice);
    childInput.addEventListener("input", updateTotalPrice);
    updateTotalPrice()

});
