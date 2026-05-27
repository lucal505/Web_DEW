const CHART_COLORS = {
    bar: '#ff8da1',
    line: '#db7093',
    lineArea: 'rgba(255, 182, 193, 0.4)',
    marker: '#ff1493'
};

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
    ctxLine: document.getElementById('chartLine'),
    sentenceSelect: document.getElementById('filter-sentence'),
    countFilters: document.getElementById('count-filters'),
    filterSecondary: document.getElementById('filter-secondary'),
    tertiaryContainer: document.getElementById('tertiary-container')
};

const activeCharts = { bar: null, line: null };

let currentPage = 1;
let currentTotalPages = 1;
let currentApiUrl = '';
let startYear = 2020;
let endYear = 2026;

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
                to: sliderValue => Math.round(sliderValue),
                from: sliderValue => Math.round(sliderValue)
            }
        });

        yearSlider.noUiSlider.on('update', function (sliderValues) {
            startYear = parseInt(sliderValues[0]);
            endYear = parseInt(sliderValues[1]);
            
            if (startYear === endYear) {
                yearDisplay.innerText = startYear;
            } else {
                yearDisplay.innerText = `${startYear} - ${endYear}`;
            }
        });
    }
});

async function loadDynamicOptions(table, elementId, defaultValue = "Toate") {
    const selectElement = document.getElementById(elementId);
    if (!selectElement) return;

    selectElement.replaceChildren();
    const defaultOpt = document.createElement('option');
    defaultOpt.value = "";
    defaultOpt.textContent = defaultValue;
    selectElement.appendChild(defaultOpt);

    try {
        const response = await fetch(`../api/options/${table}`);
        const rawApiResponse = await response.json();
        
        const parsedData = rawApiResponse.data ? rawApiResponse.data : rawApiResponse;

        let dynamicOptionsArray = [];

        if (parsedData && typeof parsedData === 'object') {
            if (elementId === 'filter-drug' && parsedData.drugs) {
                dynamicOptionsArray = parsedData.drugs;
            } else if (elementId === 'filter-text-beneficiary' && parsedData.ben_types) {
                dynamicOptionsArray = parsedData.ben_types;
            } else if (elementId === 'filter-sentence' && parsedData.sentences) {
                dynamicOptionsArray = parsedData.sentences;
            } else {
                const targetKey = Object.keys(parsedData).find(keyName => keyName !== 'years' && keyName !== 'categories' && keyName !== 'id');
                if (targetKey && parsedData[targetKey]) {
                    dynamicOptionsArray = parsedData[targetKey];
                }
            }
        } else if (Array.isArray(parsedData)) {
            dynamicOptionsArray = parsedData;
        }

        if (typeof dynamicOptionsArray === 'object' && !Array.isArray(dynamicOptionsArray)) {
            dynamicOptionsArray = Object.values(dynamicOptionsArray);
        }

        if (Array.isArray(dynamicOptionsArray) && dynamicOptionsArray.length > 0) {
            dynamicOptionsArray.forEach(optionItem => {
                let extractedValue = "";
                
                if (typeof optionItem === 'object' && optionItem !== null) {
                    extractedValue = optionItem.name || optionItem.drug_name || optionItem.drug_type || optionItem.sentence_type || Object.values(optionItem)[0];
                } else {
                    extractedValue = optionItem;
                }

                if (extractedValue) {
                    extractedValue = extractedValue.charAt(0).toUpperCase() + extractedValue.slice(1);
                    const newOption = document.createElement('option');
                    newOption.value = extractedValue;
                    newOption.textContent = extractedValue;
                    selectElement.appendChild(newOption);
                }
            });
        }
    } catch (error) {
        console.error(`Eroare la preluarea opțiunilor pentru ${elementId}:`, error);
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
    PAGE_ELEMENTS.chartSelector.addEventListener('change', function(eventObject) {
        const selectedValue = eventObject.target.value;
        document.querySelectorAll('.view-section').forEach(sectionElement => {
            sectionElement.style.display = (selectedValue === 'all' || sectionElement.id === selectedValue) ? 'block' : 'none';
        });
    });
}

function createDomSelect(id, className, options, onChangeCallback = null) {
    const select = document.createElement('select');
    select.id = id;
    select.className = className;
    
    options.forEach(opt => {
        const option = document.createElement('option');
        option.value = opt.value;
        option.textContent = opt.label;
        select.appendChild(option);
    });
    
    if (onChangeCallback) {
        select.addEventListener('change', onChangeCallback);
    }
    return select;
}

function createDomInput(id, placeholder, widthStyle) {
    const input = document.createElement('input');
    input.type = 'text';
    input.id = id;
    input.className = 'elegant-input';
    input.placeholder = placeholder;
    if (widthStyle) input.style.width = widthStyle;
    return input;
}

function updateSecondaryFilters() {
    const table = PAGE_ELEMENTS.tableSelect.value;

    if (PAGE_ELEMENTS.drugInput) PAGE_ELEMENTS.drugInput.style.display = 'none';
    if (PAGE_ELEMENTS.sentenceSelect) PAGE_ELEMENTS.sentenceSelect.style.display = 'none';
    
    PAGE_ELEMENTS.dynamicFilters.replaceChildren();

    const countFilters = document.getElementById('count-filters');
    if (table === 'general' || table === 'groups') {
        if (countFilters) countFilters.style.display = 'none';
    } else {
        if (countFilters) countFilters.style.display = 'flex';
    }

    if (table === 'seizures' || table === 'emergencies') {
        if (PAGE_ELEMENTS.drugInput) PAGE_ELEMENTS.drugInput.style.display = 'block';
        loadDynamicOptions(table, 'filter-drug', 'Toate drogurile');
    }

    if (table === 'seizures') {
        const select = createDomSelect('filter-secondary', 'elegant-select', [
            { value: '', label: 'Toate' },
            { value: 'seizures_count', label: 'Număr de Capturi' },
            { value: 'grams', label: 'Grame' },
            { value: 'tablets', label: 'Comprimate' },
            { value: 'doses_units', label: 'Doze' },
            { value: 'milliliters', label: 'Mililitri' }
        ]);
        PAGE_ELEMENTS.dynamicFilters.appendChild(select);

    } else if (table === 'emergencies') {
        const select = createDomSelect('filter-secondary', 'elegant-select', [
            { value: '', label: 'Toate Categoriile' },
            { value: 'gender', label: 'Sex' },
            { value: 'age', label: 'Vârstă' },
            { value: 'administration_route', label: 'Cale Administrare' },
            { value: 'consumption_pattern', label: 'Mod Consum' },
            { value: 'diagnosis', label: 'Diagnostic' }
        ], updateTertiaryFilter);

        const tertiaryContainer = document.createElement('span');
        tertiaryContainer.id = 'tertiary-container';
        tertiaryContainer.style.display = 'flex';

        PAGE_ELEMENTS.dynamicFilters.appendChild(select);
        PAGE_ELEMENTS.dynamicFilters.appendChild(tertiaryContainer);

    } else if (table === 'demographics') {
        const selectGender = createDomSelect('filter-gender', 'elegant-select', [
            { value: '', label: 'Tot (Sex)' },
            { value: 'Bărbați', label: 'Bărbați' },
            { value: 'Femei', label: 'Femei' }
        ]);
        const selectAge = createDomSelect('filter-age', 'elegant-select', [
            { value: '', label: 'Tot (Vârstă)' },
            { value: 'Minori', label: 'Minori' },
            { value: 'Majori', label: 'Majori' }
        ]);
        PAGE_ELEMENTS.dynamicFilters.appendChild(selectGender);
        PAGE_ELEMENTS.dynamicFilters.appendChild(selectAge);

    } else if (table === 'sentences' || table === 'articles') {
        const inputLaw = createDomInput('filter-text-law', 'Caută lege/articol...', '180px');
        PAGE_ELEMENTS.dynamicFilters.appendChild(inputLaw);

    } else if (table === 'projects' || table === 'campaigns') {
        const inputName = createDomInput('filter-text-name', 'Caută după nume...', '180px');
        PAGE_ELEMENTS.dynamicFilters.appendChild(inputName);

    } else if (table === 'activities') {
        const inputSetting = createDomInput('filter-text-setting', 'Caută după mediu...', '150px');
        
        const selectBeneficiary = createDomSelect('filter-text-beneficiary', 'elegant-select', [
            { value: '', label: 'Alege beneficiar...' }
        ]);
        selectBeneficiary.style.width = '160px';

        PAGE_ELEMENTS.dynamicFilters.appendChild(inputSetting);
        PAGE_ELEMENTS.dynamicFilters.appendChild(selectBeneficiary);
        
        loadDynamicOptions(table, 'filter-text-beneficiary', 'Toți beneficiarii');
    }
}

function updateTertiaryFilter() {
    const secondarySelect = document.getElementById('filter-secondary');
    const categoryName = secondarySelect ? secondarySelect.value : '';
    const containerElement = document.getElementById('tertiary-container');
    
    if (!containerElement) return;

    containerElement.replaceChildren();

    if (!categoryName) return;

    const filterOptions = {
        gender: [
            { value: '', label: 'Toate' }, { value: 'Masculin', label: 'Masculin' }, { value: 'Feminin', label: 'Feminin' }
        ],
        age: [
            { value: '', label: 'Toate' }, { value: '<25', label: '<25' }, { value: '25-34', label: '25-34' }, { value: '>35', label: '>35' }
        ],
        administration_route: [
            { value: '', label: 'Toate' }, { value: 'Oral/fumat/prizat', label: 'Oral/fumat/prizat' }, { value: 'Injectabil', label: 'Injectabil' }, { value: 'Altele', label: 'Altele' }
        ],
        consumption_pattern: [
            { value: '', label: 'Toate' }, { value: 'Consum singular', label: 'Consum singular' }, { value: 'Consum combinat', label: 'Consum combinat' }
        ],
        diagnosis: [
            { value: '', label: 'Toate' }, { value: 'Intoxicație', label: 'Intoxicație' }, { value: 'Utilizare nocivă', label: 'Utilizare nocivă' }, { value: 'Dependență', label: 'Dependență' }, { value: 'Sevraj', label: 'Sevraj' }, { value: 'Tulburări de comportament', label: 'Tulburări de comportament' }, { value: 'Supradoză', label: 'Supradoză' }, { value: 'Testare toxicologică', label: 'Testare toxicologică' }
        ]
    };

    const optionsForCategory = filterOptions[categoryName];
    if (optionsForCategory) {
        const tertiarySelect = createDomSelect('filter-tertiary', 'elegant-select', optionsForCategory);
        containerElement.appendChild(tertiarySelect);
    }
}

function buildBaseUrl() {
    const table = PAGE_ELEMENTS.tableSelect.value;
    const selectedDrug = document.getElementById('filter-drug')?.value || '';
    const secondaryFilter = document.getElementById('filter-secondary');
    
    const exactCount = document.getElementById('filter-exact-count')?.value || '';
    const minCount = document.getElementById('filter-min-count')?.value.trim();
    const maxCount = document.getElementById('filter-max-count')?.value.trim();

    if (maxCount !== '' && Number(maxCount) <= 0) {
        displayMessage('Valoarea maximă trebuie să fie un număr mai mare decât 0!');
        return;
    }

    let generatedUrl = `../api/filters/${table}?`;
    
    if (startYear === endYear) {
        generatedUrl += `&year=${startYear}`;
    } else {
        generatedUrl += `&from=${startYear}&to=${endYear}`;
    }

    if (exactCount !== '') generatedUrl += `&total=${exactCount}`;
    if (minCount !== '') generatedUrl += `&min=${minCount}`;
    if (maxCount !== '' && maxCount !== null && Number(maxCount) > 0) generatedUrl += `&max=${maxCount}`;

    const textLaw = document.getElementById('filter-text-law')?.value;
    const textName = document.getElementById('filter-text-name')?.value;
    const textSetting = document.getElementById('filter-text-setting')?.value;
    const textBeneficiary = document.getElementById('filter-text-beneficiary')?.value;

    if (table === 'sentences' || table === 'articles') {
    if (textLaw) generatedUrl += `&law=${encodeURIComponent(textLaw)}`;
    
    if (table === 'sentences') {
        const sentenceValue = document.getElementById('filter-sentence')?.value;
        if (sentenceValue) generatedUrl += `&sentence_type=${encodeURIComponent(sentenceValue)}`;
        }
    }
    
    if ((table === 'projects' || table === 'campaigns') && textName) {
        generatedUrl += `&name=${encodeURIComponent(textName)}`;
    }
    
    if (table === 'activities') {
        if (textSetting) generatedUrl += `&set=${encodeURIComponent(textSetting)}`;
        if (textBeneficiary) generatedUrl += `&ben_type=${encodeURIComponent(textBeneficiary)}`;
    }

    if (table === 'demographics') {
        const genderValue = document.getElementById('filter-gender')?.value;
        const ageValue = document.getElementById('filter-age')?.value;
        if (genderValue) generatedUrl += `&gender=${encodeURIComponent(genderValue)}`;
        if (ageValue) generatedUrl += `&age=${encodeURIComponent(ageValue)}`;
    }

    if (table === 'emergencies' && secondaryFilter?.value) {
        generatedUrl += `&type=${secondaryFilter.value}`;
        const tertiaryFilter = document.getElementById('filter-tertiary');
        if (tertiaryFilter?.value) generatedUrl += `&val=${encodeURIComponent(tertiaryFilter.value)}`;
    }

    if (table === 'seizures' && secondaryFilter?.value) {
        generatedUrl += `&type=${encodeURIComponent(secondaryFilter.value)}`;
    }
    
    if (selectedDrug && (table === 'seizures' || table === 'emergencies')) {
        generatedUrl += `&drug=${encodeURIComponent(selectedDrug)}`;
    }

    return generatedUrl;
}

async function handleLoadData() {
    const apiGeneratedUrl = buildBaseUrl();
    if (!apiGeneratedUrl) return;

    currentApiUrl = apiGeneratedUrl;
    currentPage = 1;
    displayMessage("Se încarcă datele...");

    try {
        const fetchResponse = await fetch(currentApiUrl);
        const jsonData = await fetchResponse.json();

        const dataArray = jsonData.data ? jsonData.data : (Array.isArray(jsonData) ? jsonData : []);

        if (dataArray.length === 0) {
            destroyCharts();
            displayMessage(`0 Rezultate. Nu există date pentru selecția curentă.`);
            
            PAGE_ELEMENTS.tableHeader.innerHTML = '';
            PAGE_ELEMENTS.tableBody.innerHTML = '<tr><td colspan="100%" style="text-align:center;">0 rezultate.</td></tr>';
            
            PAGE_ELEMENTS.btnPrev.style.display = 'none';
            PAGE_ELEMENTS.btnNext.style.display = 'none';
            PAGE_ELEMENTS.currentPageSpan.innerText = '0';
            PAGE_ELEMENTS.totalPagesSpan.innerText = '0';
            
            return;
        }

        const table = PAGE_ELEMENTS.tableSelect.value;
        const secondaryFilter = document.getElementById('filter-secondary');
        const secondaryValue = secondaryFilter ? secondaryFilter.value : null;

        const chartData = extractChartData(dataArray, table, secondaryValue);

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
    fetchPaginatedData(currentPage);
}

async function fetchPaginatedData(pageNumber) {
    try {
        const response = await fetch(`${currentApiUrl}&page=${pageNumber}`);
        const resultData = await response.json();

        if (resultData.data && resultData.pagination) {
            currentPage = resultData.pagination.page;
            currentTotalPages = resultData.pagination.total_pages;

            PAGE_ELEMENTS.currentPageSpan.innerText = currentPage;
            PAGE_ELEMENTS.totalPagesSpan.innerText = currentTotalPages;

            PAGE_ELEMENTS.btnPrev.disabled = (currentPage <= 1);
            PAGE_ELEMENTS.btnNext.disabled = (currentPage >= currentTotalPages);

            renderTable(resultData.data);
        } else if (Array.isArray(resultData)) {
            renderTable(resultData);
            PAGE_ELEMENTS.btnPrev.style.display = 'none';
            PAGE_ELEMENTS.btnNext.style.display = 'none';
        }
    } catch (error) {
        console.error("Eroare la paginare:", error);
    }
}

PAGE_ELEMENTS.btnPrev.addEventListener('click', () => {
    if (currentPage > 1) fetchPaginatedData(currentPage - 1);
});

PAGE_ELEMENTS.btnNext.addEventListener('click', () => {
    if (currentPage < currentTotalPages) fetchPaginatedData(currentPage + 1);
});

function renderTable(dataArray) {
    PAGE_ELEMENTS.tableHeader.replaceChildren();
    PAGE_ELEMENTS.tableBody.replaceChildren();

    if (!dataArray || dataArray.length === 0) {
        const emptyRow = document.createElement('tr');
        const emptyCell = document.createElement('td');
        emptyCell.colSpan = 100;
        emptyCell.className = 'text-center'; // Folosim clasa CSS în loc de style="text-align:center;"
        emptyCell.textContent = '0 rezultate. Nu există date.';
        
        emptyRow.appendChild(emptyCell);
        PAGE_ELEMENTS.tableBody.appendChild(emptyRow);
        return;
    }

    const table = PAGE_ELEMENTS.tableSelect.value;
    const secondaryElement = document.getElementById('filter-secondary');
    const secondaryValue = secondaryElement ? secondaryElement.value : '';

    let tableColumns = [];

    switch (table) {
        case 'seizures':
        if (!secondaryValue) {
            tableColumns = [
                { headerTitle: 'An',          columnWidth: '80px',  getCellValue: (rowItem) => rowItem.year  || '-' },
                { headerTitle: 'Drog',        columnWidth: '150px', getCellValue: (rowItem) => rowItem.drug  || '-' },
                { headerTitle: 'Nr. Capturi', columnWidth: '120px', getCellValue: (rowItem) => rowItem.count ?? '-' },
                { headerTitle: 'Grame',       columnWidth: '110px', getCellValue: (rowItem) => rowItem.grams ?? '-' },
                { headerTitle: 'Comprimate',  columnWidth: '120px', getCellValue: (rowItem) => rowItem.tabs  ?? '-' },
                { headerTitle: 'Doze',        columnWidth: '100px', getCellValue: (rowItem) => rowItem.doses ?? '-' },
                { headerTitle: 'Mililitri',   columnWidth: '110px', getCellValue: (rowItem) => rowItem.mills ?? '-' },
            ];
        } else {
            tableColumns = [
                { headerTitle: 'An',   columnWidth: '80px',  getCellValue: (rowItem) => rowItem.year || '-' },
                { headerTitle: 'Drog', columnWidth: '150px', getCellValue: (rowItem) => rowItem.drug_name || rowItem.drug || '-' },
                { headerTitle: measureLabels[secondaryValue] || 'Valoare', columnWidth: '150px', getCellValue: (rowItem) => rowItem[secondaryValue] ?? rowItem.count ?? '-' },
            ];
        }
        break;

        case 'emergencies':
            tableColumns = [
                { headerTitle: 'An',           columnWidth: '80px',  getCellValue: (rowItem) => rowItem.year || '-' },
                { headerTitle: 'Drog',         columnWidth: '180px', getCellValue: (rowItem) => rowItem.drug || rowItem.drug_type || '-' },
                { headerTitle: categoryLabels[secondaryValue] || 'Categorie', columnWidth: '200px', getCellValue: (rowItem) => rowItem.value || '-' },
                { headerTitle: 'Cazuri / Total', columnWidth: '120px', getCellValue: (rowItem) => rowItem.count || '-' },
            ];
            break;

        case 'demographics':
            tableColumns = [
                { headerTitle: 'An',               columnWidth: '80px',  getCellValue: (rowItem) => rowItem.year || '-' },
                { headerTitle: 'Sex',              columnWidth: '150px', getCellValue: (rowItem) => rowItem.gender || '-' },
                { headerTitle: 'Categorie vârstă', columnWidth: '200px', getCellValue: (rowItem) => rowItem.age || '-' },
                { headerTitle: 'Total',            columnWidth: '100px', getCellValue: (rowItem) => rowItem.count ?? '-' },
            ];
            break;

        case 'sentences':
            tableColumns = [
                { headerTitle: 'An',             columnWidth: '80px',  getCellValue: (rowItem) => rowItem.year || '-' },
                { headerTitle: 'Tip sentință',   columnWidth: '220px', getCellValue: (rowItem) => rowItem.sentence || '-' },
                { headerTitle: 'Referință lege', columnWidth: '180px', getCellValue: (rowItem) => rowItem.law || '-' },
                { headerTitle: 'Total',          columnWidth: '100px', getCellValue: (rowItem) => rowItem.count ?? '-' },
            ];
            break;

        case 'articles':
            tableColumns = [
                { headerTitle: 'An',            columnWidth: '80px',  getCellValue: (rowItem) => rowItem.year || '-' },
                { headerTitle: 'Articol legal', columnWidth: '280px', getCellValue: (rowItem) => rowItem.article || '-' },
                { headerTitle: 'Total',         columnWidth: '100px', getCellValue: (rowItem) => rowItem.count ?? '-' },
            ];
            break;

        case 'general':
            tableColumns = [
                { headerTitle: 'An',                   columnWidth: '80px',  getCellValue: (rowItem) => rowItem.year || '-' },
                { headerTitle: 'Persoane Cercetate',   columnWidth: '200px', getCellValue: (rowItem) => rowItem.investigated ?? '-' },
                { headerTitle: 'Persoane Trimise',     columnWidth: '200px', getCellValue: (rowItem) => rowItem.indicted ?? '-' },
                { headerTitle: 'Persoane Condamnate',  columnWidth: '200px', getCellValue: (rowItem) => rowItem.convicted ?? '-' },
            ];
            break;

        case 'activities':
            tableColumns = [
            { headerTitle: 'An',              columnWidth: '80px',  getCellValue: (rowItem) => rowItem.year || '-' },
            { headerTitle: 'Mediu',           columnWidth: '200px', getCellValue: (rowItem) => rowItem.set || rowItem.setting || '-' },
            { headerTitle: 'Nr. activități',  columnWidth: '130px', getCellValue: (rowItem) => rowItem.activities || rowItem.activities_count || '-' },
            { headerTitle: 'Nr. beneficiari', columnWidth: '140px', getCellValue: (rowItem) => rowItem.beneficiaries || rowItem.count || '-' },
            { headerTitle: 'Tip beneficiar',  columnWidth: '150px', getCellValue: (rowItem) => rowItem.ben_type || rowItem.beneficiary_type || '-' },
            ];
            break;

        case 'campaigns':
            tableColumns = [
                { headerTitle: 'An',              columnWidth: '80px',  getCellValue: (rowItem) => rowItem.year || '-' },
                { headerTitle: 'Nume campanie',   columnWidth: '320px', getCellValue: (rowItem) => rowItem.name || '-' },
                { headerTitle: 'Nr. beneficiari', columnWidth: '140px',getCellValue: (rowItem) => rowItem.count ?? '-' },
            ];
            break;

        case 'projects':
            tableColumns = [
                { headerTitle: 'An',              columnWidth: '80px',  getCellValue: (rowItem) => rowItem.year || '-' },
                { headerTitle: 'Nume proiect',    columnWidth: '320px', getCellValue: (rowItem) => rowItem.name || '-' },
                { headerTitle: 'Nr. beneficiari', columnWidth: '140px', getCellValue: (rowItem) => rowItem.count || '-' },
            ];
            break;

            case 'groups':
            tableColumns = [
                { headerTitle: 'An', columnWidth: '80px', getCellValue: (rowItem) => rowItem.year || '-' },
                { headerTitle: 'Grupuri Identificate', columnWidth: '200px', getCellValue: (rowItem) => rowItem.groups ?? rowItem.identified_groups ?? '-' },
                { headerTitle: 'Persoane Implicate', columnWidth: '200px', getCellValue: (rowItem) => rowItem.persons ?? rowItem.involved_persons ?? '-' },
            ];
            break;

            default:
            if (!dataArray || dataArray.length === 0) return;
            const filteredKeys = Object.keys(dataArray[0]).filter(keyName => keyName !== 'id');
            tableColumns = filteredKeys.map(keyName => ({
                headerTitle: keyName.replace(/_/g, ' ').toUpperCase(),
                columnWidth: '150px',
                getCellValue: (rowItem) => rowItem[keyName] !== null ? rowItem[keyName] : '-'
            }));
    }

    tableColumns.forEach(columnItem => {
        const tableHeaderCell = document.createElement('th');
        tableHeaderCell.innerText = columnItem.headerTitle;
        tableHeaderCell.style.width = columnItem.columnWidth;
        tableHeaderCell.style.minWidth = columnItem.columnWidth;
        PAGE_ELEMENTS.tableHeader.appendChild(tableHeaderCell);
    });

    dataArray.forEach(rowItem => {
        const tableRow = document.createElement('tr');
        tableColumns.forEach(columnItem => {
            const tableDataCell = document.createElement('td');
            tableDataCell.style.width = columnItem.columnWidth;
            tableDataCell.style.minWidth = columnItem.columnWidth;
            tableDataCell.innerText = columnItem.getCellValue(rowItem);
            tableRow.appendChild(tableDataCell);
        });
        PAGE_ELEMENTS.tableBody.appendChild(tableRow);
    });
}

function extractChartData(apiData, table, secondaryValue) {
    let extractedLabels = [];
    let extractedValues = [];

    const dataArray = apiData.data ? apiData.data : apiData;

    dataArray.forEach(dataItem => {
        if (table === 'seizures') {
            const numericValue = Number(dataItem[secondaryValue] || dataItem.count || 0);
            if (numericValue > 0) {
                extractedLabels.push(dataItem.drug_name || dataItem.drug_type || dataItem.drug);
                extractedValues.push(numericValue);
            }
        } else if (table === 'emergencies') {
            extractedLabels.push(`${dataItem.drug_type} (${dataItem.value})`);
            extractedValues.push(Number(dataItem.count));
        } else if (table === 'groups') {
            extractedLabels.push(String(dataItem.year));
            const val = Number(dataItem.groups || dataItem.identified_groups || 0);
            extractedValues.push(val);
        }       
        else {
            const matchedLabelKey = ['article', 'setting', 'name', 'project_name', 'campaign_name', 'gender', 'sentence', 'year'].find(keyName => dataItem[keyName] !== undefined);
            
            let matchedValueKey = secondaryValue;
            if (!matchedValueKey || dataItem[matchedValueKey] === undefined) {
                matchedValueKey = ['count', 'beneficiaries', 'activities', 'investigated', 'identified', 'involved'].find(keyName => dataItem[keyName] !== undefined);
            }
            
            if (matchedLabelKey && matchedValueKey) {
                extractedLabels.push(dataItem[matchedLabelKey]);
                extractedValues.push(Number(dataItem[matchedValueKey] || 0));
            }
        }
    });

    return { labels: extractedLabels, values: extractedValues };
}

function destroyCharts() {
    if (activeCharts.bar) { activeCharts.bar.destroy(); activeCharts.bar = null; }
    if (activeCharts.line) { activeCharts.line.destroy(); activeCharts.line = null; }
}

function renderCharts(labelsArray, valuesArray) {
    destroyCharts();

    const barChartOptions = {
        series: [{ name: 'Valoare', data: valuesArray }],
        chart: { type: 'bar', height: 350, fontFamily: 'inherit', toolbar: { show: false } },
        plotOptions: { bar: { borderRadius: 4, horizontal: false } },
        dataLabels: { enabled: false },
        colors: [CHART_COLORS.bar],
        xaxis: { categories: labelsArray }
    };
    activeCharts.bar = new ApexCharts(PAGE_ELEMENTS.ctxBar, barChartOptions);
    activeCharts.bar.render();

    const lineChartOptions = {
        series: [{ name: 'Valoare', data: valuesArray }],
        chart: { type: 'area', height: 350, fontFamily: 'inherit', toolbar: { show: false } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        colors: [CHART_COLORS.line],
        fill: { type: 'solid', color: CHART_COLORS.lineArea },
        markers: { size: 4, colors: [CHART_COLORS.marker], strokeColors: '#fff', strokeWidth: 2 },
        xaxis: { categories: labelsArray }
    };
    activeCharts.line = new ApexCharts(PAGE_ELEMENTS.ctxLine, lineChartOptions);
    activeCharts.line.render();
}

function exportData(exportFormat) {
    const table = PAGE_ELEMENTS.tableSelect.value;
    const currentParams = currentApiUrl.split('?')[1] || '';
    window.location.href = `../api/export/${table}?format=${exportFormat}&${currentParams}`;
};

async function exportTable(exportFormat) {
    const tableContainer = PAGE_ELEMENTS.containerTable;
    const titleEl = tableContainer.querySelector('h3');
    const exportButtons = tableContainer.querySelector('.export-table-buttons');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');

    // ascunde
    if (titleEl) titleEl.style.visibility = 'hidden';
    if (exportButtons) exportButtons.style.visibility = 'hidden';
    if (btnPrev) btnPrev.style.visibility = 'hidden';
    if (btnNext) btnNext.style.visibility = 'hidden';

    if (exportFormat === 'png' || exportFormat === 'webp') {
        const generatedCanvas = await html2canvas(tableContainer, {
            backgroundColor: '#ffffff',
            scale: 2
        });
        const targetMimeType = exportFormat === 'webp' ? 'image/webp' : 'image/png';
        const canvasDataURL = generatedCanvas.toDataURL(targetMimeType, 1.0);
        downloadBase64File(canvasDataURL, `tabel.${exportFormat}`);
    }

    if (titleEl) titleEl.style.visibility = '';
    if (exportButtons) exportButtons.style.visibility = '';
    if (btnPrev) btnPrev.style.visibility = '';
    if (btnNext) btnNext.style.visibility = '';


    if (exportFormat === 'svg') {
        const targetTable = tableContainer.querySelector('table');
        const tableRows = targetTable.querySelectorAll('tr');

        const columnWidths = [];
        tableRows[0].querySelectorAll('th').forEach(headerCell => columnWidths.push(headerCell.offsetWidth));

        const rowHeightValue = 36;
        const totalTableWidth = columnWidths.reduce((sumValue, widthValue) => sumValue + widthValue, 0);
        const totalTableHeight = tableRows.length * rowHeightValue + 12;

        let generatedSvgRows = '';
        tableRows.forEach((rowElement, rowIndex) => {
            const currentCells = rowElement.querySelectorAll('th, td');
            let positionX = 0;
            const positionY = rowIndex * rowHeightValue;
            const isHeaderRow = rowIndex === 0;

            if (isHeaderRow) {
                generatedSvgRows += `<rect x="0" y="${positionY}" width="${totalTableWidth}" height="${rowHeightValue}" fill="#fce4ec"/>`;
            } else if (rowIndex % 2 === 0) {
                generatedSvgRows += `<rect x="0" y="${positionY}" width="${totalTableWidth}" height="${rowHeightValue}" fill="#fff5f7"/>`;
            }

            currentCells.forEach((cellElement, colIndex) => {
                const cellWidthValue = columnWidths[colIndex] || 120;
                generatedSvgRows += `<text x="${positionX + 10}" y="${positionY + 23}" font-family="Arial" font-size="13" fill="${isHeaderRow ? '#c0507a' : '#333'}" font-weight="${isHeaderRow ? 'bold' : 'normal'}">${cellElement.innerText}</text>`;
                positionX += cellWidthValue;
            });
        });

        const finalSvgData = `<svg xmlns="http://www.w3.org/2000/svg" width="${totalTableWidth}" height="${totalTableHeight}">${generatedSvgRows}</svg>`;
        const fileBlob = new Blob([finalSvgData], { type: 'image/svg+xml;charset=utf-8' });
        const objectUrl = URL.createObjectURL(fileBlob);
        const downloadLink = document.createElement('a');
        downloadLink.href = objectUrl;
        downloadLink.download = 'tabel.svg';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
        URL.revokeObjectURL(objectUrl);
    }
};

async function downloadChart(containerElementId, exportFormat) {
    let selectedChartInstance;
    if (containerElementId === 'chartBar') selectedChartInstance = activeCharts.bar;
    if (containerElementId === 'chartLine') selectedChartInstance = activeCharts.line;
    if (!selectedChartInstance) return;

    if (exportFormat === 'svg') {
        const chartSvgElement = document.getElementById(containerElementId).querySelector('svg');
        if (chartSvgElement) {
            if (!chartSvgElement.getAttribute('xmlns')) chartSvgElement.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            const serializedSvgData = new XMLSerializer().serializeToString(chartSvgElement);
            const finalBlob = new Blob([serializedSvgData], { type: 'image/svg+xml;charset=utf-8' });
            const temporaryObjectUrl = URL.createObjectURL(finalBlob);
            const downloadAnchor = document.createElement('a');
            downloadAnchor.href = temporaryObjectUrl;
            downloadAnchor.download = `grafic-${containerElementId}.svg`;
            document.body.appendChild(downloadAnchor);
            downloadAnchor.click();
            document.body.removeChild(downloadAnchor);
            URL.revokeObjectURL(temporaryObjectUrl);
        }
        return;
    }

    const { imgURI } = await selectedChartInstance.dataURI();

    if (exportFormat === 'png') {
        downloadBase64File(imgURI, `grafic-${containerElementId}.png`);
        return;
    }

    if (exportFormat === 'webp') {
        const tempImage = new Image();
        tempImage.src = imgURI;
        tempImage.onload = function() {
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = tempImage.width;
            tempCanvas.height = tempImage.height;
            const canvasContext = tempCanvas.getContext('2d');
            canvasContext.fillStyle = '#ffffff';
            canvasContext.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
            canvasContext.drawImage(tempImage, 0, 0);
            downloadBase64File(tempCanvas.toDataURL('image/webp', 1.0), `grafic-${containerElementId}.webp`);
        };
    }
};

function downloadBase64File(base64DataString, targetFilename) {
    const downloadLinkElement = document.createElement('a');
    downloadLinkElement.href = base64DataString;
    downloadLinkElement.download = targetFilename;
    document.body.appendChild(downloadLinkElement);
    downloadLinkElement.click();
    document.body.removeChild(downloadLinkElement);
}

PAGE_ELEMENTS.tableSelect.addEventListener('change', updateSecondaryFilters);
PAGE_ELEMENTS.btnLoad.addEventListener('click', handleLoadData);

//raport general
document.getElementById('btn-export-csv')?.addEventListener('click', () => exportData('csv'));
document.getElementById('btn-export-html')?.addEventListener('click', () => exportData('html'));

//tabel
document.getElementById('btn-export-table-png')?.addEventListener('click', () => exportTable('png'));
document.getElementById('btn-export-table-webp')?.addEventListener('click', () => exportTable('webp'));
document.getElementById('btn-export-table-svg')?.addEventListener('click', () => exportTable('svg'));

//bar chart
document.getElementById('btn-export-bar-png')?.addEventListener('click', () => downloadChart('chartBar', 'png'));
document.getElementById('btn-export-bar-webp')?.addEventListener('click', () => downloadChart('chartBar', 'webp'));
document.getElementById('btn-export-bar-svg')?.addEventListener('click', () => downloadChart('chartBar', 'svg'));

//line chart
document.getElementById('btn-export-line-png')?.addEventListener('click', () => downloadChart('chartLine', 'png'));
document.getElementById('btn-export-line-webp')?.addEventListener('click', () => downloadChart('chartLine', 'webp'));
document.getElementById('btn-export-line-svg')?.addEventListener('click', () => downloadChart('chartLine', 'svg'));

updateSecondaryFilters();
displayMessage("Selectează criteriile dorite și apasă 'Filtrează Date' pentru a vedea datele.");