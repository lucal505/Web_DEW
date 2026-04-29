const API_BASE_URL = '../api/index.php';

const CHART_COLORS = {
    pie: ['#ff1493', '#ffa07a', '#da70d6', '#ff69b4', '#9370db', '#db7093', '#ffb6c1', '#ba55d3', '#d8bfd8', '#ff00ff'],
    bar: '#ff8da1',
    border: '#ffffff'
};

// referinte elemente html
const DOM = {
    tableSelect: document.getElementById('filter-table'),
    yearInput: document.getElementById('filter-year'),
    drugInput: document.getElementById('filter-drug'),
    dynamicFilters: document.getElementById('dynamic-filters'),
    btnLoad: document.getElementById('btn-load'),
    chartsWrapper: document.getElementById('charts-wrapper'),
    statusMessage: document.getElementById('status-message'),
    ctxPie: document.getElementById('chartPie'),
    ctxBar: document.getElementById('chartBar'),
    ctxLine: document.getElementById('chartLine'),
    containerLine: document.getElementById('container-line'),
    singleStatCard: document.getElementById('single-stat-card'),
    singleStatLabel: document.getElementById('single-stat-label'),
    singleStatValue: document.getElementById('single-stat-value'),
    pieDownloads: document.getElementById('pie-downloads')
};

// starea curenta a graficelor
const activeCharts = { pie: null, bar: null, line: null };

// afiseaza mesaj in loc de grafic (ex: eroare, zero rezultate)
function displayMessage(message) {
    DOM.chartsWrapper.style.display = 'none';
    DOM.statusMessage.style.display = 'block';
    DOM.statusMessage.innerText = message;
}

function hideMessage() {
    DOM.statusMessage.style.display = 'none';
    DOM.chartsWrapper.style.display = 'grid';
}

// filtrele secundare
function updateDynamicFilters() {
    const table = DOM.tableSelect.value;
    let html = '';

    // campul pt drog
    if (table === 'drug_seizures' || table === 'medical_emergencies') {
        DOM.drugInput.style.display = 'inline-block';
    } else {
        DOM.drugInput.style.display = 'none';
        DOM.drugInput.value = ''; 
    }

    // filtre specifice capturilor
    if (table === 'drug_seizures') {
        html = `
            <select id="filter-secondary">
                <option value="seizures_count">Număr Capturi</option>
                <option value="grams">Grame</option>
                <option value="tablets">Comprimate</option>
                <option value="doses_units">Doze/Bucăți</option>
                <option value="milliliters">Mililitri</option>
            </select>
        `;
    } 
    // filtre specifice urgentelor
    else if (table === 'medical_emergencies') {
        html = `
            <select id="filter-secondary">
                <option value="gender">Sex</option>
                <option value="age">Vârstă</option>
                <option value="administration_route">Cale Administrare</option>
                <option value="consumption_pattern">Model Consum</option>
                <option value="diagnosis">Diagnostic</option>
            </select>
        `;
    }

    DOM.dynamicFilters.innerHTML = html;
}

// extrage si incarca datele
async function handleLoadData() {
    const table = DOM.tableSelect.value;
    const year = DOM.yearInput.value;
    const drug = DOM.drugInput.value;
    const secondaryFilter = document.getElementById('filter-secondary');

    // opreste daca lipseste anul
    if (!year) {
        displayMessage("Te rugăm să introduci un an (ex: 2022).");
        return;
    }

    displayMessage("Se încarcă datele...");

    let url = `${API_BASE_URL}?route=filters&table=${table}&year=${year}`;
    if (table === 'medical_emergencies' && secondaryFilter) {
        url += `&category=${secondaryFilter.value}`;
    }
    if (drug && (table === 'drug_seizures' || table === 'medical_emergencies')) {
        url += `&drug=${encodeURIComponent(drug)}`;
    }

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || `Status: ${response.status}`);
        }
        if (data.error) throw new Error(data.error);
        
        if (!Array.isArray(data) || data.length === 0) {
            destroyCharts();
            displayMessage(`0 Rezultate. Nu există date pentru selecția curentă.`);
            return;
        }

        const secondaryValue = secondaryFilter ? secondaryFilter.value : null;
        const chartData = extractChartData(data, table, secondaryValue);
        
        if (chartData.labels.length === 0) {
            destroyCharts();
            displayMessage(`Valoarea este 0 pentru această selecție.`);
            return;
        }

        hideMessage(); 
        renderCharts(chartData.labels, chartData.values);

    } catch (error) {
        console.error(error);
        displayMessage(`Eroare: ${error.message}`);
    }
}

