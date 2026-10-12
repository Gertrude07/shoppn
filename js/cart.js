// Cart page: live totals while typing, and save the new quantity automatically.
document.addEventListener('DOMContentLoaded', function () {
    var rows = document.querySelectorAll('[data-cart-row]');
    var summary = document.querySelector('[data-cart-summary]');
    if (!rows.length || !summary) return;

    function money(symbol, currency, amount) {
        var text = amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        return currency === 'GHS' ? symbol + ' ' + text : symbol + text;
    }

    function readQty(input) {
        var qty = parseInt(input.value, 10);
        return isNaN(qty) || qty < 1 ? null : qty;
    }

    function refresh() {
        var totalQty = 0;
        var totalAmount = 0;

        rows.forEach(function (row) {
            var input = row.querySelector('input[name="qty"]');
            var qty = readQty(input) || parseInt(input.getAttribute('data-original'), 10) || 1;
            var unit = parseFloat(row.getAttribute('data-unit-price')) || 0;

            totalQty += qty;
            totalAmount += qty * unit;
            row.querySelector('[data-line-total]').textContent =
                money(row.getAttribute('data-symbol'), row.getAttribute('data-currency'), qty * unit);
        });

        summary.querySelector('[data-summary-qty]').textContent = totalQty;
        summary.querySelector('[data-summary-total]').textContent =
            money(summary.getAttribute('data-symbol') || '$', 'USD', totalAmount);
    }

    var saveTimer = null;

    rows.forEach(function (row) {
        var form = row.querySelector('.qty-form');
        var input = form.querySelector('input[name="qty"]');
        var updateBtn = form.querySelector('.update-btn');

        // Totals update instantly; the page saves the change on its own.
        updateBtn.classList.add('is-hidden');

        function save() {
            var qty = readQty(input);
            if (qty === null || String(qty) === input.getAttribute('data-original')) return;
            input.value = qty;
            form.classList.add('is-saving');
            form.submit();
        }

        function queueSave() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(save, 600);
        }

        input.addEventListener('input', function () {
            refresh();
            queueSave();
        });
        input.addEventListener('change', function () {
            clearTimeout(saveTimer);
            save();
        });

        form.querySelectorAll('.qty-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var current = readQty(input) || 1;
                var next = Math.max(1, current + parseInt(btn.getAttribute('data-step'), 10));
                input.value = next;
                refresh();
                queueSave();
            });
        });
    });
});
