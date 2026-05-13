const API_BASE_URL = '../api/index.php';

const CHART_COLORS = {
    bar: '#ff8da1',
    line: '#db7093',
    lineArea: 'rgba(255, 182, 193, 0.4)'
};

const PAGE_ELEMENTS = {
    tableSelect: document.getElementById('filter-table'),
    drugInput: document.getElementById('filter-drug'),
    dynamicFilters: document.getElementById('dynamic-filters'),
    minCountInput: document.getElementById('filter-min-count'),
    maxCountInput: document.getElementById('filter-max-count'),
    btnLoad: document.getElementById('btn-load'),
    chartsWrapper: document.getElementById('charts-wrapper'),
    statusMessage: document.getElementById('status-message'),
    viewSelectorContainer: document.getElementById('view-selector-container'),
    chartSelector: document.getElementById('chart-selector'),
    containerTable: document.getElementById('container-table'),
    tableHeader: document.getElementById('table-header'),
    tableBody: document.getElementById('table-body'),
    btnPrev: document.getElementById('btn-prev'),
    btnNext: document.getElementById('btn-next'),
    currentPageSpan: document.getElementById('current-page'),
    totalPagesSpan: document.getElementById('total-pages'),
    ctxBar: document.getElementById('chartBar'),
    ctxLine: document.getElementById('chartLine')
};

const activeCharts = { bar: null, line: null };

let currentPage = 1;
let currentTotalPages = 1;
let currentApiUrl = '';
let startYear = 2020;
let endYear = 2026;

// Inițializare Slider Ani
document.addEventListener('DOMContentLoaded', () => {
    const yearSlider = document.getElementById('year-slider');
    const yearDisplay = document.getElementById('year-display');

    if (yearSlider) {
        noUiSlider.create(yearSlider, {
            start: [2020, 2026],
            connect: true,
            step: 1,
            range: { 'min': 2020, 'max': 2026 },
            format: {
                to: value => Math.round(value),
                from: value => Math.round(value)
            }
        });

        yearSlider.noUiSlider.on('update', function (values) {
            startYear = parseInt(values[0]);
            endYear = parseInt(values[1]);
            
            if (startYear === endYear) {
                yearDisplay.innerText = startYear;
            } else {
                yearDisplay.innerText = `${startYear} - ${endYear}`;
            }
        });
    }
});

// Funcția inteligentă HIBRIDĂ
async function loadDynamicOptions(table, elementId, defaultValue = "Toate") {
    const select = document.getElementById(elementId);
    if (!select) return;

    // Resetăm dropdown-ul instant
    select.innerHTML = `<option value="">${defaultValue}</option>`;

    try {
        const response = await fetch(`${API_BASE_URL}?route=options&table=${table}`);
        const data = await response.json();

        let optionsArray = [];

        // Detecție automată inteligentă: Luăm direct array-ul care ne interesează 
        // (ignorând "years" sau "categories")
        if (data && typeof data === 'object' && !Array.isArray(data)) {
            const correctKey = Object.keys(data).find(key => key !== 'years' && key !== 'categories');
            
            if (correctKey && Array.isArray(data[correctKey])) {
                optionsArray = data[correctKey]; // Aici va extrage automat `data.sentence_types` sau `data.drugs`
            }
        } else if (Array.isArray(data)) {
            optionsArray = data;
        }

        // Punem datele brute, exact cum le-a trimis backend-ul, direct în dropdown
        if (optionsArray.length > 0) {
            optionsArray.forEach(opt => {
                let value = typeof opt === 'object' && opt !== null 
                    ? (opt.name || opt.drug_name || opt.drug_type || opt.sentence_type || Object.values(opt)[0]) 
                    : opt;

                if (value) {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = value;
                    select.appendChild(option);
                }
            });
        }
    } catch (error) {
        console.error(`Eroare la preluarea opțiunilor:`, error);
    }
}

function displayMessage(message) {
    PAGE_ELEMENTS.chartsWrapper.style.display = 'none';
    PAGE_ELEMENTS.viewSelectorContainer.style.display = 'none';
    PAGE_ELEMENTS.statusMessage.style.display = 'block';
    PAGE_ELEMENTS.statusMessage.innerText = message;
}

function hideMessage() {
    PAGE_ELEMENTS.statusMessage.style.display = 'none';
    PAGE_ELEMENTS.chartsWrapper.style.display = 'block';
    PAGE_ELEMENTS.viewSelectorContainer.style.display = 'block';
}

