let draggedCard = null;

document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('dragstart', e => {
            draggedCard = card;
            e.dataTransfer.effectAllowed = 'move';
        });
    });

    document.querySelectorAll('.column').forEach(column => {
        column.addEventListener('dragover', e => {
            e.preventDefault();
        });

        column.addEventListener('drop', e => {
            e.preventDefault();

            if (!draggedCard) return;

            const cardId = draggedCard.dataset.cardId;
            const fromColumn = draggedCard.dataset.columnId;
            const toColumn = column.dataset.columnId;

            moveCard(cardId, fromColumn, toColumn, 0);
        });
    });
});

function moveCard(cardId, fromColumn, toColumn, position) {
    fetch('/cards/move', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': window.CSRF_TOKEN
        },
        body: new URLSearchParams({
            card_id: cardId,
            from_column: fromColumn,
            to_column: toColumn,
            position: position
        })
    })
    .then(() => location.reload());
}