function extractChartData(apiData, table, secondaryValue) {
    let labels = [];
    let values = [];

    apiData.forEach(item => {
        if (table === 'drug_seizures') {
            const val = Number(item[secondaryValue]);
            if (val > 0) {
                labels.push(item.drug_name);
                values.push(val);
            }
        } 
        else if (table === 'medical_emergencies') {
            labels.push(`${item.drug_type} (${item.value})`);
            values.push(Number(item.count));
        } 
        else {
            const labelKey = ['legal_article', 'setting', 'project_name', 'campaign_name'].find(k => item[k] !== undefined);
            const valueKey = ['count', 'beneficiaries_count'].find(k => item[k] !== undefined);
            if (labelKey && valueKey) {
                labels.push(item[labelKey]);
                values.push(Number(item[valueKey]));
            }
        }
    });
    return { labels, values };
}

// reseteaza toate graficele
function destroyCharts() {
    if (activeCharts.pie) activeCharts.pie.destroy();
    if (activeCharts.bar) activeCharts.bar.destroy();
    if (activeCharts.line) activeCharts.line.destroy();
}

function toggleSingleStat(isSingle, label = "", value = 0) {
    // afiseaza doar text daca avem un singur rezultat (ex: un singur drog sau o singura cale de administrare)
    if (isSingle) {
        DOM.ctxPie.style.display = 'none';
        DOM.pieDownloads.style.display = 'none';
        DOM.containerLine.style.display = 'none';
        DOM.singleStatCard.style.display = 'block';
        DOM.singleStatLabel.innerText = label;
        DOM.singleStatValue.innerText = value.toLocaleString('ro-RO');
    } 
    else {
        DOM.ctxPie.style.display = 'block';
        DOM.pieDownloads.style.display = 'block';
        DOM.containerLine.style.display = 'flex';
        DOM.singleStatCard.style.display = 'none';
    }
}

function renderCharts(labels, values) {
    destroyCharts();

    if (labels.length === 1) {
        toggleSingleStat(true, labels[0], values[0]);
    } else {
        toggleSingleStat(false);

        activeCharts.pie = new Chart(DOM.ctxPie, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: CHART_COLORS.pie,
                    borderColor: CHART_COLORS.border,
                    borderWidth: 2
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'bottom',
                        labels: {
                            boxWidth: 15,
                            padding: 15,
                            font: { size: 11 }
                        }
                    } 
                } 
            }
        });
    }

    activeCharts.bar = new Chart(DOM.ctxBar, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Statistici',
                data: values,
                backgroundColor: CHART_COLORS.bar,
                borderRadius: 4
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    activeCharts.line = new Chart(DOM.ctxLine, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Trend / Vârfuri',
                data: values,
                borderColor: '#db7093',
                backgroundColor: 'rgba(255, 182, 193, 0.4)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ff1493'
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            scales: { y: { beginAtZero: true } } 
        }
    });
}

window.exportData = function(format) {
    const table = DOM.tableSelect.value;
    const year = DOM.yearInput.value;
    window.location.href = `${API_BASE_URL}?route=export&table=${table}&year=${year}&format=${format}`;
};

// functie pt descarcarea chart urilor
window.downloadChart = function(canvasId, format) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const link = document.createElement('a');
    link.download = `vizualizare-${canvasId}.${format}`;
    link.href = canvas.toDataURL(`image/${format === 'svg' ? 'png' : format}`);
    link.click();
};

DOM.tableSelect.addEventListener('change', updateDynamicFilters);
DOM.btnLoad.addEventListener('click', handleLoadData);

updateDynamicFilters();
displayMessage("Selectează criteriile și apasă 'Filtrează Date' pentru a începe analiza.");