if (PAGE_ELEMENTS.chartSelector) {
    PAGE_ELEMENTS.chartSelector.addEventListener('change', function(e) {
        const selectedValue = e.target.value;
        document.querySelectorAll('.view-section').forEach(section => {
            section.style.display = (selectedValue === 'all' || section.id === selectedValue) ? 'block' : 'none';
        });
    });
}

function updateSecondaryFilters() {
    const table = PAGE_ELEMENTS.tableSelect.value;

    if(PAGE_ELEMENTS.drugInput) PAGE_ELEMENTS.drugInput.style.display = 'none';
    const sentenceSelect = document.getElementById('filter-sentence');
    if(sentenceSelect) sentenceSelect.style.display = 'none';
    PAGE_ELEMENTS.dynamicFilters.innerHTML = '';

    if (table === 'drug_seizures' || table === 'medical_emergencies') {
        PAGE_ELEMENTS.drugInput.style.display = 'block';
        loadDynamicOptions(table, 'filter-drug', 'Toate drogurile');
    }

    if (table === 'crimes_sentence') {
        if(sentenceSelect) sentenceSelect.style.display = 'block';
        loadDynamicOptions(table, 'filter-sentence', 'Toate sentințele');
    }

    if (table === 'drug_seizures') {
        PAGE_ELEMENTS.dynamicFilters.innerHTML = `
            <select id="filter-secondary" class="elegant-select">
                <option value="seizures_count">Număr de Capturi</option>
                <option value="grams">Grame</option>
                <option value="tablets">Comprimate</option>
                <option value="doses_units">Doze</option>
                <option value="milliliters">Mililitri</option>
            </select>
        `;
    } else if (table === 'medical_emergencies') {
        PAGE_ELEMENTS.dynamicFilters.innerHTML = `
            <select id="filter-secondary" class="elegant-select" onchange="updateTertiaryFilter()">
                <option value="">Alege o categorie...</option>
                <option value="gender">Sex</option>
                <option value="age">Vârstă</option>
                <option value="administration_route">Cale Administrare</option>
                <option value="consumption_pattern">Mod Consum</option>
                <option value="diagnosis">Diagnostic</option>
            </select>
            <span id="tertiary-container" style="display: flex;"></span>
        `;
    } else if (table === 'crimes_demographic') {
        PAGE_ELEMENTS.dynamicFilters.innerHTML = `
            <select id="filter-gender" class="elegant-select">
                <option value="">Tot (Sex)</option>
                <option value="Masculin">Masculin</option>
                <option value="Feminin">Feminin</option>
            </select>
            <select id="filter-age" class="elegant-select">
                <option value="">Tot (Vârstă)</option>
                <option value="Minori">Minori</option>
                <option value="Majori">Majori</option>
            </select>
        `;
    } 
    // --- FILTRELE TEXT NOI ---
    else if (table === 'crimes_sentence' || table === 'crimes_article') {
        PAGE_ELEMENTS.dynamicFilters.innerHTML = `
            <input type="text" id="filter-text-law" class="elegant-input" placeholder="Caută lege / articol..." style="width: 180px;">
        `;
    } else if (table === 'prevention_projects' || table === 'prevention_campaigns') {
        PAGE_ELEMENTS.dynamicFilters.innerHTML = `
            <input type="text" id="filter-text-name" class="elegant-input" placeholder="Caută după nume..." style="width: 180px;">
        `;
    } else if (table === 'prevention_activities') {
        PAGE_ELEMENTS.dynamicFilters.innerHTML = `
            <input type="text" id="filter-text-setting" class="elegant-input" placeholder="Mediu (ex: școală)" style="width: 150px;">
            <input type="text" id="filter-text-beneficiary" class="elegant-input" placeholder="Beneficiari (ex: elevi)" style="width: 160px;">
        `;
    }
}

