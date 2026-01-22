document.addEventListener('DOMContentLoaded', () => {
    const categoryContainer = document.getElementById('category-bars');
    if (categoryContainer) {
        const rows = document.querySelectorAll('[data-chart="category"] .chart-row');
        const values = Array.from(rows).map(row => parseFloat(row.dataset.value || '0'));
        const max = Math.max(...values, 1);
        rows.forEach(row => {
            const bar = document.createElement('div');
            bar.className = 'progress';
            const fill = document.createElement('div');
            fill.className = 'progress-bar';
            const value = parseFloat(row.dataset.value || '0');
            fill.style.width = `${(value / max) * 100}%`;
            bar.appendChild(fill);
            categoryContainer.appendChild(bar);
        });
    }

    const monthlyBars = document.querySelectorAll('#monthly-bars .bar-group');
    if (monthlyBars.length) {
        const receitaValues = Array.from(monthlyBars).map(group => parseFloat(group.dataset.receita || '0'));
        const despesaValues = Array.from(monthlyBars).map(group => parseFloat(group.dataset.despesa || '0'));
        const max = Math.max(...receitaValues, ...despesaValues, 1);
        monthlyBars.forEach(group => {
            const receita = parseFloat(group.dataset.receita || '0');
            const despesa = parseFloat(group.dataset.despesa || '0');
            const receitaBar = group.querySelector('.bar.receita');
            const despesaBar = group.querySelector('.bar.despesa');
            receitaBar.style.height = `${(receita / max) * 120}px`;
            despesaBar.style.height = `${(despesa / max) * 120}px`;
        });
    }
});
