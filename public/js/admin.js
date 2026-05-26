const msgDiv = document.getElementById("message");
const loginForm = document.getElementById("loginForm");
const uploadSection = document.getElementById("uploadSection");

function showMessage(text, isError = false) {
    msgDiv.textContent = text;
    msgDiv.style.color = isError ? "var(--danger-color)" : "var(--success-color)";
    msgDiv.style.backgroundColor = isError ? "#fff5f5" : "#f5fff7";
    msgDiv.style.border = `1px solid ${isError ? "var(--danger-color)" : "var(--success-color)"}`;
}

function checkSession() {
    const adminContainer = document.querySelector('.container');
    const token = localStorage.getItem('jwt_token');

    if (token) {
        loginForm.style.display = 'none';
        uploadSection.style.display = 'block';
        adminContainer.classList.add('large-mode'); 
    } else {
        loginForm.style.display = 'block';
        uploadSection.style.display = 'none';
        adminContainer.classList.remove('large-mode'); 
    }
}

// auth
loginForm.addEventListener('submit', (submitEvent) => {
    submitEvent.preventDefault();
    const formData = new FormData();
    formData.append('username', document.getElementById('username').value);
    formData.append('password', document.getElementById('password').value);

    fetch('../api/auth/login', { method: 'POST', body: formData })
        .then(serverResponse => serverResponse.json())
        .then(responseData => {
            if (responseData.success) {
                localStorage.setItem('jwt_token', responseData.token);
                showMessage('Autentificare reușită!');
                checkSession();
            } else {
                showMessage(responseData.message, true);
            }
        });
});

document.getElementById('logoutBtn').addEventListener('click', () => {
    fetch('../api/auth/logout', { method: 'POST' })
        .then(() => {
            localStorage.removeItem('jwt_token');
            showMessage('Sesiune închisă.');
            checkSession();
        });
});

document.getElementById("uploadForm").addEventListener("submit", (submitEvent) => {
    submitEvent.preventDefault();
    const formData = new FormData();
    formData.append("fileToUpload", document.getElementById("fileToUpload").files[0]);

    fetch("../api/admin/import/upload", { 
        method: "POST", 
        headers: { 'Authorization': 'Bearer ' + localStorage.getItem('jwt_token') },
        body: formData 
    })
    .then(serverResponse => serverResponse.json())
    .then(responseData => {
        if (responseData.success) {
            showMessage(responseData.message);
            document.getElementById("uploadForm").reset();
        } else {
            showMessage(responseData.message, true);
        }
    })
    .catch(() => showMessage("Eroare de rețea la încărcare.", true));
});

document.getElementById("importAllBtn").addEventListener("click", () => {
    showMessage("Se procesează toate fișierele încărcate, vă rugăm așteptați...");

    fetch("../api/admin/import/all", { 
        method: "POST",
        headers: { 'Authorization': 'Bearer ' + localStorage.getItem('jwt_token') }
    })
    .then(serverResponse => serverResponse.json())
    .then(responseData => {
        if (responseData.success) {
            showMessage(responseData.message);
        } else {
            showMessage(responseData.message, true);
        }
    })
    .catch(() => showMessage("Eroare de rețea la import.", true));
});

// WIPE!!
const wipeButton = document.getElementById("wipeDataBtn");
if (wipeButton) {
    wipeButton.addEventListener("click", () => {
        const userConfirmation = confirm("Ești sigur? Toate datele din sistem vor fi șterse definitiv!");
        if (userConfirmation) {
            showMessage("Se execută curățarea bazei de date...", false);
            
            fetch("../api/admin/wipe", { 
                method: "DELETE",
                headers: { 'Authorization': 'Bearer ' + localStorage.getItem('jwt_token') }
            })
            .then(serverResponse => serverResponse.json())
            .then(responseData => {
                if (responseData.success) {
                    showMessage("Toate datele au fost șterse cu succes!");
                } else {
                    showMessage(responseData.message || "Eroare la ștergerea bazei de date.", true);
                }
            })
            .catch(() => showMessage("Eroare de comunicare.", true));
        }
    });
}