window.updateTertiaryFilter = function() {
    const category = document.getElementById('filter-secondary').value;
    const container = document.getElementById('tertiary-container');
    const options = {
        gender: `<option value="">Toate</option><option value="Masculin">Masculin</option><option value="Feminin">Feminin</option>`,
        age: `<option value="">Toate</option><option value="<25">&lt;25</option><option value="25-34">25-34</option><option value=">35">&gt;35</option>`,
        administration_route: `<option value="">Toate</option><option value="Oral/fumat/prizat">Oral/fumat/prizat</option><option value="Injectabil">Injectabil</option><option value="Altele">Altele</option>`,
        consumption_pattern: `<option value="">Toate</option><option value="Consum singular">Consum singular</option><option value="Consum combinat">Consum combinat</option>`,
        diagnosis: `<option value="">Toate</option><option value="Intoxicație">Intoxicație</option><option value="Utilizare nocivă">Utilizare nocivă</option><option value="Dependență">Dependență</option><option value="Sevraj">Sevraj</option><option value="Tulburări de comportament">Tulburări de comportament</option><option value="Supradoză">Supradoză</option><option value="Testare toxicologică">Testare toxicologică</option>`
    };
    container.innerHTML = category ? `<select id="filter-tertiary" class="elegant-select">${options[category] || ''}</select>` : '';
};

