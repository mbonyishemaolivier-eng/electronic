document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('aiGenerateDesc');
    if (!btn) return;

    btn.addEventListener('click', function () {
        var name = document.getElementById('name').value.trim();
        var categoryId = document.getElementById('category_id').value;
        var price = document.getElementById('price').value;
        var descField = document.getElementById('description');

        if (!name || !categoryId || !price) {
            alert('Please fill in product name, category, and price first.');
            return;
        }

        btn.disabled = true;
        btn.textContent = '🤖 Generating...';

        fetch('../api/ai-description.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name, category_id: categoryId, price: price })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.description) {
                descField.value = data.description;
            } else {
                alert(data.error || 'Could not generate description.');
            }
        })
        .catch(function () {
            alert('AI service error. Please try again.');
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = '🤖 AI Generate Description';
        });
    });
});