//manager admini
async function handleAdminAction(actionType, httpMethod) {
    const adminUsername = document.getElementById("newAdminUsername").value.trim();
    const adminPassword = document.getElementById("newAdminPassword").value;

    if (!adminUsername) {
        showMessage("Numele de utilizator al administratorului este obligatoriu!", true);
        return;
    }

    const requestPayload = { username: adminUsername, password: adminPassword };

    try {
        const serverResponse = await fetch("../api/admin/admins", {
            method: httpMethod,
            headers: { 
                'Authorization': 'Bearer ' + localStorage.getItem('jwt_token'),
                "Content-Type": "application/json" 
            },
            body: JSON.stringify(requestPayload),
        });

        if (serverResponse.ok) {
            showMessage(`Administrator: acțiune de ${actionType} realizată cu succes.`);
            document.getElementById("newAdminUsername").value = "";
            document.getElementById("newAdminPassword").value = "";
        } else {
            const resultData = await serverResponse.json();
            showMessage(resultData.error || resultData.message || "Eroare la nivelul serverului.", true);
        }
    } catch (networkError) {
        showMessage("Eroare de rețea la gestionarea contului.", true);
    }
}

document.getElementById("btnCreateAdmin").addEventListener("click", () => handleAdminAction("creare", "POST"));
document.getElementById("btnUpdateAdmin").addEventListener("click", () => handleAdminAction("actualizare parolă", "PATCH"));
document.getElementById("btnDeleteAdmin").addEventListener("click", () => handleAdminAction("ștergere cont", "DELETE"));


async function handleRecordAction(actionType, httpMethod) {
    const targetTable = document.getElementById("crudTableSelect").value;
    const recordIdentifier = document.getElementById("recordId").value.trim();
    const rawJsonInput = document.getElementById("recordJsonData").value.trim();

    let targetApiUrl = `../api/admin/${targetTable}`;
    if (httpMethod === 'PATCH' || httpMethod === 'DELETE') {
        if (!recordIdentifier) {
            showMessage(`Pentru operația de ${actionType} este necesar să specificați ID-ul înregistrării!`, true);
            return;
        }
        targetApiUrl += `/${recordIdentifier}`;
    }

    let parsedPayload = null;
    if (httpMethod !== 'DELETE') {
        if (!rawJsonInput) {
            showMessage("Câmpul de date JSON este obligatoriu pentru Insert/Update!", true);
            return;
        }
        try {
            parsedPayload = JSON.parse(rawJsonInput);
        } catch (jsonParseError) {
            showMessage("Formatul JSON introdus nu este valid. Verificați sintaxa.", true);
            return;
        }
    }

    try {
        const serverResponse = await fetch(targetApiUrl, {
            method: httpMethod,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('jwt_token'),
                'Content-Type': 'application/json'
            },
            body: parsedPayload ? JSON.stringify(parsedPayload) : null
        });

        if (serverResponse.ok) {
            showMessage(`Date înregistrate: ${actionType} executat cu succes pe tabelul [${targetTable}].`);
            document.getElementById("recordId").value = "";
            document.getElementById("recordJsonData").value = "";
        } else {
            const errorResponseData = await serverResponse.json();
            showMessage(errorResponseData.error || `Eroare backend la operația de ${actionType}.`, true);
        }
    } catch (networkError) {
        showMessage("Eroare de conexiune la serverul API.", true);
    }
}

document.getElementById("btnCreateRecord").addEventListener("click", () => handleRecordAction("ADĂUGARE (INSERT)", "POST"));
document.getElementById("btnUpdateRecord").addEventListener("click", () => handleRecordAction("ACTUALIZARE (PATCH)", "PATCH"));
document.getElementById("btnDeleteRecord").addEventListener("click", () => handleRecordAction("ȘTERGERE (DELETE)", "DELETE"));

checkSession();