function buildBaseUrl() {
    const table = PAGE_ELEMENTS.tableSelect.value;
    const drug = document.getElementById('filter-drug')?.value || '';
    const secondaryFilter = document.getElementById('filter-secondary');
    const minCount = document.getElementById('filter-min-count').value;
    const maxCount = document.getElementById('filter-max-count').value;

    let url = `${API_BASE_URL}?route=filters&table=${table}`;
    
    // Filtre Universale
    url += `&start_year=${startYear}&end_year=${endYear}`;
    if (startYear === endYear) url += `&year=${startYear}`;
    if (minCount !== '') url += `&min_count=${minCount}`;
    if (maxCount !== '') url += `&max_count=${maxCount}`;

    // Sentințe - Filtru fix
    const sentenceFilter = document.getElementById('filter-sentence');
    if (table === 'crimes_sentence' && sentenceFilter?.value) {
        url += `&sentence_type=${encodeURIComponent(sentenceFilter.value)}`;
    }

    // ==========================================
    // CAPTURAREA FILTRELOR TEXT (NOU)
    // ==========================================
    const textLaw = document.getElementById('filter-text-law')?.value;
    const textName = document.getElementById('filter-text-name')?.value;
    const textSetting = document.getElementById('filter-text-setting')?.value;
    const textBeneficiary = document.getElementById('filter-text-beneficiary')?.value;

    if ((table === 'crimes_sentence' || table === 'crimes_article') && textLaw) {
        url += `&law=${encodeURIComponent(textLaw)}`;
    }
    if ((table === 'prevention_projects' || table === 'prevention_campaigns') && textName) {
        url += `&name=${encodeURIComponent(textName)}`;
    }
    if (table === 'prevention_activities') {
        if (textSetting) url += `&setting=${encodeURIComponent(textSetting)}`;
        if (textBeneficiary) url += `&beneficiary_type=${encodeURIComponent(textBeneficiary)}`;
    }
    // ==========================================

    // Filtre Demografice
    if (table === 'crimes_demographic') {
        const genderVal = document.getElementById('filter-gender')?.value;
        const ageVal = document.getElementById('filter-age')?.value;
        if (genderVal) url += `&gender=${encodeURIComponent(genderVal)}`;
        if (ageVal) url += `&age_category=${encodeURIComponent(ageVal)}`;
    }

    // Filtre Urgențe
    if (table === 'medical_emergencies' && secondaryFilter?.value) {
        url += `&category=${secondaryFilter.value}`;
        const tertiaryFilter = document.getElementById('filter-tertiary');
        if (tertiaryFilter?.value) url += `&value=${encodeURIComponent(tertiaryFilter.value)}`;
    }

    // Filtre Capturi Droguri
    if (table === 'drug_seizures' && secondaryFilter?.value) {
        url += `&measurement=${encodeURIComponent(secondaryFilter.value)}`;
    }
    if (drug && (table === 'drug_seizures' || table === 'medical_emergencies')) {
        url += `&drug=${encodeURIComponent(drug)}`;
    }

    return url;
}
async function handleLoadData() {
    const url = buildBaseUrl();
    if (!url) return;

    currentApiUrl = url;
    currentPage = 1;
    displayMessage("Se încarcă datele...");

    fetchPaginatedData(currentPage);

    try {
        const response = await fetch(currentApiUrl);
        const data = await response.json();

        if (!response.ok) throw new Error(data.error || `Status: ${response.status}`);
        if (data.error) throw new Error(data.error);

        if (!Array.isArray(data) || data.length === 0) {
            if (!data.data) {
                destroyCharts();
                displayMessage(`0 Rezultate. Nu există date pentru selecția curentă.`);
                return;
            }
        }

        const table = PAGE_ELEMENTS.tableSelect.value;
        const secondaryFilter = document.getElementById('filter-secondary');
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

async function fetchPaginatedData(page) {
    try {
        const response = await fetch(`${currentApiUrl}&page=${page}`);
        const result = await response.json();

        if (result.data && result.pagination) {
            currentPage = result.pagination.page;
            currentTotalPages = result.pagination.total_pages;

            PAGE_ELEMENTS.currentPageSpan.innerText = currentPage;
            PAGE_ELEMENTS.totalPagesSpan.innerText = currentTotalPages;

            PAGE_ELEMENTS.btnPrev.disabled = (currentPage <= 1);
            PAGE_ELEMENTS.btnNext.disabled = (currentPage >= currentTotalPages);

            renderTable(result.data);
        } else if (Array.isArray(result)) {
            renderTable(result);
            PAGE_ELEMENTS.btnPrev.style.display = 'none';
            PAGE_ELEMENTS.btnNext.style.display = 'none';
        }
    } catch (err) {
        console.error("Eroare la paginare:", err);
    }
}

PAGE_ELEMENTS.btnPrev.addEventListener('click', () => {
    if (currentPage > 1) fetchPaginatedData(currentPage - 1);
});

PAGE_ELEMENTS.btnNext.addEventListener('click', () => {
    if (currentPage < currentTotalPages) fetchPaginatedData(currentPage + 1);
});

function renderTable(dataArray) {
    PAGE_ELEMENTS.tableHeader.innerHTML = '';
    PAGE_ELEMENTS.tableBody.innerHTML = '';

    if (!dataArray || dataArray.length === 0) {
        PAGE_ELEMENTS.tableBody.innerHTML = '<tr><td colspan="100%" style="text-align:center;">0 rezultate. Nu există date.</td></tr>';
        return;
    }

    const table = PAGE_ELEMENTS.tableSelect.value;
    const secondaryEl = document.getElementById('filter-secondary');
    const secondaryValue = secondaryEl ? secondaryEl.value : '';

    // Afișează anul singur sau intervalul (ex: 2022 sau 2020-2026)
    const yearDisplay = (startYear === endYear) ? startYear : `${startYear} - ${endYear}`;

    const measureLabels = {
        seizures_count: 'Nr. capturi',
        grams: 'Grame',
        tablets: 'Comprimate',
        doses_units: 'Doze',
        milliliters: 'Mililitri'
    };

    const categoryLabels = {
        gender: 'Sex',
        age: 'Grupă de vârstă',
        administration_route: 'Cale de administrare',
        consumption_pattern: 'Mod de consum',
        diagnosis: 'Diagnostic'
    };

    let columns = [];

    // Restaurăm mapările tale originale pentru fiecare tabel!
    // ACUM FOLOSIM row.year PENTRU A AFIȘA ANUL EXACT DIN BAZA DE DATE!
    switch (table) {
        case 'drug_seizures':
            columns = [
                { header: 'An',      width: '80px',  getValue: (row) => row.year || '-' },
                { header: 'Drog',    width: '200px', getValue: (row) => row.drug_name || '-' },
                { header: measureLabels[secondaryValue] || 'Valoare', width: '150px', getValue: (row) => row[secondaryValue] || '-' },
            ];
            break;

        case 'medical_emergencies':
            columns = [
                { header: 'An',           width: '80px',  getValue: (row) => row.year || '-' },
                { header: 'Drog',         width: '180px', getValue: (row) => row.drug_type || '-' },
                { header: categoryLabels[secondaryValue] || 'Categorie', width: '200px', getValue: (row) => row.value || '-' },
                { header: 'Cazuri / Total', width: '120px', getValue: (row) => row.count || '-' },
            ];
            break;

        case 'crimes_demographic':
            columns = [
                { header: 'An',               width: '80px',  getValue: (row) => row.year || '-' },
                { header: 'Sex',              width: '150px', getValue: (row) => row.gender || '-' },
                { header: 'Categorie vârstă', width: '200px', getValue: (row) => row.age_category || '-' },
                { header: 'Total',            width: '100px', getValue: (row) => row.count || '-' },
            ];
            break;

        case 'crimes_sentence':
            columns = [
                { header: 'An',             width: '80px',  getValue: (row) => row.year || '-' },
                { header: 'Tip sentință',   width: '220px', getValue: (row) => row.sentence_type || '-' },
                { header: 'Referință lege', width: '180px', getValue: (row) => row.law_reference || '-' },
                { header: 'Total',          width: '100px', getValue: (row) => row.count || '-' },
            ];
            break;

        case 'crimes_article':
            columns = [
                { header: 'An',            width: '80px',  getValue: (row) => row.year || '-' },
                { header: 'Articol legal', width: '280px', getValue: (row) => row.legal_article || '-' },
                { header: 'Total',         width: '100px', getValue: (row) => row.count || '-' },
            ];
            break;

        case 'prevention_activities':
            columns = [
                { header: 'An',              width: '80px',  getValue: (row) => row.year || '-' },
                { header: 'Mediu',           width: '200px', getValue: (row) => row.setting || '-' },
                { header: 'Nr. activități',  width: '130px', getValue: (row) => row.activities_count || '-' },
                { header: 'Nr. beneficiari', width: '140px', getValue: (row) => row.beneficiaries_count || '-' },
                { header: 'Tip beneficiar',  width: '150px', getValue: (row) => row.beneficiary_type || '-' },
            ];
            break;

        case 'prevention_campaigns':
            columns = [
                { header: 'An',              width: '80px',  getValue: (row) => row.year || '-' },
                { header: 'Nume campanie',   width: '320px', getValue: (row) => row.campaign_name || '-' },
                { header: 'Nr. beneficiari', width: '140px', getValue: (row) => row.beneficiaries_count || '-' },
            ];
            break;

        case 'prevention_projects':
            columns = [
                { header: 'An',              width: '80px',  getValue: (row) => row.year || '-' },
                { header: 'Nume proiect',    width: '320px', getValue: (row) => row.project_name || '-' },
                { header: 'Nr. beneficiari', width: '140px', getValue: (row) => row.beneficiaries_count || '-' },
            ];
            break;

        default:
            const keys = Object.keys(dataArray[0]).filter(k => k !== 'category' && k !== 'id');
            columns = keys.map(key => ({
                header: key.replace(/_/g, ' ').toUpperCase(),
                width: '150px',
                getValue: (row) => row[key] !== null ? row[key] : '-'
            }));
    }

    // Punem headerele corecte
    columns.forEach(col => {
        const th = document.createElement('th');
        th.innerText = col.header;
        th.style.width = col.width;
        th.style.minWidth = col.width;
        PAGE_ELEMENTS.tableHeader.appendChild(th);
    });

    // Punem datele rând cu rând pe coloanele specifice
    dataArray.forEach(row => {
        const tr = document.createElement('tr');
        columns.forEach(col => {
            const td = document.createElement('td');
            td.style.width = col.width;
            td.style.minWidth = col.width;
            td.innerText = col.getValue(row);
            tr.appendChild(td);
        });
        PAGE_ELEMENTS.tableBody.appendChild(tr);
    });
}
function extractChartData(apiData, table, secondaryValue) {
    let labels = [];
    let values = [];

    const dataArray = apiData.data ? apiData.data : apiData;

    dataArray.forEach(item => {
        if (table === 'drug_seizures') {
            const val = Number(item[secondaryValue || 'seizures_count']);
            if (val > 0) {
                labels.push(item.drug_name || item.drug_type);
                values.push(val);
            }
        } else if (table === 'medical_emergencies') {
            labels.push(`${item.drug_type} (${item.value})`);
            values.push(Number(item.count));
        } else {
            const labelKey = ['legal_article', 'setting', 'project_name', 'campaign_name', 'gender', 'sentence_type'].find(k => item[k] !== undefined);
            const valueKey = ['count', 'beneficiaries_count'].find(k => item[k] !== undefined);
            if (labelKey && valueKey) {
                labels.push(item[labelKey]);
                values.push(Number(item[valueKey]));
            }
        }
    });

    return { labels, values };
}

function destroyCharts() {
    if (activeCharts.bar) { activeCharts.bar.destroy(); activeCharts.bar = null; }
    if (activeCharts.line) { activeCharts.line.destroy(); activeCharts.line = null; }
}

function renderCharts(labels, values) {
    destroyCharts();

    const barOptions = {
        series: [{ name: 'Statistici', data: values }],
        chart: { type: 'bar', height: 350, fontFamily: 'inherit', toolbar: { show: false } },
        plotOptions: { bar: { borderRadius: 4, horizontal: false } },
        dataLabels: { enabled: false },
        colors: [CHART_COLORS.bar],
        xaxis: { categories: labels }
    };
    activeCharts.bar = new ApexCharts(PAGE_ELEMENTS.ctxBar, barOptions);
    activeCharts.bar.render();

    const lineOptions = {
        series: [{ name: 'Trend', data: values }],
        chart: { type: 'area', height: 350, fontFamily: 'inherit', toolbar: { show: false } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        colors: [CHART_COLORS.line],
        fill: { type: 'solid', opacity: 0.2 },
        markers: { size: 4, colors: ['#ff1493'], strokeColors: '#fff', strokeWidth: 2 },
        xaxis: { categories: labels }
    };
    activeCharts.line = new ApexCharts(PAGE_ELEMENTS.ctxLine, lineOptions);
    activeCharts.line.render();
}

window.exportData = function(format) {
    const url = buildBaseUrl();
    if (!url) return;
    window.location.href = url.replace('route=filters', `route=export&format=${format}`);
};

window.exportTable = async function(format) {
    const tableContainer = PAGE_ELEMENTS.containerTable;

    if (format === 'png' || format === 'webp') {
        const canvas = await html2canvas(tableContainer, {
            backgroundColor: '#ffffff',
            scale: 2
        });
        const mimeType = format === 'webp' ? 'image/webp' : 'image/png';
        const dataURL = canvas.toDataURL(mimeType, 1.0);
        downloadBase64File(dataURL, `tabel.${format}`);
    }

    if (format === 'svg') {
        const table = tableContainer.querySelector('table');
        const rows = table.querySelectorAll('tr');

        const colWidths = [];
        rows[0].querySelectorAll('th').forEach(th => colWidths.push(th.offsetWidth));

        const rowHeight = 36;
        const totalWidth = colWidths.reduce((a, b) => a + b, 0);
        const totalHeight = rows.length * rowHeight + 12;

        let svgRows = '';
        rows.forEach((row, rowIndex) => {
            const cells = row.querySelectorAll('th, td');
            let x = 0;
            const y = rowIndex * rowHeight;
            const isHeader = rowIndex === 0;

            if (isHeader) {
                svgRows += `<rect x="0" y="${y}" width="${totalWidth}" height="${rowHeight}" fill="#fce4ec"/>`;
            } else if (rowIndex % 2 === 0) {
                svgRows += `<rect x="0" y="${y}" width="${totalWidth}" height="${rowHeight}" fill="#fff5f7"/>`;
            }

            cells.forEach((cell, colIndex) => {
                const w = colWidths[colIndex] || 120;
                svgRows += `<text x="${x + 10}" y="${y + 23}" font-family="Arial" font-size="13" fill="${isHeader ? '#c0507a' : '#333'}" font-weight="${isHeader ? 'bold' : 'normal'}">${cell.innerText}</text>`;
                x += w;
            });
        });

        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${totalWidth}" height="${totalHeight}">${svgRows}</svg>`;
        const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'tabel.svg';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }
};

window.downloadChart = async function(containerId, format) {
    let chartInstance;
    if (containerId === 'chartBar') chartInstance = activeCharts.bar;
    if (containerId === 'chartLine') chartInstance = activeCharts.line;
    if (!chartInstance) return;

    if (format === 'svg') {
        const svgElement = document.getElementById(containerId).querySelector('svg');
        if (svgElement) {
            if (!svgElement.getAttribute('xmlns')) svgElement.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            const svgData = new XMLSerializer().serializeToString(svgElement);
            const blob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `grafic-${containerId}.svg`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }
        return;
    }

    const { imgURI } = await chartInstance.dataURI();

    if (format === 'png') {
        downloadBase64File(imgURI, `grafic-${containerId}.png`);
        return;
    }

    if (format === 'webp') {
        const img = new Image();
        img.src = imgURI;
        img.onload = function() {
            const canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0);
            downloadBase64File(canvas.toDataURL('image/webp', 1.0), `grafic-${containerId}.webp`);
        };
    }
};

function downloadBase64File(base64Data, filename) {
    const link = document.createElement('a');
    link.href = base64Data;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

PAGE_ELEMENTS.tableSelect.addEventListener('change', updateSecondaryFilters);
PAGE_ELEMENTS.btnLoad.addEventListener('click', handleLoadData);

// Inițializare garantată la final
updateSecondaryFilters();
displayMessage("Selectează criteriile dorite și apasă 'Filtrează Date' pentru a vedea datele.");