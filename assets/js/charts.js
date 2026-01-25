(function () {
    const data = window.reportData || { categories: [], incomeExpense: [] };

    const categoryCanvas = document.getElementById('categoryChart');
    if (categoryCanvas) {
        const ctx = categoryCanvas.getContext('2d');
        const total = data.categories.reduce((sum, item) => sum + Number(item.total), 0) || 1;
        let startAngle = 0;
        data.categories.forEach((item, index) => {
            const slice = (Number(item.total) / total) * Math.PI * 2;
            ctx.beginPath();
            ctx.moveTo(100, 100);
            ctx.fillStyle = ['#2ebd59', '#ffb703', '#fb7185', '#38bdf8', '#6366f1'][index % 5];
            ctx.arc(100, 100, 90, startAngle, startAngle + slice);
            ctx.closePath();
            ctx.fill();
            startAngle += slice;
        });
    }

    const incomeCanvas = document.getElementById('incomeExpenseChart');
    if (incomeCanvas) {
        const ctx = incomeCanvas.getContext('2d');
        const width = incomeCanvas.width = 300;
        const height = incomeCanvas.height = 200;
        ctx.clearRect(0, 0, width, height);
        const max = Math.max(...data.incomeExpense.map(item => Math.max(item.income, item.expense)), 1);
        const barWidth = width / (data.incomeExpense.length * 2 + 1);

        data.incomeExpense.forEach((item, index) => {
            const incomeHeight = (item.income / max) * (height - 20);
            const expenseHeight = (item.expense / max) * (height - 20);
            const x = (index * 2 + 1) * barWidth;

            ctx.fillStyle = '#2ebd59';
            ctx.fillRect(x, height - incomeHeight, barWidth - 4, incomeHeight);
            ctx.fillStyle = '#e55353';
            ctx.fillRect(x + barWidth, height - expenseHeight, barWidth - 4, expenseHeight);
        });
    }
})();
