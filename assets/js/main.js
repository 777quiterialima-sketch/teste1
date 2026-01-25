document.addEventListener('DOMContentLoaded', () => {
    const typeField = document.getElementById('transactionType');
    const accountDest = document.getElementById('accountDest');
    const cardField = document.getElementById('cardId');
    const paymentMethod = document.getElementById('paymentMethod');

    const toggleFields = () => {
        if (!typeField) {
            return;
        }
        const type = typeField.value;
        if (accountDest) {
            accountDest.disabled = type !== 'transfer';
        }
        if (paymentMethod && cardField) {
            const isCard = paymentMethod.value === 'cartao';
            cardField.disabled = !isCard;
        }
    };

    if (typeField) {
        typeField.addEventListener('change', toggleFields);
    }
    if (paymentMethod) {
        paymentMethod.addEventListener('change', toggleFields);
    }
    toggleFields();
});
