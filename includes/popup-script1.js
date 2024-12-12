document.addEventListener("DOMContentLoaded", function () {
//    const specificWord = "' . esc_js( $specific_word ) . '";
    console.log('test')
    const specificWord = "Syros"

    if (window.location.href.includes(specificWord)) {
        const promoPopup = document.getElementById("syros-promo-popup");
        promoPopup.style.display = "block";

        const closeBtn = document.getElementById("close-syros-promo-popup");
        closeBtn.addEventListener("click", function () {
            promoPopup.style.display = "none";
        });

        // Close popup when clicking on the background
        promoPopup.addEventListener("click", function (event) {
            // Ensure the click is outside the content area
            if (event.target === promoPopup) {
                promoPopup.style.display = "none";
            }
        });
    }
});