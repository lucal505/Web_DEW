const API_BASE_URL = '../api/index.php';

const CHART_COLORS = {
    pie: ['#ff1493', '#ffa07a', '#da70d6', '#ff69b4', '#9370db', '#db7093', '#ffb6c1', '#ba55d3', '#d8bfd8', '#ff00ff'],
    bar: '#ff8da1',
    border: '#ffffff'
};

const PAGE_ELEMENTS = {
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

// afiseaza mesaj in loc de grafic (ex: eroare sau zero rezultate)
function displayMessage(message) {
    PAGE_ELEMENTS.chartsWrapper.style.display = 'none';
    PAGE_ELEMENTS.statusMessage.style.display = 'block';
    PAGE_ELEMENTS.statusMessage.innerText = message;
}

function hideMessage() {
    PAGE_ELEMENTS.statusMessage.style.display = 'none';
    PAGE_ELEMENTS.chartsWrapper.style.display = 'grid';
}

// filtrele secundare
function updateDynamicFilters() {
    const table = PAGE_ELEMENTS.tableSelect.value;
    let html = '';

    // campul pt drog
    if (table === 'drug_seizures' || table === 'medical_emergencies') {
        PAGE_ELEMENTS.drugInput.style.display = 'inline-block';
    } else {
        PAGE_ELEMENTS.drugInput.style.display = 'none';
        PAGE_ELEMENTS.drugInput.value = ''; 
    }

    // filtre specifice capturilor
    if (table === 'drug_seizures') {
        html = `
            <select id="filter-secondary">
                <option value="seizures_count">Număr Capturi</option>
                <option value="grams">Grame</option>
                <option value="tablets">Comprimate</option>
                <option value="doses_units">Doze</option>
                <option value="milliliters">Mililitri</option>
            </select>
        `;
        PAGE_ELEMENTS.dynamicFilters.innerHTML = html;
    } 

    // filtre specifice urgentelor medicale
    else if (table === 'medical_emergencies') {
        html = `
            <select id="filter-secondary" onchange="updateTertiaryFilter()">
                <option value="">Toate categoriile</option>
                <option value="gender">Sex</option>
                <option value="age">Vârstă</option>
                <option value="administration_route">Cale de Administrare</option>
                <option value="consumption_pattern">Mod de Consumare</option>
                <option value="diagnosis">Diagnostic</option>
            </select>
            <span id="tertiary-container"></span>
        `;
        PAGE_ELEMENTS.dynamicFilters.innerHTML = html;
    } else {
        PAGE_ELEMENTS.dynamicFilters.innerHTML = '';
    }
}

window.updateTertiaryFilter = function() {
    const category = document.getElementById('filter-secondary').value;
    const container = document.getElementById('tertiary-container');
    let html = '';

    if (category === 'gender') {
        html = `
            <select id="filter-tertiary">
                <option value="">Toate</option>
                <option value="Masculin">Masculin</option>
                <option value="Feminin">Feminin</option>
            </select>
        `;
    } else if (category === 'age') {
        html = `
            <select id="filter-tertiary">
                <option value="">Toate</option>
                <option value="<25"> <25 </option>
                <option value="25-34"> 25-34 </option>
                <option value=">35"> >35 </option>
            </select>
        `;
    } else if (category === 'administration_route') {
        html = `
            <select id="filter-tertiary">
                <option value="">Toate</option>
                <option value="Oral/fumat/prizat">Oral/fumat/prizat</option>
                <option value="Injectabil">Injectabil</option>
                <option value="Altele">Altele</option>
            </select>
        `;
    } else if (category === 'consumption_pattern') {
        html = `
            <select id="filter-tertiary">
                <option value="">Toate</option>
                <option value="Consum singular">Consum singular</option>
                <option value="Consum combinat">Consum combinat</option>
            </select>
        `;
    } else if (category === 'diagnosis') {
        html = `
            <select id="filter-tertiary">
                <option value="">Toate</option>
                <option value="Intoxicație">Intoxicație</option>
                <option value="Utilizare nocivă">Utilizare nocivă</option>
                <option value="Dependență">Dependență</option>
                <option value="Sevraj">Sevraj</option>
                <option value="Tulburări de comportament">Tulburări de comportament</option>
                <option value="Supradoză">Supradoză</option>
                <option value="Testare toxicologică">Testare toxicologică</option>
            </select>
        `;
    }

    container.innerHTML = html;
};

// extrage si incarca datele
async function handleLoadData() {
    const table = PAGE_ELEMENTS.tableSelect.value;
    const year = PAGE_ELEMENTS.yearInput.value;
    const drug = PAGE_ELEMENTS.drugInput.value;
    const secondaryFilter = document.getElementById('filter-secondary');

    // opreste daca lipseste anul
    if (!year) {
        displayMessage("Te rugăm să introduci un an (ex: 2022).");
        return;
    }

    displayMessage("Se încarcă datele...");

    let url = `${API_BASE_URL}?route=filters&table=${table}&year=${year}`;
    
    // Filtre dinamice aplicate corect, cu tot cu acolade
    if (table === 'medical_emergencies' && secondaryFilter) {
        if (secondaryFilter.value) {
             url += `&category=${secondaryFilter.value}`;
        }

        const tertiaryFilter = document.getElementById('filter-tertiary');
        if (tertiaryFilter && tertiaryFilter.value !== '') {
             url += `&value=${encodeURIComponent(tertiaryFilter.value)}`;
        }
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
        PAGE_ELEMENTS.ctxPie.style.display = 'none';
        PAGE_ELEMENTS.pieDownloads.style.display = 'none';
        PAGE_ELEMENTS.containerLine.style.display = 'none';
        PAGE_ELEMENTS.singleStatCard.style.display = 'block';
        PAGE_ELEMENTS.singleStatLabel.innerText = label;
        PAGE_ELEMENTS.singleStatValue.innerText = value.toLocaleString('ro-RO');
    } 
    else {
        PAGE_ELEMENTS.ctxPie.style.display = 'block';
        PAGE_ELEMENTS.pieDownloads.style.display = 'block';
        PAGE_ELEMENTS.containerLine.style.display = 'flex';
        PAGE_ELEMENTS.singleStatCard.style.display = 'none';
    }
}

function renderCharts(labels, values) {
    destroyCharts();

    if (labels.length === 1) {
        toggleSingleStat(true, labels[0], values[0]);
    } else {
        toggleSingleStat(false);

        activeCharts.pie = new Chart(PAGE_ELEMENTS.ctxPie, {
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

    activeCharts.bar = new Chart(PAGE_ELEMENTS.ctxBar, {
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

    activeCharts.line = new Chart(PAGE_ELEMENTS.ctxLine, {
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

// Funcția de export modificată pentru a include noile filtre
window.exportData = function(format) {
    const table = PAGE_ELEMENTS.tableSelect.value;
    const year = PAGE_ELEMENTS.yearInput.value;
    const drug = PAGE_ELEMENTS.drugInput.value;
    const secondaryFilter = document.getElementById('filter-secondary');
    const tertiaryFilter = document.getElementById('filter-tertiary');

    if (!year) {
        alert("Te rugăm să introduci un an pentru a putea exporta (ex: 2022).");
        return;
    }

    let url = `${API_BASE_URL}?route=export&table=${table}&year=${year}&format=${format}`;

    if (table === 'medical_emergencies' && secondaryFilter && secondaryFilter.value) {
        url += `&category=${encodeURIComponent(secondaryFilter.value)}`;
    }
    
    if (tertiaryFilter && tertiaryFilter.value !== '') {
        url += `&value=${encodeURIComponent(tertiaryFilter.value)}`;
    }

    if (drug && (table === 'drug_seizures' || table === 'medical_emergencies')) {
        url += `&drug=${encodeURIComponent(drug)}`;
    }

    window.location.href = url;
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

PAGE_ELEMENTS.tableSelect.addEventListener('change', updateDynamicFilters);
PAGE_ELEMENTS.btnLoad.addEventListener('click', handleLoadData);

updateDynamicFilters();
displayMessage("Selectează criteriile și apasă 'Filtrează Date' pentru a începe analiza.");