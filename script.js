document.addEventListener("DOMContentLoaded", () => {
    const boutonsSuppression =
        document.querySelectorAll(".confirmer-suppression");

    boutonsSuppression.forEach(bouton => {
        bouton.addEventListener("click", evenement => {
            const confirmation = confirm(
                "Voulez-vous vraiment supprimer cette marchandise ?"
            );

            if (!confirmation) {
                evenement.preventDefault();
            }
        });
    });
});
